<?php

namespace App\Livewire\OrderServices;

use App\Models\DetailOrderService;
use App\Models\OrderService;
use App\Models\Parameter;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;

class Pay extends Component
{
    public $orden;
    public $pagos;
    public $metodoPago = '';
    public float $descuentoInput = 0;
    public $pagoIdAEliminar = null;

    public $modoPago;
    public $monto_pagado = null;
    public $monto_entregado = null;
    public $descuento = 0;
    public $total = 0;
    public $mostrarModalComprobante = false;
    public $iframeSrc;
    public bool $esGeneradoPorReserva = false;
    public ?int $reservaId = null;

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'open-modal-comprobante' => 'openModalPreview',
    ];

    public function mount($orden)
    {
        $this->orden = OrderService::with([
            'customer',
            'reservationRoom.reservation',
            'detalles' => function ($query) {
                $query->whereNull('auditoriaFechaEliminacion')->with('servicio');
            },
            'pagos.metodoPago'
        ])->findOrFail($orden);

        if ($this->orden->detalles->isEmpty()) {
            return Redirect::route('order-services.edit', ['id' => $this->orden->id]);
        }

        $this->pagos = $this->orden->pagos;
        $this->descuentoInput = $this->orden->descuento ?? 0;
        $this->modoPago = $this->orden->modo_pago_id;

        if ($this->orden->reservation_room_id !== null) {
            $this->esGeneradoPorReserva = true;
            $this->reservaId = optional($this->orden->reservationRoom)->reservation_id;
        }

        if ($this->orden->estado_id === 2) {
            $this->iframeSrc = route('comprobante-servicio.preview', ['ordenId' => $this->orden->id]) . '?t=' . now()->timestamp;
        }
    }

    public function getDetallesFiltradosProperty()
    {
        return $this->orden->detalles->whereNull('auditoriaFechaEliminacion');
    }
    public function updatedDescuentoInput($value)
    {
        if ($this->orden->estado_id === 2) return;

        $valor = is_numeric($value) ? floatval($value) : 0;
        $faltanteReal = max(0, $this->orden->total - $this->totalPagado);

        if ($valor > $faltanteReal) $valor = $faltanteReal;

        $this->descuentoInput = $valor;

        $this->orden->update([
            'descuento' => $valor
        ]);

        $this->orden->refresh();
    }

    public function getTotalPagadoProperty()
    {
        return $this->pagos->sum('monto_pagado');
    }

    public function getVueltoProperty()
    {
        $montoPagar = $this->modoPago == 2
            ? floatval($this->monto_pagado ?? 0)
            : floatval($this->orden->total - $this->orden->descuento - $this->totalPagado);

        return max(0, floatval($this->monto_entregado ?? 0) - $montoPagar);
    }

    public function getMetodosPagoProperty()
    {
        return Parameter::where('codigoParametro', 'METODO_PAGO')->get();
    }

    public function getModosPagoProperty()
    {
        return Parameter::where('codigoParametro', 'MODO_PAGO')->get();
    }

    public function iniciarProcesarPago()
    {
        $this->validate([
            'modoPago' => 'required|numeric',
            'metodoPago'      => 'required|numeric',
            'monto_entregado' => 'required|numeric|min:0.01',
        ]);

        $totalConDescuento = $this->orden->total - $this->orden->descuento;
        $restante = $totalConDescuento - $this->totalPagado;

        if ($this->modoPago == 2) {
            $this->validate([
                'monto_pagado' => 'required|numeric|min:0.01',
            ]);

            // Validar que el primer pago no sea igual o mayor al total con descuento
            if ($this->totalPagado == 0 && $this->monto_pagado >= $totalConDescuento) {
                $this->addError('monto_pagado', 'El primer pago debe ser menor al monto total (después del descuento).');
                $this->dispatch('errorPayService', ['mensaje' => "El primer pago debe ser menor al monto total (después del descuento)."]);
                return;
            }

            // Validar que el monto que se desea pagar no sea mayor a lo que resta
            if ($this->monto_pagado > $restante) {
                $this->addError('monto_pagado', 'El monto ingresado supera el saldo pendiente.');
                $this->dispatch('errorPayService', ['mensaje' => "El monto ingresado supera el saldo pendiente."]);
                return;
            }

        } else {
            // Modo completo: monto entregado no puede ser menor al restante
            if ($this->monto_entregado < $restante) {
                $this->addError('monto_entregado', 'Debe entregar al menos el saldo pendiente para completar el pago.');
                $this->dispatch('errorPayService', ['mensaje' => "Debe entregar al menos el saldo pendiente para completar el pago."]);
                return;
            }
        }

        $this->dispatch('openModalProcesarPago');
    }

    public function getTotalConDescuentoProperty()
    {
        // Validar que $this->descuento sea numérico y positivo, si no, usar 0
        $descuento = is_numeric($this->descuento) && $this->descuento > 0 ? floatval($this->descuento) : 0;

        return max(0, $this->total - $descuento);
    }

    public function procesarPago()
    {
        $totalDeuda = $this->orden->total - $this->orden->descuento;
        $restante = $totalDeuda - $this->totalPagado;

        if ($restante <= 0) {
            $this->dispatch('errorPayService', ['mensaje' => "La orden ya está completamente pagada."]);
            return;
        }

        // Validación de cambio de modo de pago
        if ($this->modoPago != $this->orden->modo_pago_id) {
            if ($this->orden->pagos()->exists()) {
                $this->dispatch('errorPayService', [
                    'mensaje' => "No se puede cambiar el modo de pago porque ya existen pagos registrados."
                ]);
                return;
            }

            // Si no hay pagos, actualizar el modo de pago
            $this->orden->update([
                'modo_pago_id' => $this->modoPago
            ]);

            $this->orden->refresh();
        }

        $montoPagado = $this->modoPago == 2
            ? floatval($this->monto_pagado)
            : min($restante, floatval($this->monto_entregado));

        $montoEntregado = floatval($this->monto_entregado);
        $vuelto = max(0, $montoEntregado - $montoPagado);

        // Registrar pago
        Payment::create([
            'pagable_id'             => $this->orden->id,
            'pagable_type'           => OrderService::class,
            'monto_pagado'           => $montoPagado,
            'monto_entregado'        => $montoEntregado,
            'vuelto'                 => $vuelto,
            'metodo_pago_id'         => $this->metodoPago,
            'estado_pago_id'         => 1,
            'fecha_pago'             => Carbon::now(),
            'user_id'                => Auth::id(),
            'auditoriaFechaCreacion' => Carbon::now(),
            'auditoriaCreadoPor'     => Auth::id(),
        ]);

        $this->orden->refresh();
        $this->pagos = $this->orden->pagos()->with('metodoPago')->get();

        // Sumamos el nuevo pago manualmente
        $nuevoTotalPagado = $this->totalPagado + $montoPagado;

        if ($nuevoTotalPagado >= $totalDeuda) {
            $this->orden->update([
                'pagado' => true,
                'estado_id' => 2,
            ]);
            $this->iframeSrc = route('comprobante-servicio.preview', ['ordenId' => $this->orden->id]) . '?t=' . now()->timestamp;
        }

        $this->reset(['metodoPago', 'monto_pagado', 'monto_entregado']);
        $this->dispatch('successPayService', ['mensaje' => "Pago registrado correctamente."]);
        $this->dispatch('refreshComponent');
    }


    public function confirmarEliminacion($pagoId)
    {
        $this->pagoIdAEliminar = $pagoId;
        $this->dispatch('abrirModalEliminarPago');
    }

    public function eliminarPago()
    {
        $pago = Payment::findOrFail($this->pagoIdAEliminar);

        $pago->update([
            'estado_pago_id'         => 3,
            'auditoriaFechaEliminacion' => Carbon::now(),
            'auditoriaEliminadoPor'     => Auth::id(),
        ]);

        $this->orden->refresh();
        $this->pagos = $this->orden->pagos()->with('metodoPago')->get();

        $totalDeuda = $this->orden->total - $this->orden->descuento;

        if ($this->totalPagado < $totalDeuda) {
            $this->orden->update([
                'pagado' => false,
                'estado_id' => 4,
            ]);
        }

        $this->pagoIdAEliminar = null;
        $this->dispatch('successPayService', ['mensaje' => "Pago eliminado correctamente."]);
        $this->dispatch('refreshComponent');
    }

    public function openModalPreview()
    {
        $this->vistaComprobantePreview($this->orden->id);
    }

    public function vistaComprobantePreview($ordenId)
    {
        $orden = OrderService::with('customer')->findOrFail($ordenId);

        $servicios = DetailOrderService::with('servicio')
            ->where('order_service_id', $ordenId)
            ->get()
            ->map(function ($detalle) {
                return [
                    'id'             => $detalle->service_id,
                    'nombre'         => $detalle->servicio->nombre ?? '',
                    'precio_unitario'=> $detalle->precio_unitario,
                    'subtotal'       => $detalle->subtotal,
                    'cantidad'       => $detalle->cantidad,
                ];
            })->toArray();

        $pdf = Pdf::loadView('pdf.comprobante-servicio-termica', compact('orden', 'servicios'))
            ->setPaper([0, 0, 226.77, 600]);

        return $pdf->stream("comprobante-{$ordenId}.pdf");
    }

    public function cancelarServicio()
    {
        foreach ($this->orden->pagos as $pago) {
            $pago->update([
                'estado_pago_id'         => 3,
                'auditoriaFechaEliminacion' => Carbon::now(),
                'auditoriaEliminadoPor'     => Auth::id(),
            ]);
        }

        $this->orden->update([
            'pagado' => false,
            'estado_id' => 1,
            'descuento' => 0,
            'auditoriaFechaModificacion' => Carbon::now(),
            'auditoriaModificadoPor' => Auth::id(),
        ]);

        $this->orden->refresh();
        $this->pagos = $this->orden->pagos()->with('metodoPago')->get();

        $this->dispatch('successPayService', ['mensaje' => 'Servicio cancelado correctamente.']);
        $this->dispatch('cerrarModalCancelarServicio');
        $this->dispatch('refreshComponent');
    }

    public function render()
    {
        return view('livewire.order-services.pay', [
            'orden'       => $this->orden,
            'pagos'       => $this->pagos,
            'metodos'     => $this->metodosPago,
            'totalPagado' => $this->totalPagado,
            'vuelto'      => $this->vuelto,
            'modosPago'   => $this->modosPago,
        ]);
    }
}
