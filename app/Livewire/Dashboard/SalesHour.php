<?php

namespace App\Livewire\Dashboard;

use App\Models\Sale;
use Carbon\Carbon;
use Livewire\Component;

class SalesHour extends Component
{
    public ?string $fechaInicio = null;
    public ?string $fechaFin = null;

    public $ventasPorHora = [];

    protected $listeners = ['rangoFechasActualizado' => 'actualizarRangoFechas'];

    public function mount()
    {
        $this->fechaInicio = now()->toDateString();
        $this->fechaFin = now()->toDateString();

        $this->cargarDatos();
    }

    public function actualizarRangoFechas(array $rango)
    {
        $this->fechaInicio = $rango['inicio'];
        $this->fechaFin = $rango['fin'];

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->ventasPorHora = [];

        // Inicializar las horas de 6 a.m. (06) a 10 p.m. (22)
        $horas = [];
        for ($i = 6; $i <= 22; $i++) {
            $horas[str_pad($i, 2, '0', STR_PAD_LEFT)] = 0;
        }

        $inicio = Carbon::parse($this->fechaInicio)->startOfDay();
        $fin = Carbon::parse($this->fechaFin)->endOfDay();

        $query = Sale::where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion')
            ->whereBetween('fecha_venta', [$inicio, $fin]);

        // 🔒 Aplicar filtro si el usuario no tiene permiso
        if (!auth()->user()->can('ver reporte general dashboard')) {
            $query->where('usuario_id', auth()->id());
        }

        $ventas = $query->get();

        foreach ($ventas as $venta) {
            $hora = Carbon::parse($venta->fecha_venta)->format('H');
            if (array_key_exists($hora, $horas)) {
                $horas[$hora]++;
            }
        }

        $this->ventasPorHora = $horas;

        $this->dispatch('actualizarGraficoVentasHora', [
            'labels' => $this->generarEtiquetas(),
            'ventas' => array_values($this->ventasPorHora),
        ]);
    }


    public function render()
    {
        return view('livewire.dashboard.sales-hour', [
            'ventasPorHoraJson' => json_encode(array_values($this->ventasPorHora)),
            'labelsJson' => json_encode($this->generarEtiquetas()),
        ]);
    }

    private function generarEtiquetas()
    {
        $labels = [];
        for ($i = 6; $i <= 22; $i++) {
            $labels[] = Carbon::createFromTime($i)->format('g a');
        }
        return $labels;
    }
}
