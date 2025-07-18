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
    public $pagos; // colección de modelos
    public $metodoPago = '';
    public $pago_con = 0;

    // Mantener listener para el dispatch de Livewire 3.x
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount($orden)
    {
        $this->orden = OrderService::with([
            'customer',
            'detalles.servicio',
            'pagos.metodoPago'
        ])->findOrFail($orden);

        $this->pagos = $this->orden->pagos; // colección con relación cargada
    }

    public function getTotalPagadoProperty()
    {
        return $this->pagos->sum('monto_pagado');
    }

    public function getVueltoProperty()
    {
        return max(0, $this->pago_con - ($this->orden->total - $this->orden->descuento - $this->totalPagado));
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

        // Refrescar modelo completo y obtener pagos actualizados
        $this->orden->refresh();
        $this->pagos = $this->orden->pagos()->with('metodoPago')->get();

        // Actualizar si está totalmente pagado
        if ($this->totalPagado >= $totalDeuda) {
            $this->orden->update(['pagado' => true]);
        }

        $this->reset(['pago_con', 'metodoPago']);
        session()->flash('success', 'Pago registrado correctamente.');

        // Este dispatch seguirá funcionando si otros componentes escuchan el evento
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
