<?php

namespace App\Livewire\OrderServices;

use App\Models\DetailOrderService;
use App\Models\OrderService;
use App\Models\Parameter;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Pay extends Component
{
    public $orden;
    public $pagos;
    public $metodoPago = '';
    public $pago_con = 0;
    public float $descuentoInput = 0;
    public $pagoIdAEliminar = null;

    public $mostrarModalComprobante = false;
    public $iframeSrc;

    protected $listeners =
        [
        'refreshComponent' => '$refresh',
        'open-modal-comprobante' => 'openModalPreview',
        ];

    public function mount($orden)
    {
        $this->orden = OrderService::with([
            'customer',
            'detalles' => function ($query) {
                $query->whereNull('auditoriaFechaEliminacion')
                    ->with('servicio');
            },
            'pagos.metodoPago'
        ])->findOrFail($orden);

        $this->pagos = $this->orden->pagos;
        $this->descuentoInput = $this->orden->descuento ?? 0;

        if ($this->orden->estado_id === 2) {
            $this->iframeSrc = route('comprobante-servicio.preview', ['ordenId' => $this->orden->id]) . '?t=' . now()->timestamp;
        }
    }


    public function updatedDescuentoInput($value)
    {
        if ($this->orden->estado_id === 2) {
            // No permitir editar si la orden ya está pagada
            return;
        }

        $valor = is_numeric($value) ? floatval($value) : 0;

        $faltanteReal = max(0, $this->orden->total - $this->totalPagado);

        if ($valor > $faltanteReal) {
            $valor = $faltanteReal;
        }

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
        return max(0, floatval($this->pago_con ?? 0) - ($this->orden->total - $this->orden->descuento - $this->totalPagado));
    }


    public function getMetodosPagoProperty()
    {
        return Parameter::where('codigoParametro', 'METODO_PAGO')->get();
    }

    public function iniciarProcesarPago()
    {
        $this->validate([
            'metodoPago' => 'required|numeric',
            'pago_con'   => 'required|numeric|min:0.01',
        ]);

        $this->dispatch('openModalProcesarPago');
    }
    public function procesarPago()
    {
        $totalDeuda = $this->orden->total - $this->orden->descuento;
        $restante   = $totalDeuda - $this->totalPagado;

        if ($restante <= 0) {
            //session()->flash('success', 'La orden ya está completamente pagada.');
            $this->dispatch('errorPayService', ['mensaje' => "La orden ya está completamente pagada."]);
            return;
        }

        if ($this->pago_con > $restante) {
            $this->pago_con = $restante;
        }

        Payment::create([
            'pagable_id'             => $this->orden->id,
            'pagable_type'           => OrderService::class,
            'monto_pagado'           => $this->pago_con,
            'metodo_pago_id'         => $this->metodoPago,
            'estado_pago_id'         => 1,
            'fecha_pago'             => Carbon::now(),
            'auditoriaFechaCreacion' => Carbon::now(),
            'auditoriaCreadoPor'     => Auth::id(),
        ]);

        $this->orden->refresh();
        $this->pagos = $this->orden->pagos()->with('metodoPago')->get();

        $nuevoTotalPagado = $this->totalPagado + $this->pago_con;

        if ($nuevoTotalPagado >= $totalDeuda) {
            $this->orden->update([
                'pagado' => true,
                'estado_id' => 2,
            ]);
            $this->iframeSrc = route('comprobante-servicio.preview', ['ordenId' => $this->orden->id]) . '?t=' . now()->timestamp;
        }

        $this->reset(['pago_con', 'metodoPago']);
        //session()->flash('success', 'Pago registrado correctamente.');
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

        //session()->flash('success', 'Pago eliminado correctamente.');
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
                    'id'       => $detalle->service_id,
                    'nombre'   => $detalle->servicio->nombre ?? '',
                    'precio_unitario'   => $detalle->precio_unitario,
                    'subtotal'   => $detalle->subtotal,
                    'cantidad' => $detalle->cantidad,
                ];
            })->toArray();

        $pdf = Pdf::loadView('pdf.comprobante-servicio-termica', compact('orden', 'servicios'))
            ->setPaper([0, 0, 226.77, 600]); // 80mm de ancho

        return $pdf->stream("comprobante-{$ordenId}.pdf");
    }


    public function render()
    {
        return view('livewire.order-services.pay', [
            'orden'       => $this->orden,
            'pagos'       => $this->pagos,
            'metodos'     => $this->metodosPago,
            'totalPagado' => $this->totalPagado,
            'vuelto'      => $this->vuelto,
        ]);
    }
}
