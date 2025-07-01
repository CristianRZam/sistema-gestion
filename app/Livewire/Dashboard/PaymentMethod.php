<?php

namespace App\Livewire\Dashboard;

use App\Models\Sale;
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

        $ventas = Sale::where('estado_venta_id', 2)
            ->whereNull('auditoriaFechaEliminacion');

        if ($this->fechaInicio && $this->fechaFin) {
            $inicio = Carbon::parse($this->fechaInicio)->startOfDay();
            $fin = Carbon::parse($this->fechaFin)->endOfDay();
            $ventas->whereBetween('fecha_venta', [$inicio, $fin]);
        }

        $ventas = $ventas->get();
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
