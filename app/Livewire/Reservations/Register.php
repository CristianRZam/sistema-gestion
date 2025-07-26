<?php

namespace App\Livewire\Reservations;

use App\Models\Customer;
use App\Models\OrderService;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Register extends Component
{
    public $montoTotal = 0;

    public $cliente_nombre = '';
    public $cliente_seleccionado = null;

    public $reservationId;
    public $detallesHabitaciones = [];

    public function mount($id)
    {
        $this->reservationId = $id;

        $this->detallesHabitaciones = ReservationRoom::with(['room.tipo', 'ordenesServicio.estado', 'ventas'])
            ->where('reservation_id', $this->reservationId)
            ->whereNull('auditoriaFechaEliminacion')
            ->get()
            ->map(function ($detalle) {
                $room = $detalle->room;
                $roomArray = $room->toArray();
                $roomArray['tipo_nombre'] = $room->tipo?->nombre ?? '---';

                return [
                    'id' => $detalle->id,
                    'room' => $roomArray,
                    'fecha_inicio' => Carbon::parse($detalle->fecha_inicio)->format('Y-m-d'),
                    'fecha_fin' => Carbon::parse($detalle->fecha_fin)->format('Y-m-d'),
                    'cantidad_personas' => $detalle->cantidad_personas,
                    'precio' => $detalle->precio,
                    'subtotal' => $detalle->subtotal ?? 0,
                    'servicios' => [],
                    'ordenes_servicio' => $detalle->ordenesServicio->map(function ($orden) {
                        return [
                            'id' => $orden->id,
                            'fecha' => $orden->fecha->format('Y-m-d H:i'),
                            'estado_id' => $orden->estado_id,
                            'estado_nombre' => $orden->estado->nombre ?? 'Sin estado',
                            'total' => max(0, $orden->total - $orden->descuento),
                            'pagado' => $orden->pagado,
                            'cantidad_detalles' => $orden->detalles()->count(),
                        ];
                    })->toArray(),
                    'productos' => $detalle->ventas
                        ->where('estado_venta_id', '!=', 3)
                        ->map(function ($venta) {
                            return [
                                'id' => $venta->id,
                                'fecha' => optional($venta->created_at)->format('Y-m-d H:i'),
                                'estado_venta_id' => $venta->estado_venta_id,
                                'estado_nombre' => $venta->estadoVenta->nombre ?? 'Sin estado',
                                'detalles' => $venta->detalles
                                    ->whereNull('auditoriaFechaEliminacion')
                                    ->map(function ($detalle) {
                                        return [
                                            'producto_id' => $detalle->product_id,
                                            'nombre' => $detalle->product?->nombre ?? 'Producto eliminado',
                                            'cantidad' => $detalle->cantidad,
                                            'precio_unitario' => $detalle->precio_unitario,
                                            'subtotal' => $detalle->subtotal,
                                        ];
                                    })->toArray(),
                            ];
                        })
                        ->values()
                        ->toArray(),

                ];
            })
            ->toArray();


        $this->updatedDetallesHabitaciones();
    }




    protected $listeners = [
        'clienteSeleccionadoDesdeVenta' => 'cargarClienteDesdeModal',
    ];

    public $mostrarServicios = [];

    public function toggleServicios($detalleId)
    {
        $this->mostrarServicios[$detalleId] = !($this->mostrarServicios[$detalleId] ?? false);
    }

    public function updatedDetallesHabitaciones()
    {
        $this->montoTotal = 0;

        foreach ($this->detallesHabitaciones as $index => $detalle) {
            if (!empty($detalle['fecha_inicio']) && !empty($detalle['fecha_fin'])) {
                $inicio = Carbon::parse($detalle['fecha_inicio'])->startOfDay();
                $fin = Carbon::parse($detalle['fecha_fin'])->startOfDay();

                $dias = $inicio->diffInDays($fin);
                if ($dias < 1) $dias = 1;

                $precioPorDia = $detalle['precio'] ?? 0;
                $subtotal = $precioPorDia * $dias;

                $this->detallesHabitaciones[$index]['subtotal'] = $subtotal;
                $this->montoTotal += $subtotal;
            }
        }
    }


    public function cargarClienteDesdeModal($clienteId)
    {
        $cliente = Customer::find($clienteId);

        if ($cliente) {
            $this->cliente_seleccionado = [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'dni' => $cliente->documento,
                'direccion' => $cliente->direccion,
            ];

            // También puedes llenar otros campos si necesitas
            $this->cliente_nombre = $cliente->nombre;
        }
    }

    public function eliminarClienteSeleccionado()
    {
        $this->cliente_seleccionado = null;
        $this->cliente_nombre = null; // por si estás usando este campo también
    }

    public $detalleAConfirmar = null;
    public function abrirConfirmacionDetalle($detalleId)
    {
        $this->detalleAConfirmar = $detalleId;
    }

    public $detalleAEliminarId = null;

    public function abrirConfirmacionEliminacion($id)
    {
        $this->detalleAEliminarId = $id;
    }

    public function confirmarEliminarDetalle()
    {
        $detalleId = $this->detalleAEliminarId;

        $detalleKey = collect($this->detallesHabitaciones)->search(fn($item) => $item['id'] === $detalleId);

        if ($detalleKey === false) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "No se encontró el detalle."]);
            return;
        }

        $detalle = ReservationRoom::find($detalleId);

        if ($detalle) {
            // Eliminar lógicamente
            $detalle->auditoriaFechaEliminacion = Carbon::now();
            $detalle->auditoriaEliminadoPor = Auth::id();
            $detalle->save();

            // Eliminar del arreglo local
            unset($this->detallesHabitaciones[$detalleKey]);
            $this->detallesHabitaciones = array_values($this->detallesHabitaciones); // Reindexar

            // Recalcular monto total
            $this->montoTotal = collect($this->detallesHabitaciones)->sum('subtotal');

            // Actualizar el total de la reserva en la base de datos
            $reserva = Reservation::find($this->reservationId);
            if ($reserva) {
                $reserva->monto_total = $this->montoTotal;
                $reserva->auditoriaFechaModificacion = Carbon::now();
                $reserva->auditoriaModificadoPor = Auth::id();
                $reserva->save();
            }

            $this->dispatch('successRegisterReservation', ['mensaje' => "Detalle eliminado correctamente."]);
        } else {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "No se encontró la reserva asociada."]);
        }
    }

    public function guardarDetalle()
    {
        $detalleId = $this->detalleAConfirmar;
        $detalleKey = collect($this->detallesHabitaciones)->search(fn($item) => $item['id'] === $detalleId);

        if ($detalleKey === false) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "No se encontró el detalle."]);
            return;
        }

        $datos = $this->detallesHabitaciones[$detalleKey];

        if (empty($datos['fecha_inicio']) || empty($datos['fecha_fin'])) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "Debe ingresar ambas fechas."]);
            return;
        }

        if ($datos['cantidad_personas'] < 1) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "La cantidad de personas debe ser al menos 1."]);
            return;
        }

        $fechaInicio = Carbon::parse($datos['fecha_inicio'])->startOfDay();
        $fechaFin = Carbon::parse($datos['fecha_fin'])->startOfDay();
        $hoy = Carbon::now()->startOfDay();

        if ($fechaInicio->gt($fechaFin)) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "La fecha de inicio no puede ser mayor que la fecha de fin."]);
            return;
        }

        $detalleBD = ReservationRoom::find($detalleId);
        if (!$detalleBD) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "No se encontró la reserva en base de datos."]);
            return;
        }

        $fechaInicioOriginal = Carbon::parse($detalleBD->fecha_inicio)->startOfDay();
        $fechaFinOriginal = Carbon::parse($detalleBD->fecha_fin)->startOfDay();

        $fechaInicioModificada = !$fechaInicio->equalTo($fechaInicioOriginal);
        $fechaFinModificada = !$fechaFin->equalTo($fechaFinOriginal);

        if ($fechaInicioModificada && $fechaInicioOriginal->lte($hoy)) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "No se puede modificar la fecha de inicio porque ya ha comenzado o está en curso."]);
            return;
        }

        if (($fechaInicioModificada && $fechaInicio->lt($hoy)) || ($fechaFinModificada && $fechaFin->lt($hoy))) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "Las fechas modificadas no pueden ser anteriores a hoy."]);
            return;
        }

        $roomId = $datos['room']['id'] ?? null;
        if (!$roomId) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "No se identificó la habitación."]);
            return;
        }

        $conflictos = ReservationRoom::where('room_id', $roomId)
            ->where('id', '!=', $detalleId)
            ->whereNull('auditoriaFechaEliminacion')
            ->whereHas('reservation', function ($query) {
                $query->whereIn('estado_id', [3, 4, 6]);
            })
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query
                    ->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                    ->orWhere(function ($sub) use ($fechaInicio, $fechaFin) {
                        $sub->where('fecha_inicio', '<=', $fechaInicio)
                            ->where('fecha_fin', '>=', $fechaFin);
                    });
            })
            ->exists();

        if ($conflictos) {
            $this->dispatch('errorRegisterReservation', ['mensaje' => "Conflicto: La habitación ya está reservada en esas fechas."]);
            return;
        }

        $dias = $fechaInicio->diffInDays($fechaFin);
        if ($dias < 1) $dias = 1;

        $subtotal = $detalleBD->precio * $dias;

        $detalleBD->fecha_inicio = $datos['fecha_inicio'];
        $detalleBD->fecha_fin = $datos['fecha_fin'];
        $detalleBD->cantidad_personas = $datos['cantidad_personas'];
        $detalleBD->subtotal = $subtotal;
        $detalleBD->auditoriaFechaModificacion = Carbon::now();
        $detalleBD->auditoriaModificadoPor = Auth::id();
        $detalleBD->save();

        $this->detallesHabitaciones[$detalleKey]['subtotal'] = $subtotal;

        $this->montoTotal = collect($this->detallesHabitaciones)->sum('subtotal');

        $reserva = Reservation::find($this->reservationId);
        if ($reserva) {
            $reserva->monto_total = $this->montoTotal;
            $reserva->auditoriaFechaModificacion = Carbon::now();
            $reserva->auditoriaModificadoPor = Auth::id();
            $reserva->save();
        }

        $this->dispatch('successRegisterReservation', ['mensaje' => "Detalle guardado correctamente."]);
    }


    public function agregarServicio($servicioId)
    {
        $clienteId = $this->cliente_seleccionado['id'] ?? null;

        // Crear la orden
        $orden = OrderService::create([
            'customer_id'             => $clienteId,
            'reservation_room_id'     => $servicioId,
            'fecha'                   => Carbon::now(),
            'estado_id'               => 4,
            'usuario_id'              => Auth::id(),
            'auditoriaFechaCreacion'  => Carbon::now(),
            'auditoriaCreadoPor'      => Auth::id(),
        ]);

        return redirect()->route('order-services.edit', ['id' => $orden->id]);
    }

    public function agregarProducto($servicioId)
    {
        $clienteId = $this->cliente_seleccionado['id'] ?? null;

        // Crear la orden
        $venta = Sale::create([
            'customer_id'             => $clienteId,
            'reservation_room_id'     => $servicioId,
            'fecha_venta'                   => Carbon::now(),
            'estado_venta_id'               => 1,
            'usuario_id'              => Auth::id(),
            'auditoriaFechaCreacion'  => Carbon::now(),
            'auditoriaCreadoPor'      => Auth::id(),
        ]);

        return redirect()->route('sales.edit', ['id' => $venta->id]);
    }


    public function render()
    {
        return view('livewire.reservations.register');
    }
}
