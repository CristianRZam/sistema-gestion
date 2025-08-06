<?php

namespace App\Livewire\Sales;

use App\Models\Parameter;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseDetail;
use App\Models\PurchaseSaleDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;

class Pay extends Component
{
    public $venta;
    public $pagos;
    public float $descuentoInput = 0;
    public $pagoIdAEliminar = null;
    public $modoPago;
    public $monto_pagado = null;
    public $monto_entregado = null;


    public $productos = [];
    public $descuento = 0;
    public $metodoPago = '';
    public $total = 0;

    public $estadoVenta=1;
    public $mostrarModalComprobante = false;
    public $iframeSrc;
    public bool $esGeneradoPorReserva = false;
    public ?int $reservaId = null;

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'open-modal-comprobante' => 'openModalPreview',
    ];

    public function mount($venta)
    {
        $this->venta = Sale::with([
            'customer',
            'reservationRoom.reservation',
            'detalles' => function ($query) {
                $query->whereNull('auditoriaFechaEliminacion')->with('product');
            },
            'pagos.metodoPago'
        ])->findOrFail($venta);

        if ($this->venta->detalles->isEmpty()) {
            return Redirect::route('sales.edit', ['id' => $this->venta->id]);
        }

        $this->pagos = $this->venta->pagos;
        $this->descuentoInput = $this->venta->descuento ?? 0;
        $this->modoPago = $this->venta->modo_pago_id;

        if ($this->venta->reservation_room_id !== null) {
            $this->esGeneradoPorReserva = true;
            $this->reservaId = optional($this->venta->reservationRoom)->reservation_id;
        }

        if ($this->venta->estado_venta_id == 2) {
            $this->iframeSrc = route('comprobante.preview', ['ventaId' => $venta]) . '?t=' . now()->timestamp;
        }

    }

    public function getDetallesFiltradosProperty()
    {
        return $this->venta->detalles->whereNull('auditoriaFechaEliminacion');
    }

    public function updatedDescuentoInput($value)
    {
        if ($this->venta->estado_venta_id === 2) return;

        $valor = is_numeric($value) ? floatval($value) : 0;
        $faltanteReal = max(0, $this->venta->total - $this->totalPagado);

        if ($valor > $faltanteReal) $valor = $faltanteReal;

        $this->descuentoInput = $valor;

        $this->venta->update([
            'descuento' => $valor
        ]);

        $this->venta->refresh();
    }

    public function getTotalPagadoProperty()
    {
        return $this->pagos->sum('monto_pagado');
    }

    public function getVueltoProperty()
    {
        $montoPagar = $this->modoPago == 2
            ? floatval($this->monto_pagado ?? 0)
            : floatval($this->venta->total - $this->venta->descuento - $this->totalPagado);

        return max(0, floatval($this->monto_entregado ?? 0) - $montoPagar);
    }

    public function getMetodosPagoProperty()
    {
        return Parameter::where('codigoParametro', 'METODO_PAGO')->get();
    }

    public function getModosPagoProperty()
    {
        return Parameter::where('codigoParametro', 'MODO_PAGO')->get();
    }

    public function iniciarProcesarPago()
    {
        $this->validate([
            'modoPago' => 'required|numeric',
            'metodoPago'      => 'required|numeric',
            'monto_entregado' => 'required|numeric|min:0.01',
        ]);

        $totalConDescuento = $this->venta->total - $this->venta->descuento;
        $restante = $totalConDescuento - $this->totalPagado;

        if ($this->modoPago == 2) {
            $this->validate([
                'monto_pagado' => 'required|numeric|min:0.01',
            ]);

            // Validar que el primer pago no sea igual o mayor al total con descuento
            if ($this->totalPagado == 0 && $this->monto_pagado >= $totalConDescuento) {
                $this->addError('monto_pagado', 'El primer pago debe ser menor al monto total (después del descuento).');
                $this->dispatch('errorPaySale', ['mensaje' => "El primer pago debe ser menor al monto total (después del descuento)."]);
                return;
            }

            // Validar que el monto que se desea pagar no sea mayor a lo que resta
            if ($this->monto_pagado > $restante) {
                $this->addError('monto_pagado', 'El monto ingresado supera el saldo pendiente.');
                $this->dispatch('errorPaySale', ['mensaje' => "El monto ingresado supera el saldo pendiente."]);
                return;
            }

        } else {
            // Modo completo: monto entregado no puede ser menor al restante
            if ($this->monto_entregado < $restante) {
                $this->addError('monto_entregado', 'Debe entregar al menos el saldo pendiente para completar el pago.');
                $this->dispatch('errorPaySale', ['mensaje' => "Debe entregar al menos el saldo pendiente para completar el pago."]);
                return;
            }
        }

        $this->dispatch('openModalProcesarPago');
    }
    public function getTotalConDescuentoProperty()
    {
        // Validar que $this->descuento sea numérico y positivo, si no, usar 0
        $descuento = is_numeric($this->descuento) && $this->descuento > 0 ? floatval($this->descuento) : 0;

        return max(0, $this->total - $descuento);
    }



    public function procesarPago()
    {
        // 1) Cálculos iniciales
        $totalDeuda   = $this->venta->total - $this->venta->descuento;
        $restante     = $totalDeuda - $this->totalPagado;
        $modoParcial  = $this->modoPago == 2;
        $yaTienePagos = $this->venta->pagos()
            ->whereNull('auditoriaFechaEliminacion')
            ->exists();

        // 2) Validaciones previas
        if ($restante <= 0) {
            $this->dispatch('errorPaySale', ['mensaje' => "La venta ya está completamente pagada."]);
            return;
        }

        if (! $this->venta) {
            $this->dispatch('errorPaySale', ['mensaje' => "Error inesperado: venta no encontrada."]);
            return;
        }

        if (! $yaTienePagos) {
            // Validaciones de stock y FIFO solo si es el primer pago
            foreach ($this->venta->detalles as $detalle) {
                $producto = Product::find($detalle->product_id);
                if (! $producto) {
                    $this->dispatch('errorPaySale', ['mensaje' => "Producto no encontrado."]);
                    return;
                }
                if ($producto->stock < $detalle->cantidad) {
                    $this->dispatch('errorPaySale', ['mensaje' => "Stock insuficiente para '{$producto->nombre}'."]);
                    return;
                }

                $cantidadRest = $detalle->cantidad;
                $lotes = PurchaseDetail::where('product_id', $detalle->product_id)
                    ->where('stock_restante', '>', 0)
                    ->whereHas('purchase', fn($q) => $q->where('estado_compra_id', 3))
                    ->orderBy('id')
                    ->get();

                if ($lotes->sum('stock_restante') < $cantidadRest) {
                    $this->dispatch('errorPaySale', ['mensaje' => "No hay suficiente stock FIFO para '{$producto->nombre}'."]);
                    return;
                }
            }
        }

        // Validar modo de pago
        if ($this->modoPago != $this->venta->modo_pago_id && $yaTienePagos) {
            $this->dispatch('errorPaySale', ['mensaje' => "No se puede cambiar el modo de pago: ya hay pagos registrados."]);
            return;
        }

        // Validar monto de pago
        if ($modoParcial) {
            if (empty($this->monto_pagado) || floatval($this->monto_pagado) <= 0) {
                $this->dispatch('errorPaySale', ['mensaje' => "Debe ingresar un monto parcial válido."]);
                return;
            }
            if ($this->totalPagado == 0 && floatval($this->monto_pagado) >= $totalDeuda) {
                $this->dispatch('errorPaySale', ['mensaje' => "El primer pago debe ser menor al monto total si es parcial."]);
                return;
            }
            if (floatval($this->monto_pagado) > $restante) {
                $this->dispatch('errorPaySale', ['mensaje' => "El monto parcial excede el saldo pendiente."]);
                return;
            }
        }

        if (empty($this->monto_entregado) || floatval($this->monto_entregado) <= 0) {
            $this->dispatch('errorPaySale', ['mensaje' => "Debe indicar con cuánto paga el cliente."]);
            return;
        }

        if (! $modoParcial && floatval($this->monto_entregado) < $restante) {
            $this->dispatch('errorPaySale', ['mensaje' => "El pago debe cubrir al menos el total pendiente."]);
            return;
        }

        // 3) Transacción
        DB::beginTransaction();
        try {
            // SOLO si es el primer pago, descontamos stock y aplicamos FIFO
            if (! $yaTienePagos) {
                foreach ($this->venta->detalles->whereNull('auditoriaFechaEliminacion') as $detalle) {
                    $producto = Product::findOrFail($detalle->product_id);
                    $producto->decrement('stock', $detalle->cantidad);

                    // Eliminar lógicamente anteriores (por si acaso)
                    PurchaseSaleDetail::where('sale_detail_id', $detalle->id)
                        ->update([
                            'auditoriaFechaEliminacion' => now(),
                            'auditoriaEliminadoPor'     => auth()->id(),
                        ]);

                    $cantidadRest = $detalle->cantidad;
                    $lotes = PurchaseDetail::where('product_id', $detalle->product_id)
                        ->where('stock_restante', '>', 0)
                        ->whereHas('purchase', fn($q) => $q->where('estado_compra_id', 3))
                        ->orderBy('id')
                        ->get();

                    foreach ($lotes as $lote) {
                        if ($cantidadRest <= 0) break;
                        $usar = min($cantidadRest, $lote->stock_restante);

                        PurchaseSaleDetail::create([
                            'sale_detail_id'         => $detalle->id,
                            'purchase_detail_id'     => $lote->id,
                            'cantidad_utilizada'     => $usar,
                            'costo_unitario'         => $lote->precio_unitario,
                            'auditoriaFechaCreacion' => now(),
                            'auditoriaCreadoPor'     => auth()->id(),
                        ]);

                        $lote->decrement('stock_restante', $usar);
                        $cantidadRest -= $usar;
                    }
                }
            }

            // 4) Actualizar modo de pago si es necesario
            if ($this->modoPago != $this->venta->modo_pago_id) {
                $this->venta->update(['modo_pago_id' => $this->modoPago]);
            }

            // 5) Registrar el pago
            $montoPagado     = $modoParcial ? floatval($this->monto_pagado) : min($restante, floatval($this->monto_entregado));
            $montoEntregado  = floatval($this->monto_entregado);
            $vuelto          = max(0, $montoEntregado - $montoPagado);

            Payment::create([
                'pagable_id'             => $this->venta->id,
                'pagable_type'           => Sale::class,
                'monto_pagado'           => $montoPagado,
                'monto_entregado'        => $montoEntregado,
                'vuelto'                 => $vuelto,
                'metodo_pago_id'         => $this->metodoPago,
                'estado_pago_id'         => 1,
                'fecha_pago'             => now(),
                'user_id'                => auth()->id(),
                'auditoriaFechaCreacion' => now(),
                'auditoriaCreadoPor'     => auth()->id(),
            ]);

            // 6) Verificar si ya está completamente pagado
            $nuevoTotalPagado = $this->totalPagado + $montoPagado;
            if ($nuevoTotalPagado >= $totalDeuda) {
                $this->venta->update([
                    'pagado'      => true,
                    'estado_venta_id'   => 2, // Completada
                    'fecha_venta' => now(),
                ]);
                $this->iframeSrc = route('comprobante.preview', ['ventaId' => $this->venta->id]) . '?t=' . now()->timestamp;
                $this->mostrarModalComprobante = true;
                $this->dispatch('open-modal-comprobante');
            } else {
                $this->venta->update([
                    'pagado'    => false,
                    'estado_venta_id' => 1, // Pendiente
                ]);
            }

            DB::commit();

            $this->reset(['metodoPago', 'monto_pagado', 'monto_entregado']);
            $this->dispatch('successPaySale', ['mensaje' => "Pago registrado correctamente."]);
            $this->dispatch('refreshComponent');
            $this->venta->refresh();
            $this->pagos = $this->venta->pagos()->with('metodoPago')->get();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error procesando pago: {$e->getMessage()}");
            $this->dispatch('errorPaySale', ['mensaje' => "Error al procesar el pago."]);
        }
    }


    public function confirmarEliminacion($pagoId)
    {
        $this->pagoIdAEliminar = $pagoId;
        $this->dispatch('abrirModalEliminarPago');
    }

    public function eliminarPago()
    {
        $pago = Payment::findOrFail($this->pagoIdAEliminar);

        // Eliminar lógicamente el pago
        $pago->update([
            'estado_pago_id'             => 3,
            'auditoriaFechaEliminacion' => now(),
            'auditoriaEliminadoPor'     => auth()->id(),
        ]);

        $this->venta->refresh();
        $this->pagos = $this->venta->pagos()->with('metodoPago')->get();

        // Verificamos si ya no hay pagos válidos
        $tienePagosActivos = $this->venta->pagos()
            ->whereNull('auditoriaFechaEliminacion')
            ->exists();

        if (! $tienePagosActivos) {
            // Restaurar stock general y stock FIFO
            foreach ($this->venta->detalles->whereNull('auditoriaFechaEliminacion') as $detalle) {
                $producto = Product::find($detalle->product_id);
                if ($producto) {
                    $producto->stock += $detalle->cantidad;
                    $producto->save();
                }

                // Restaurar stock FIFO
                $relacionesFIFO = PurchaseSaleDetail::where('sale_detail_id', $detalle->id)
                    ->whereNull('auditoriaFechaEliminacion')
                    ->get();

                foreach ($relacionesFIFO as $relacion) {
                    $lote = PurchaseDetail::find($relacion->purchase_detail_id);
                    if ($lote) {
                        $lote->stock_restante += $relacion->cantidad_utilizada;
                        $lote->save();
                    }

                    // Eliminar lógicamente la relación
                    $relacion->auditoriaFechaEliminacion = now();
                    $relacion->auditoriaEliminadoPor = auth()->id();
                    $relacion->save();
                }
            }
        }

        // Actualizar estado de la venta
        $totalDeuda = $this->venta->total - $this->venta->descuento;
        if ($this->totalPagado < $totalDeuda) {
            $this->venta->update([
                'pagado' => false,
                'estado_venta_id' => 1,
            ]);
        }

        $this->pagoIdAEliminar = null;
        $this->dispatch('successPaySale', ['mensaje' => "Pago eliminado correctamente."]);
        $this->dispatch('refreshComponent');
    }


    public function openModalPreview()
    {
        $this->vistaComprobantePreview($this->venta->id);
    }
    public function vistaComprobantePreview($ventaId)
    {
        $venta = Sale::with('customer')->findOrFail($ventaId);

        $productos = SaleDetail::with('product')
            ->where('sale_id', $ventaId)
            ->whereNull('auditoriaFechaEliminacion')
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

    public function cancelar()
    {
        DB::beginTransaction();

        try {
            foreach ($this->venta->pagos as $pago) {
                $pago->update([
                    'estado_pago_id'         => 3,
                    'auditoriaFechaEliminacion' => now(),
                    'auditoriaEliminadoPor'     => auth()->id(),
                ]);
            }

            foreach ($this->venta->detalles->whereNull('auditoriaFechaEliminacion') as $detalle) {
                // Restaurar stock total del producto
                $producto = Product::find($detalle->product_id);
                if ($producto) {
                    $producto->stock += $detalle->cantidad;
                    $producto->save();
                }

                // Restaurar stock FIFO (de forma lógica)
                $relacionesFIFO = DB::table('purchase_sale_details')
                    ->where('sale_detail_id', $detalle->id)
                    ->whereNull('auditoriaFechaEliminacion') // Solo relaciones activas
                    ->get();

                foreach ($relacionesFIFO as $relacion) {
                    $lote = PurchaseDetail::find($relacion->purchase_detail_id);
                    if ($lote) {
                        $lote->stock_restante += $relacion->cantidad_utilizada;
                        $lote->save();
                    }

                    // Marcar la relación como eliminada lógicamente
                    DB::table('purchase_sale_details')
                        ->where('sale_detail_id', $detalle->id)
                        ->where('purchase_detail_id', $relacion->purchase_detail_id)
                        ->update([
                            'auditoriaFechaEliminacion' => now(),
                            'auditoriaEliminadoPor'     => auth()->id(),
                        ]);
                }
            }

            $this->venta->update([
                'pagado' => false,
                'estado_venta_id' => 3, // Cancelada
                'descuento' => 0,
                'auditoriaFechaModificacion' => now(),
                'auditoriaModificadoPor' => auth()->id(),
            ]);

            $this->venta->refresh();
            $this->pagos = $this->venta->pagos()->with('metodoPago')->get();

            DB::commit();

            $this->dispatch('successPaySale', ['mensaje' => 'Venta cancelada correctamente.']);
            $this->dispatch('cerrarModalCancelarVenta');
            $this->dispatch('refreshComponent');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al cancelar venta: ' . $e->getMessage());
            $this->addError('cancelacion', 'Ocurrió un error al intentar cancelar la venta.');
            $this->dispatch('errorPaySale', ['mensaje' => "Ocurrió un error al intentar cancelar la venta."]);
        }
    }


    public function render()
    {
        return view('livewire.sales.pay', [
            'venta'       => $this->venta,
            'pagos'       => $this->pagos,
            'metodos'     => $this->metodosPago,
            'totalPagado' => $this->totalPagado,
            'vuelto'      => $this->vuelto,
            'modosPago'   => $this->modosPago,
        ]);
    }
}
