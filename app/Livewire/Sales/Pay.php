<?php

namespace App\Livewire\Sales;

use App\Models\Parameter;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
    public $venta;
    public $estadoVenta=1;
    public $mostrarModalComprobante = false;
    public $iframeSrc;


    protected $listeners = [
        'open-modal-comprobante' => 'openModalPreview',
    ];

    public function openModalPreview()
    {
        $this->vistaComprobantePreview($this->ventaId);
    }
    public function mount($venta)
    {
        $this->venta = $venta;
        $this->ventaId = $venta;

        $this->metodosPago = Parameter::where('tipo', 'METODO_PAGO')
            ->orderBy('orden')
            ->get();

        $ventaModel = Sale::with('customer')->findOrFail($venta);
        $this->estadoVenta = $ventaModel->estado_venta_id;

        $this->metodoPago = $ventaModel->metodo_pago_id ?? '';
        $this->total = $ventaModel->total;
        $this->descuento = $ventaModel->descuento ?? 0;
        $this->pago_con = $ventaModel->pago_con ?? 0;

        if ($ventaModel->estado_venta_id == 2) {
            $this->vuelto = $ventaModel->vuelto ?? 0;
            $this->iframeSrc = route('comprobante.preview', ['ventaId' => $this->ventaId]) . '?t=' . now()->timestamp;
        }


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


    public function procesarPago()
    {
        // Validar que se haya seleccionado un método de pago
        if (!$this->metodoPago) {
            $this->addError('metodoPago', 'Debe seleccionar un método de pago.');
            return;
        }

        // Validación básica
        if (!is_numeric($this->pago_con) || $this->pago_con < $this->totalConDescuento) {
            $this->addError('pago_con', 'El monto pagado debe ser mayor o igual al total con descuento.');
            return;
        }

        // Iniciar transacción
        DB::beginTransaction();

        try {
            foreach ($this->productos as $producto) {
                $productoDB = Product::find($producto['id']);

                if (!$productoDB) {
                    DB::rollBack();
                    $this->addError('productos', 'Producto no encontrado.');
                    return;
                }

                if ($productoDB->stock < $producto['cantidad']) {
                    DB::rollBack();
                    $this->addError('productos', "Stock insuficiente para {$producto['nombre']}.");
                    return;
                }

                // Descontar el stock
                $productoDB->stock -= $producto['cantidad'];
                $productoDB->save();
            }

            // Actualizar estado de la venta
            $venta = Sale::find($this->ventaId);
            if (!$venta) {
                DB::rollBack();
                $this->addError('productos', 'Venta no encontrada.');
                return;
            }

            $venta->estado_venta_id = 2; // Pagada
            $venta->metodo_pago_id = $this->metodoPago;
            $venta->descuento = $this->descuento;
            $venta->pago_con = $this->pago_con;
            $venta->vuelto = $this->vuelto;
            $venta->auditoriaFechaModificacion = Carbon::now();
            $venta->auditoriaModificadoPor = auth()->id();
            $venta->save();

            DB::commit();

            $this->estadoVenta = 2;
            $this->iframeSrc = route('comprobante.preview', ['ventaId' => $this->ventaId]) . '?t=' . now()->timestamp;
            $this->mostrarModalComprobante = true;
            $this->dispatch('open-modal-comprobante');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('productos', 'Ocurrió un error al procesar el pago.');
            \Log::error('Error al procesar pago: ' . $e->getMessage());
        }
    }

    public function eliminarVenta()
    {
        DB::beginTransaction();

        try {
            $venta = Sale::with('detalles')->findOrFail($this->ventaId);

            // Verifica si la venta está pagada
            if ($venta->estado_venta_id == 2) {
                // Recuperar los detalles de la venta
                $detalles = SaleDetail::where('sale_id', $venta->id)->get();

                // Devolver los productos al stock
                foreach ($detalles as $detalle) {
                    $producto = Product::find($detalle->product_id);
                    if ($producto) {
                        $producto->stock += $detalle->cantidad;
                        $producto->save();
                    }
                }
            }

            // Cambiar el estado de la venta a "cancelada" (3)
            $venta->estado_venta_id = 3;
            $venta->auditoriaFechaModificacion = Carbon::now();
            $venta->auditoriaModificadoPor = auth()->id();
            $venta->save();

            DB::commit();

            session()->flash('success', 'La venta fue cancelada correctamente.');
            return redirect()->route('sales');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar la venta: ' . $e->getMessage());
            $this->addError('eliminacion', 'Ocurrió un error al intentar eliminar la venta.');
        }
    }

    public function vistaComprobantePreview($ventaId)
    {
        $venta = Sale::with('customer')->findOrFail($ventaId);

        $productos = SaleDetail::with('product')
            ->where('sale_id', $ventaId)
            ->get()
            ->map(function ($detalle) {
                return [
                    'id'       => $detalle->product_id,
                    'nombre'   => $detalle->product->nombre ?? '',
                    'precio_unitario'   => $detalle->precio_unitario,
                    'subtotal'   => $detalle->subtotal,
                    'cantidad' => $detalle->cantidad,
                    'stock'    => $detalle->product->stock ?? 0,
                ];
            })->toArray();

        $pdf = Pdf::loadView('pdf.comprobante-termica', compact('venta', 'productos'))
            ->setPaper([0, 0, 226.77, 600]); // 80mm de ancho

        return $pdf->stream("comprobante-{$ventaId}.pdf");
    }



    public function render()
    {
        return view('livewire.sales.pay');
    }
}
