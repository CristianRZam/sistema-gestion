<?php

namespace App\Livewire\Dashboard;

use App\Models\Sale;
use Carbon\Carbon;
use Livewire\Component;

class SalesHour extends Component
{
    public $ventasPorHora = [];

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $hoy = Carbon::now()->format('Y-m-d');

        // Inicializar las horas de 6 a.m. (06) a 10 p.m. (22)
        $horas = [];
        for ($i = 6; $i <= 22; $i++) {
            $horas[str_pad($i, 2, '0', STR_PAD_LEFT)] = 0;
        }

        // Obtener ventas de hoy entre 6:00 a.m. y 10:59 p.m.
        $ventas = Sale::where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion')
            ->whereDate('fecha_venta', $hoy)
            ->whereTime('fecha_venta', '>=', '06:00:00')
            ->whereTime('fecha_venta', '<=', '22:59:59')
            ->get();


        foreach ($ventas as $venta) {
            $hora = Carbon::parse($venta->fecha_venta)->format('H');
            if (array_key_exists($hora, $horas)) {
                $horas[$hora]++;
            }
        }

        $this->ventasPorHora = $horas;
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
            $labels[] = Carbon::createFromTime($i)->format('g a'); // Ej: 6 a.m., 7 a.m., ..., 10 p.m.
        }
        return $labels;
    }
}
