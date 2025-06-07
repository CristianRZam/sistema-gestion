<?php

namespace App\Livewire\Dashboard;

use App\Models\Sale;
use App\Models\Parameter;
use Livewire\Component;

class PaymentMethod extends Component
{
    public $labels = [];
    public $valores = [];
    public $montos = [];

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $metodos = Parameter::where('tipo', 'METODO_PAGO')
            ->whereNull('auditoriaFechaEliminacion')
            ->orderBy('orden')
            ->get();

        $conteo = [];
        $montosTotales = [];

        foreach ($metodos as $metodo) {
            $conteo[$metodo->idParametro] = 0;
            $montosTotales[$metodo->idParametro] = 0;
        }

        $ventas = Sale::where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion')
            ->get();

        foreach ($ventas as $venta) {
            if (isset($conteo[$venta->metodo_pago_id])) {
                $conteo[$venta->metodo_pago_id]++;
                $monto = ($venta->total ?? 0) - ($venta->descuento ?? 0);
                $montosTotales[$venta->metodo_pago_id] += $monto;
            }
        }

        foreach ($metodos as $metodo) {
            $this->labels[] = $metodo->nombreCorto ?? $metodo->nombre;
            $this->valores[] = $conteo[$metodo->idParametro] ?? 0;
            $this->montos[] = round($montosTotales[$metodo->idParametro] ?? 0, 2);
        }
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
