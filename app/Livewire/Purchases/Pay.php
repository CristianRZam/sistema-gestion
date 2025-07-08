<?php

namespace App\Livewire\Purchases;

use App\Models\Parameter;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Pay extends Component
{
    public $productos = [];
    public $metodoPago = '';
    public $total = 0;

    public $compraId;

    public $proveedor_nombre = '';
    public $proveedor_documento = '';
    public $proveedor_id = '';
    public $metodosPago = [];

    public $estadosCompra = [];

    public $compra;
    public $estadoCompra=1;
    public $mostrarModalComprobante = false;
    public $iframeSrc;


    protected $listeners = [
        'open-modal-comprobante' => 'openModalPreview',
        'detalleActualizado' => 'actualizarDetalles',
    ];

    public function openModalPreview()
    {
        $this->vistaComprobantePreview($this->compraId);
    }
    public function mount($compra)
    {
        $this->compra = $compra;
        $this->compraId = $compra;

        $this->metodosPago = Parameter::where('codigoParametro', 'METODO_PAGO')
            ->orderBy('orden')
            ->get();

        $this->estadosCompra = Parameter::where('codigoParametro', 'ESTADO_COMPRA')
            ->orderBy('orden')
            ->get();

        $compraModel = Purchase::with('supplier')->findOrFail($compra);
        $this->estadoCompra = $compraModel->estado_compra_id;

        $this->metodoPago = $compraModel->metodo_pago_id ?? '';
        $this->total = $compraModel->total;

        if ($compraModel->estado_compra_id != 4) {
            $this->iframeSrc = route('comprobante.compra.preview', ['compraId' => $this->compraId]) . '?t=' . now()->timestamp;
        }


        if ($compraModel->supplier) {
            $this->proveedor_id = $compraModel->supplier->id;
            $this->proveedor_nombre = $compraModel->supplier->nombre;
            $this->proveedor_documento = $compraModel->supplier->documento;
        }

        $this->actualizarDetalles();


    }

    public function actualizarDetalles()
    {
        $detalles = PurchaseDetail::with([
            'product',
            'losses' => fn ($q) => $q
                ->whereNull('auditoriaFechaEliminacion')
                ->with(['motivo', 'tipo']),
        ])->where('purchase_id', $this->compraId)->get();

        $this->productos = $detalles->map(function ($detalle) {
            // Solo considerar tipo_id 1 o 3 como descuentos
            $perdidasQueDescuentan = $detalle->losses
                ->filter(fn($l) => in_array($l->tipo_id, [1, 3]));

            $cantidadDescontada = $perdidasQueDescuentan->sum('cantidad_fallida');
            $cantidadUtil = $detalle->cantidad - $cantidadDescontada;

            return [
                'id'         => $detalle->id,
                'product_id' => $detalle->product_id,
                'nombre'     => $detalle->product->nombre ?? '',
                'precio'     => $detalle->precio_unitario,
                'cantidad'   => $detalle->cantidad,
                'fallidas'   => $detalle->losses->map(function ($loss) {
                    return [
                        'cantidad_fallida' => $loss->cantidad_fallida,
                        'motivo' => optional($loss->motivo)->nombre ?? '',
                        'tipo'   => optional($loss->tipo)->nombre ?? '',
                        'tipo_id' => $loss->tipo_id,
                        'fecha'  => $loss->fecha_perdida,
                    ];
                })->toArray(),
                'cantidad_util' => $cantidadUtil,
                'subtotal'      => $cantidadUtil * $detalle->precio_unitario,
            ];
        })->toArray();

    }


    public function getTotalConDescuentoProperty()
    {
        return max(0, collect($this->productos)->sum('subtotal'));
    }



    public function guardar()
    {

        // Validar que se haya seleccionado un método de pago
        if (!$this->metodoPago) {
            $this->dispatch('errorPayPurchase', ['mensaje' => "Debe seleccionar un método de pago."]);
            $this->addError('metodoPago', 'Debe seleccionar un método de pago.');
            return;
        }


        // Iniciar transacción
        DB::beginTransaction();

        try {
            foreach ($this->productos as $producto) {
                $productoDB = Product::find($producto['product_id']);

                if (!$productoDB) {
                    DB::rollBack();
                    $this->addError('productos', 'Producto no encontrado.');
                    $this->dispatch('errorPayPurchase', ['mensaje' => "Producto no encontrado."]);
                    return;
                }

                // Si la compra está marcada como completa (id = 4), aumentar el stock
                if ($this->estadoCompra == 3) {
                    $productoDB->stock += $producto['cantidad'];
                    $productoDB->save();
                }
            }


            // Actualizar estado de la venta
            $compra = Purchase::find($this->compraId);
            if (!$compra) {
                DB::rollBack();
                $this->addError('productos', 'Compra no encontrada.');
                $this->dispatch('errorPayPurchase', ['mensaje' => "Error interno. Compra no encontrada."]);
                return;
            }


            if ($this->estadoCompra == 2 || ($compra->estado_compra_id ==1 && $this->estadoCompra == 3)) {
                $compra->fecha_compra = Carbon::now();
            }

            $compra->estado_compra_id = $this->estadoCompra ;
            $compra->metodo_pago_id = $this->metodoPago;
            $compra->auditoriaFechaModificacion = Carbon::now();
            $compra->auditoriaModificadoPor = auth()->id();
            $compra->save();

            DB::commit();

            $this->iframeSrc = route('comprobante.compra.preview', ['compraId' => $this->compraId]) . '?t=' . now()->timestamp;
            $this->mostrarModalComprobante = true;
            $this->dispatch('open-modal-comprobante');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('productos', 'Ocurrió un error al procesar el pago.'. $e->getMessage());
            \Log::error('Error al procesar pago: ' . $e->getMessage());
            $this->dispatch('errorPayPurchase', ['mensaje' => "Error interno  al procesar la compra."]);
        }
    }

    public function eliminarCompra()
    {
        DB::beginTransaction();

        try {
            $compra = Purchase::with('detalles')->findOrFail($this->compraId);

            // Verifica si la venta está pagada
            if ($this->estadoCompra == '3') {
                // Recuperar los detalles de la venta
                $detalles = PurchaseDetail::where('purchase_id', $compra->id)->get();

                // Restar del stock los productos de la compra cancelada
                foreach ($detalles as $detalle) {
                    $producto = Product::find($detalle->product_id);
                    if ($producto) {
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();
                    }
                }

            }

            // Cambiar el estado de la venta a "cancelada" (3)
            $compra->estado_compra_id = 4;
            $compra->auditoriaFechaModificacion = Carbon::now();
            $compra->auditoriaModificadoPor = auth()->id();
            $compra->fecha_compra = Carbon::now();
            $compra->save();

            DB::commit();

            session()->flash('success', 'La compra fue cancelada correctamente.');
            return redirect()->route('purchases');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar la compra: ' . $e->getMessage());
            $this->addError('eliminacion', 'Ocurrió un error al intentar eliminar la compra.');
            $this->dispatch('errorPayPurchase', ['mensaje' => "Error interno al intentar eliminar la compra."]);
        }
    }

    public function vistaComprobantePreview($compraId)
    {
        $compra = Purchase::with('supplier')->findOrFail($compraId);

        $productos = PurchaseDetail::with([
            'product',
            'losses' => fn ($q) => $q
                ->whereNull('auditoriaFechaEliminacion')
                ->with(['tipo'])
        ])
            ->where('purchase_id', $compraId)
            ->get()
            ->map(function ($detalle) {
                // Pérdidas que descuentan del total (tipo 1 o 3)
                $descuentan = $detalle->losses->filter(fn($l) => in_array($l->tipo_id, [1, 3]));
                $cantidadDescontada = $descuentan->sum('cantidad_fallida');
                $cantidadFinal = max(0, $detalle->cantidad - $cantidadDescontada);

                return [
                    'id'              => $detalle->product_id,
                    'nombre'          => $detalle->product->nombre ?? '',
                    'precio_unitario' => $detalle->precio_unitario,
                    'cantidad'        => $detalle->cantidad,
                    'cantidad_descontada' => $cantidadDescontada,
                    'subtotal'        => $cantidadFinal * $detalle->precio_unitario,
                ];
            })->toArray();

        // Recalcular el total del comprobante
        $total = collect($productos)->sum('subtotal');
        $compra->total = $total;

        $pdf = Pdf::loadView('pdf.ticket-pedido-termica', compact('compra', 'productos'))
            ->setPaper([0, 0, 226.77, 600]); // 80mm de ancho

        return $pdf->stream("comprobante-{$compraId}.pdf");
    }


    public function render()
    {
        return view('livewire.purchases.pay');
    }
}
