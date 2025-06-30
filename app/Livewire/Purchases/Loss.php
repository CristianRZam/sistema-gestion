<?php

namespace App\Livewire\Purchases;

use App\Models\Parameter;
use App\Models\PurchaseDetail;
use App\Models\PurchaseLoss;
use Livewire\Component;

class Loss extends Component
{

    public $productoSeleccionado = [];
    public $cantidadFallida;
    public $motivoPerdida = '';
    public $tipoPerdida = '';
    public $detalleCompraId;

    public $motivos = [];

    public $tipos = [];
    public $purchaseLossId = null; // ID de pérdida existente (si la hay)

    protected $listeners = ['open-modal-loss' => 'cargarDetalleCompra'];

    public function mount()
    {
        $this->motivos = Parameter::where('codigoParametro', 'MOTIVO_PERDIDA_COMPRA')
            ->orderBy('orden')
            ->get();

        $this->tipos = Parameter::where('codigoParametro', 'TIPO_PERDIDA_COMPRA')
            ->orderBy('orden')
            ->get();
    }

    public function cargarDetalleCompra($id = null)
    {
        $this->resetValidation(); // ✅ Limpia mensajes de validación anteriores
        $this->reset(['productoSeleccionado', 'cantidadFallida', 'motivoPerdida', 'tipoPerdida', 'detalleCompraId', 'purchaseLossId']);

        $this->detalleCompraId = $id;

        $detalle = PurchaseDetail::with('product')->find($this->detalleCompraId);

        if ($detalle) {
            $totalComprado = $detalle->cantidad;

            $totalUtilizado = \DB::table('purchase_sale_details as psd')
                ->join('sale_details as sd', 'sd.id', '=', 'psd.sale_detail_id')
                ->join('sales as s', 's.id', '=', 'sd.sale_id')
                ->where('psd.purchase_detail_id', $this->detalleCompraId)
                ->where('s.estado_venta_id', 2)
                ->sum('psd.cantidad_utilizada');

            $stockDisponible = $totalComprado - $totalUtilizado;

            $this->productoSeleccionado = [
                'id' => $detalle->id,
                'nombre' => $detalle->product->nombre,
                'cantidad_comprada' => $totalComprado,
                'cantidad_utilizada' => $totalUtilizado,
                'stock_disponible' => $stockDisponible,
            ];

            // Buscar si ya hay pérdida registrada (que no esté eliminada lógicamente)
            $perdida = PurchaseLoss::where('purchase_detail_id', $this->detalleCompraId)
                ->whereNull('auditoriaFechaEliminacion')
                ->first();

            if ($perdida) {
                $this->purchaseLossId = $perdida->id;
                $this->cantidadFallida = $perdida->cantidad_fallida;
                $this->motivoPerdida = $perdida->motivo_id;
                $this->tipoPerdida = $perdida->tipo_id;
            }

        }

    }

    public function guardarPerdidaVenta()
    {
        $this->validate([
            'cantidadFallida' => 'required|integer|min:1|max:' . ($this->productoSeleccionado['stock_disponible'] ?? 0),
            'motivoPerdida' => 'required|integer|exists:parameters,idParametro',
            'tipoPerdida' => 'required|integer|exists:parameters,idParametro',
        ]);

        $detalle = PurchaseDetail::with('product')->find($this->detalleCompraId);
        $producto = $detalle->product;

        if (!$detalle || !$producto) {
            $this->addError('producto', 'Detalle de compra o producto no encontrados.');
            return;
        }

        // Cargar pérdida actual (si existe)
        $perdida = PurchaseLoss::find($this->purchaseLossId);

        $nuevaCantidad = $this->cantidadFallida;
        $nuevoTipo = $this->tipoPerdida;

        // Si es actualización
        if ($perdida) {
            $cantidadAnterior = $perdida->cantidad_fallida;
            $tipoAnterior = $perdida->tipo_id;

            // Si cambió cantidad o tipo
            if ($nuevaCantidad != $cantidadAnterior || $nuevoTipo != $tipoAnterior) {
                // Revertir efectos anteriores
                if (in_array($tipoAnterior, [1, 2])) { // 1=Devolución, 2=Pérdida
                    $detalle->stock_restante += $cantidadAnterior;
                    $producto->stock += $cantidadAnterior;
                }

                // Aplicar nuevo efecto
                if (in_array($nuevoTipo, [1, 2])) {
                    $detalle->stock_restante -= $nuevaCantidad;
                    $producto->stock -= $nuevaCantidad;
                }

                // Guardar actualizaciones
                $detalle->save();
                $producto->save();
            }

            // Actualizar datos
            $perdida->update([
                'cantidad_fallida' => $nuevaCantidad,
                'motivo_id' => $this->motivoPerdida,
                'tipo_id' => $nuevoTipo,
                'auditoriaFechaModificacion' => now(),
                'auditoriaModificadoPor' => auth()->id(),
            ]);
        } else {
            // Nueva pérdida
            if (in_array($nuevoTipo, [1, 2])) {
                $detalle->stock_restante -= $nuevaCantidad;
                $producto->stock -= $nuevaCantidad;

                $detalle->save();
                $producto->save();
            }

            PurchaseLoss::create([
                'purchase_detail_id' => $this->detalleCompraId,
                'cantidad_fallida' => $nuevaCantidad,
                'motivo_id' => $this->motivoPerdida,
                'tipo_id' => $nuevoTipo,
                'fecha_perdida' => now(),
                'auditoriaFechaCreacion' => now(),
                'auditoriaCreadoPor' => auth()->id(),
            ]);
        }

        $this->dispatch('cerrarModalProductLoss');
        $this->reset([
            'productoSeleccionado', 'cantidadFallida',
            'motivoPerdida', 'detalleCompraId', 'purchaseLossId', 'tipoPerdida'
        ]);
        $this->dispatch('detalleActualizado');
    }


    public function eliminarPerdidaVenta()
    {
        if ($this->purchaseLossId) {
            $perdida = PurchaseLoss::find($this->purchaseLossId);

            if ($perdida) {
                // Obtener detalle y producto relacionados
                $detalle = PurchaseDetail::with('product')->find($perdida->purchase_detail_id);

                if ($detalle && $detalle->product) {
                    $producto = $detalle->product;

                    // Solo revertir si la pérdida afecta el stock
                    if (in_array($perdida->tipo_id, [1, 2])) {
                        $detalle->stock_restante += $perdida->cantidad_fallida;
                        $producto->stock += $perdida->cantidad_fallida;

                        $detalle->save();
                        $producto->save();
                    }
                }

                // Marcamos como eliminada (soft delete)
                $perdida->update([
                    'auditoriaFechaEliminacion' => now(),
                    'auditoriaEliminadoPor' => auth()->id(),
                ]);
            }

            // Limpiar estado y cerrar modal
            $this->dispatch('cerrarModalProductLoss');
            $this->reset([
                'productoSeleccionado', 'cantidadFallida',
                'motivoPerdida', 'detalleCompraId', 'purchaseLossId'
            ]);
            $this->dispatch('detalleActualizado');
        }
    }




    public function render()
    {
        return view('livewire.purchases.loss');
    }
}
