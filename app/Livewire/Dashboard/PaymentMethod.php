<?php

namespace App\Livewire\Dashboard;

use App\Models\Payment;
use App\Models\Parameter;
use Carbon\Carbon;
use Livewire\Component;

class PaymentMethod extends Component
{
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;

    public $labels = [];
    public $valores = [];
    public $montos = [];

    protected $listeners = ['rangoFechasActualizado' => 'actualizarRangoFechas'];
    public function mount()
    {
        // Al cargar por primera vez, puedes asignar fechas por defecto (ej. hoy)
        $this->fechaInicio = now()->toDateString();
        $this->fechaFin = now()->toDateString();

        $this->cargarDatos(); // Este método no recibe argumentos
    }

    public function actualizarRangoFechas(array $rango)
    {
        $this->fechaInicio = $rango['inicio'];
        $this->fechaFin = $rango['fin'];

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->labels = [];
        $this->valores = [];
        $this->montos = [];
        $metodos = Parameter::where('codigoParametro', 'METODO_PAGO')
            ->whereNull('auditoriaFechaEliminacion')
            ->orderBy('orden')
            ->get();

        $conteo = [];
        $montosTotales = [];

        foreach ($metodos as $metodo) {
            $conteo[$metodo->idParametro] = 0;
            $montosTotales[$metodo->idParametro] = 0;
        }

        $ventas = Payment::where('estado_pago_id', 1)
            ->where('pagable_type', 'App\Models\Sale')
            ->whereNull('auditoriaFechaEliminacion');

        if ($this->fechaInicio && $this->fechaFin) {
            $inicio = Carbon::parse($this->fechaInicio)->startOfDay();
            $fin = Carbon::parse($this->fechaFin)->endOfDay();
            $ventas->whereBetween('fecha_pago', [$inicio, $fin]);
        }

        if (!auth()->user()->can('ver reporte general dashboard')) {
            $ventas->where('user_id', auth()->id()); // 👈 Aplica filtro si no tiene el permiso
        }

        $ventas = $ventas->get();


        foreach ($ventas as $venta) {
            if (isset($conteo[$venta->metodo_pago_id])) {
                $conteo[$venta->metodo_pago_id]++;
                $monto = ($venta->monto_pagado ?? 0);
                $montosTotales[$venta->metodo_pago_id] += $monto;
            }
        }

        foreach ($metodos as $metodo) {
            $this->labels[] = $metodo->nombreCorto ?? $metodo->nombre;
            $this->valores[] = $conteo[$metodo->idParametro] ?? 0;
            $this->montos[] = round($montosTotales[$metodo->idParametro] ?? 0, 2);
        }

        $this->dispatch('actualizarGraficoMetodoPago', [
            'labels' => $this->labels,
            'valores' => $this->valores,
            'montos' => $this->montos,
        ]);

    }

    public function render()
    {
        return view('livewire.dashboard.payment-method', [
            'labelsJson' => json_encode($this->labels),
            'valoresJson' => json_encode($this->valores),
            'montosJson' => json_encode($this->montos),
        ]);
    }
}
