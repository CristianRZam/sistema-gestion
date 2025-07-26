<div class="p-6 bg-white dark:bg-zinc-900 rounded-lg shadow-md space-y-6">

    {{-- Título y botón de imprimir --}}
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Detalles de pago</h2>

        <div class="flex items-center gap-2">
            @if($reservaId)
                <a href="{{ route('reservations.register', ['id' => $reservaId]) }}"
                   class="px-6 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition cursor-pointer">
                    Volver a la reserva
                </a>
            @else
                @can('crear venta')
                    <a href="{{ route('sales.register') }}"
                       class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition cursor-pointer">
                        Realizar nueva venta
                    </a>
                @endcan
            @endif

            @if($venta->estado_venta_id == 2)
                <flux:modal.trigger name="comprobante-preview">
                    <button
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition cursor-pointer"
                        x-data
                        x-on:click.prevent="$dispatch('open-modal-comprobante')">
                        {{ __('Imprimir comprobante') }}
                    </button>
                </flux:modal.trigger>
            @endif
        </div>
    </div>



    <flux:modal name="comprobante-preview" :show="$mostrarModalComprobante" focusable class="max-w-lg">
        <div class="mb-4">
            <flux:heading size="lg">{{ __('Vista previa del comprobante') }}</flux:heading>
        </div>

        <div class="border rounded shadow-sm" style="height: 400px;">
            @if($iframeSrc)
                <iframe src="{{ $iframeSrc }}"
                        class="w-full h-full"
                        frameborder="0"></iframe>
            @endif
        </div>
    </flux:modal>

    {{-- Datos del Cliente --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <flux:input
                :label="__('Cliente')"
                :value="$venta->customer?->nombre ?? '---'"
                type="text"
                disabled
            />
        </div>
        <div>
            <flux:input
                :label="__('DNI / RUC')"
                :value="$venta->customer?->documento ?? '---'"
                type="text"
                disabled
            />
        </div>
    </div>

    {{-- Lista de productos --}}
    <div>
        <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">Productos:</h3>

        <table class="w-full text-left border-t border-gray-200 dark:border-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Producto</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Precio</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Cantidad</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @forelse($this->detallesFiltrados as $detalle)
                <tr class="border-t border-gray-200 dark:border-gray-600">
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">{{ $detalle->product?->nombre }}</td>
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">{{ $detalle->cantidad }}</td>
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">S/ {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-3 px-4 text-gray-500 dark:text-gray-400 text-center">No hay productos agregados.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @error('productos')
        <div class="text-red-600 dark:text-red-400 text-sm mt-2">{{ $message }}</div>
        @enderror
    </div>

    {{-- Resumen de totales --}}
    <div class="text-right space-y-2 text-gray-800 dark:text-gray-100">

        {{-- Subtotal --}}
        <div>Subtotal:
            <strong>S/ {{ number_format($venta->total, 2) }}</strong>
        </div>

        {{-- Descuento --}}
        <div class="text-left md:text-right">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descuento (S/)</label>

            @if($venta->estado_venta_id !== 2 && $venta->estado_venta_id !== 3)
                <input
                    type="number"
                    min="0"
                    step="0.01"
                    wire:model.live.debounce="descuentoInput"
                    class="mt-1 w-full md:w-48 text-right rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 dark:bg-zinc-800 dark:text-white"
                    placeholder="0.00"
                />
            @else
                <div class="text-green-600 dark:text-green-400 font-semibold">
                    S/ {{ number_format($venta->descuento, 2) }}
                </div>
            @endif
        </div>


        {{-- Total a pagar --}}
        <div>Total a pagar:
            <strong class="text-green-600 dark:text-green-400">
                S/ {{ number_format($venta->total - $venta->descuento, 2) }}
            </strong>
        </div>

        {{-- Pagado --}}
        <div>Pagado:
            <strong class="text-blue-600 dark:text-blue-400">
                S/ {{ number_format($totalPagado, 2) }}
            </strong>
        </div>

        {{-- Restante --}}
        <div>Restante:
            <strong class="text-red-600 dark:text-red-400">
                S/ {{ number_format($venta->total - $venta->descuento - $totalPagado, 2) }}
            </strong>
        </div>
    </div>

    @if($venta->estado_venta_id !== 3)
        {{-- Formulario de nuevo pago --}}
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="flex items-center justify-between mb-4">
                @if(($venta->total - $venta->descuento - $totalPagado) > 0)
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Nuevo Pago</h3>
                @endif

                @if($venta->estado_venta_id === 1 && $venta->pagos->isEmpty())
                    <a href="{{ route('sales.edit', $venta->id) }}"
                       class="text-sm text-blue-600 hover:underline dark:text-blue-400">
                        ✏️ Editar venta
                    </a>
                @endif
            </div>

            @if(($venta->total - $venta->descuento - $totalPagado) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Modo de pago --}}
                    <div>
                        <flux:select
                            wire:model.live="modoPago"
                            :label="__('Modo de pago')"
                            required
                            :disabled="count($pagos) > 0"
                        >
                            <option value="">{{ __('Seleccione...') }}</option>
                            @foreach($modosPago as $modo)
                                <option value="{{ $modo->idParametro }}">{{ $modo->nombre }}</option>
                            @endforeach
                        </flux:select>
                    </div>




                    {{-- Método de pago --}}
                    <div>
                        <flux:select
                            wire:model.live="metodoPago"
                            :label="__('Método de pago')"
                            required
                        >
                            <option value="">{{ __('Seleccione...') }}</option>
                            @foreach($metodos as $m)
                                <option value="{{ $m->idParametro }}">{{ $m->nombre }}</option>
                            @endforeach
                        </flux:select>
                    </div>

                    {{-- Si es modo por partes, permitir ingresar monto pagado --}}
                    @if($modoPago == 2)
                        <div>
                            <flux:input
                                wire:model.live="monto_pagado"
                                :label="__('Monto a pagar (S/)')"
                                type="number"
                                step="0.01"
                                min="0"
                            />
                        </div>
                    @endif

                    {{-- Con cuánto paga --}}
                    <div class="md:col-span-1 {{ $modoPago == 2 ? '' : 'md:col-span-2' }}">
                        <flux:input
                            wire:model.live="monto_entregado"
                            :label="__('Con cuánto paga (S/)')"
                            type="number"
                            step="0.01"
                            min="0"
                        />
                    </div>
                </div>

                {{-- Mostrar vuelto solo si hay monto entregado --}}
                @if($monto_entregado > 0)
                    <div class="text-right mt-2 text-green-600 dark:text-green-400">
                        Vuelto: S/ {{ number_format($vuelto, 2) }}
                    </div>
                @endif

                <div class="mt-4 flex flex-col md:flex-row md:justify-end items-stretch gap-2 md:gap-4">
                    <button
                        wire:click="iniciarProcesarPago"
                        class="w-full md:w-auto px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition cursor-pointer"
                    >
                        Registrar Pago
                    </button>
                </div>
            @else
                <div class="mt-4 flex flex-col md:flex-row md:justify-end items-stretch gap-2 md:gap-4">
                    <div class="w-full md:w-auto inline-block px-6 py-2 bg-green-100 text-green-700 rounded-md text-center">
                        Pago completado
                    </div>
                </div>
            @endif
        </div>
    @endif

    <flux:modal name="confirm-pay-sale" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit.prevent="procesarPago" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Deseas confirmar el pago de la venta?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Una vez procesado el pago, no podrás modificar los productos ni el cliente. ¿Estás seguro de continuar?') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:modal.close>
                    <flux:button variant="danger" type="submit">{{ __('Continuar') }}</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>

    {{-- Si no esta en cancelado --}}
    @if($venta->estado_venta_id !== 3)
        {{-- Historial de pagos --}}
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Historial de Pagos</h3>

            @if(count($pagos))
                <table class="w-full text-left border-t border-gray-200 dark:border-gray-700 text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="py-2 px-4 text-gray-600 dark:text-gray-300">Fecha</th>
                        <th class="py-2 px-4 text-gray-600 dark:text-gray-300">Método</th>
                        <th class="py-2 px-4 text-gray-600 dark:text-gray-300">Monto Pagado</th>
                        <th class="py-2 px-4 text-gray-600 dark:text-gray-300">Con cuánto pagó</th>
                        <th class="py-2 px-4 text-gray-600 dark:text-gray-300">Vuelto</th>
                        <th class="py-2 px-4 text-gray-600 dark:text-gray-300">Estado</th>
                        <th class="py-2 px-4 text-gray-600 dark:text-gray-300">Registrado por</th>
                        <th class="py-2 px-4 text-gray-600 dark:text-gray-300 text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($pagos as $p)
                        <tr class="border-t border-gray-200 dark:border-gray-600">
                            <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                                {{ $p->metodoPago?->nombre ?? 'N/A' }}
                            </td>
                            <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                                S/ {{ number_format($p->monto_pagado, 2) }}
                            </td>
                            <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                                S/ {{ number_format($p->monto_entregado ?? 0, 2) }}
                            </td>
                            <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                                S/ {{ number_format($p->vuelto ?? 0, 2) }}
                            </td>
                            <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                                {{ $p->estadoPago?->nombre ?? 'N/A' }}
                            </td>
                            <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                                {{ $p->user?->name ?? '—' }}
                            </td>
                            <td class="py-2 px-4 text-center">
                                <button
                                    wire:click="confirmarEliminacion({{ $p->id }})"
                                    x-init="tippy($el, { content: 'Eliminar pago' })"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-600 text-sm cursor-pointer"
                                >
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 dark:text-gray-400">No hay pagos registrados.</p>
            @endif
        </div>
    @endif

    <flux:modal name="confirm-pay-delete" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit.prevent="eliminarPago" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Eliminar pago seleccionado?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Esta acción no se puede deshacer.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:modal.close>
                    <flux:button variant="danger" type="submit">{{ __('Eliminar') }}</flux:button>
                </flux:modal.close>
            </div>
        </form>
    </flux:modal>

    @if($venta->estado_venta_id !== 3)
        <div class="mt-4 flex flex-col md:flex-row md:justify-end items-stretch gap-2 md:gap-4">
            <flux:modal.trigger name="confirm-sale-deletion">
                <flux:button class="w-full md:w-auto cursor-pointer" variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal-sale-delete', 'confirm-sale-deletion')">
                    {{ __('Cancelar venta') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    @endif

    <flux:modal name="confirm-sale-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="cancelar" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que deseas cancelar esta venta?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Una vez cancelada, esta venta no podrá recuperarse. Esta acción es permanente. Confirma si deseas continuar.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                @can('eliminar venta')
                    <flux:button variant="danger" type="submit">{{ __('Continuar') }}</flux:button>
                @endcan
            </div>
        </form>
    </flux:modal>

</div>

<script>
    if (!window._cerrarModalCancelarVenta) {
        window._cerrarModalCancelarVenta = true;

        window.addEventListener('cerrarModalCancelarVenta', () => {
            setTimeout(() => {
                Flux.modal('confirm-sale-deletion').close();
            }, 100);
        });

    }

    if (!window._abrirModalProcesarPago) {
        window._abrirModalProcesarPago = true;

        window.addEventListener('openModalProcesarPago', () => {
            setTimeout(() => {
                Flux.modal('confirm-pay-sale').show();
            }, 100);
        });

    }

    if (!window._abrirModalEliminarPago) {
        window._abrirModalEliminarPago = true;

        window.addEventListener('abrirModalEliminarPago', () => {
            setTimeout(() => {
                Flux.modal('confirm-pay-delete').show();
            }, 100);
        });
    }

    if (!window._errorPaySale) {
        window._errorPaySale = true;

        window.addEventListener('errorPaySale', (event) => {
            toastr.error(event.detail[0].mensaje);
        });
    }

    if (!window._successPaySale) {
        window._successPaySale = true;

        window.addEventListener('successPaySale', (event) => {
            toastr.success(event.detail[0].mensaje);
        });
    }
</script>
