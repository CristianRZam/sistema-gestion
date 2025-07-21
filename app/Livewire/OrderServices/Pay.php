<?php

namespace App\Livewire\OrderServices;

use App\Models\OrderService;
use App\Models\Parameter;
use App\Models\Payment;
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

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount($orden)
    {
        $this->orden = OrderService::with([
            'customer',
            'detalles.servicio',
            'pagos.metodoPago'
        ])->findOrFail($orden);

        $this->pagos = $this->orden->pagos;
        $this->descuentoInput = $this->orden->descuento ?? 0;
    }

    public function updatedDescuentoInput($value)
    {
        $valor = is_numeric($value) ? floatval($value) : 0;

        // El máximo descuento es lo que falta pagar, sin tomar en cuenta el descuento actual
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

    public function procesarPago()
    {
        $this->validate([
            'metodoPago' => 'required|numeric',
            'pago_con'   => 'required|numeric|min:0.01',
        ]);

        $totalDeuda = $this->orden->total - $this->orden->descuento;
        $restante   = $totalDeuda - $this->totalPagado;

        if ($restante <= 0) {
            session()->flash('success', 'La orden ya está completamente pagada.');
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

        if ($this->totalPagado >= $totalDeuda) {
            $this->orden->update([
                'pagado' => true,
                'estado_id' => 2,
            ]);
        }

        $this->reset(['pago_con', 'metodoPago']);
        session()->flash('success', 'Pago registrado correctamente.');
        $this->dispatch('refreshComponent');
    }

    public function eliminarPago($pagoId)
    {
        $pago = Payment::findOrFail($pagoId);
        $pago->update([
            'auditoriaFechaEliminacion' => Carbon::now(),
            'auditoriaEliminadoPor'     => Auth::id(),
        ]);

        $this->orden->refresh();
        $this->pagos = $this->orden->pagos()->with('metodoPago')->get();

        // Recalcular si la orden ya no está pagada
        $totalDeuda = $this->orden->total - $this->orden->descuento;

        if ($this->totalPagado < $totalDeuda) {
            $this->orden->update([
                'pagado' => false,
                'estado_id' => 1, // O el estado correspondiente a "pendiente"
            ]);
        }

        session()->flash('success', 'Pago eliminado correctamente.');
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
        ]);
    }
}
