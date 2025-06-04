<?php

namespace App\Livewire\Sales;

use App\Models\Parameter;
use App\Models\Sale;
use App\Models\SaleDetail;
use Livewire\Component;

class Pay extends Component
{
    public $productos = [];
    public $descuento = 0;
    public $metodoPago = '';
    public $total = 0;

    public $ventaId;

    public $cliente_nombre = '';
    public $cliente_documento = '';
    public $cliente_id = '';
    public $metodosPago = [];
    public $pago_con = 0; // Monto con el que pagó


    public function mount($venta)
    {
        $this->ventaId = $venta;

        // Cargar métodos de pago desde la base de datos
        $this->metodosPago = Parameter::where('tipo', 'METODO_PAGO')
            ->orderBy('orden')
            ->get();

        // Cargar la venta
        $ventaModel = Sale::with('customer')->findOrFail($venta);
        $this->metodoPago = $ventaModel->metodo_pago_id ?? ''; // Asegúrate de usar el campo correcto

        $this->total = $ventaModel->total;

        if ($ventaModel->customer) {
            $this->cliente_id = $ventaModel->customer->id;
            $this->cliente_nombre = $ventaModel->customer->nombre;
            $this->cliente_documento = $ventaModel->customer->documento;
        }

        $detalles = SaleDetail::with('product')
            ->where('sale_id', $venta)
            ->get();

        $this->productos = $detalles->map(function ($detalle) {
            return [
                'id'       => $detalle->product_id,
                'nombre'   => $detalle->product->nombre ?? '',
                'precio'   => $detalle->precio_unitario,
                'cantidad' => $detalle->cantidad,
                'stock'    => $detalle->product->stock ?? 0,
            ];
        })->toArray();
    }


    public function getTotalConDescuentoProperty()
    {
        // Validar que $this->descuento sea numérico y positivo, si no, usar 0
        $descuento = is_numeric($this->descuento) && $this->descuento > 0 ? floatval($this->descuento) : 0;

        return max(0, $this->total - $descuento);
    }

    public function getVueltoProperty()
    {
        $pagoCon = is_numeric($this->pago_con) ? floatval($this->pago_con) : 0;
        $total = $this->totalConDescuento;

        return max(0, $pagoCon - $total);
    }


    public function render()
    {
        return view('livewire.sales.pay');
    }
}
