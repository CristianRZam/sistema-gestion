<div class="p-6 bg-white dark:bg-zinc-900 rounded-lg shadow-md space-y-6">

    {{-- Título y botón de imprimir --}}
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Detalles de pago</h2>

        <div class="flex items-center gap-2">
            @can('crear compra')
                <a href="{{ route('purchases.register') }}"
                   class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition cursor-pointer">
                    Realizar nueva compra
                </a>
            @endcan

            @if($estadoCompra != 1)
                <flux:modal.trigger name="comprobante-preview">
                    <button
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition cursor-pointer"
                        x-data
                        x-on:click.prevent="$dispatch('open-modal-comprobante')">
                        {{ __('Imprimir ticket de compra') }}
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
                wire:model.live="proveedor_nombre"
                :label="__('Nombre del Proveedor')"
                type="text"
                disabled
            />
        </div>
        <div>
            <flux:input
                wire:model.live="proveedor_documento"
                :label="__('DNI / RUC')"
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
                @if(in_array($estadoCompra, [3]))
                    <th class="py-2 px-4 text-sm text-red-600 dark:text-red-400">Pérdidas</th>
                @endif
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Producto</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Precio</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Cantidad</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @forelse($productos as $index => $detalle)
                @php
                    $fallidas = collect($detalle['fallidas'] ?? []);
                    $fallidasQueDescuentan = $fallidas->filter(fn($f) => in_array($f['tipo_id'], [1, 3]));
                    $cantidadDescontada = $fallidasQueDescuentan->sum('cantidad_fallida');
                    $subtotalDescontado = ($detalle['cantidad'] - $cantidadDescontada) * $detalle['precio'];
                    $subtotalOriginal = $detalle['cantidad'] * $detalle['precio'];
                @endphp
                <tr class="border-t border-gray-200 dark:border-gray-600">
                    @if(in_array($estadoCompra, [3]))
                        <td class="py-2 px-4 space-y-1">
                            <flux:modal.trigger name="product-loss">
                                <button
                                    class="block mt-1 text-xs text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
                                    x-data
                                    x-on:click.prevent="$dispatch('open-modal-loss', { id: {{ $detalle['id'] }} })">
                                    {{ __('Registrar pérdida') }}
                                </button>
                            </flux:modal.trigger>
                        </td>
                    @endif

                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">{{ $detalle['nombre'] }}</td>

                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                        S/ {{ number_format($detalle['precio'], 2) }}
                    </td>

                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                        {{ $detalle['cantidad'] }}
                        @if($fallidas->count() > 0)
                            <div class="text-xs text-red-500">
                                -{{ $fallidas->sum('cantidad_fallida') }} pérdidas
                            </div>
                        @endif
                    </td>

                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                        @if($fallidasQueDescuentan->count() > 0)
                            <div class="line-through text-sm text-red-500">
                                S/ {{ number_format($subtotalOriginal, 2) }}
                            </div>
                            <div class="text-green-600 font-semibold">
                                S/ {{ number_format($subtotalDescontado, 2) }}
                            </div>
                        @else
                            S/ {{ number_format($subtotalOriginal, 2) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ in_array($estadoCompra, [3]) ? 5 : 4 }}"
                        class="py-3 px-4 text-gray-500 dark:text-gray-400 text-center">
                        No hay productos agregados.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @livewire('purchases.loss')

    {{-- Método de pago y resumen --}}
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mt-4">
            <!-- Método de pago -->
            <div class="w-full md:w-1/2">
                <flux:select
                    wire:model.defer="metodoPago"
                    :label="__('Método de Pago')"
                    required
                    :disabled="$estadoCompra == 3"
                >
                    <option value="">{{ __('Seleccione') }}</option>
                    @foreach($metodosPago as $metodo)
                        <option value="{{ $metodo->idParametro }}">
                            {{ $metodo->nombre }}
                        </option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Estado Compra -->
            <div class="w-full md:w-1/2">
                <flux:select
                    wire:model.defer="estadoCompra"
                    :label="__('Estado Compra')"
                    required
                    :disabled="$estadoCompra == 3"
                >
                    <option value="">{{ __('Seleccione') }}</option>
                    @foreach($estadosCompra as $estado)
                        <option value="{{ $estado->idParametro }}">
                            {{ $estado->nombre }}
                        </option>
                    @endforeach
                </flux:select>
            </div>
        </div>


        <div class="flex flex-col md:flex-row items-start md:items-end justify-end gap-6 mt-4">
            <!-- Total a pagar -->
            <div class="w-full md:w-1/2 mt-2 md:mt-0">
                <div class="text-right font-semibold text-lg space-y-1">
                    <p class="text-gray-800 dark:text-gray-100">
                        {{ __('Total:') }}
                    </p>
                    <p class="text-green-600 dark:text-green-400 text-2xl">
                        S/ {{ number_format($this->totalConDescuento, 2) }}
                    </p>
                </div>
            </div>

        </div>


        @error('stock')
        <div class="text-red-600 dark:text-red-400 text-sm mt-2">{{ $message }}</div>
        @enderror



    </div>

    {{-- Botón de acción --}}
    <div class="flex justify-between items-center mt-6">
        {{-- Lado izquierdo --}}
        @can('editar compra')
            <div>
                @if($estadoCompra == 1 || $estadoCompra == 2)
                    <a href="{{ route('purchases.edit', $compra) }}" class="text-sm text-blue-600 dark:text-white hover:underline">
                        ← Editar compra
                    </a>
                @endif
            </div>
        @endcan

        {{-- Lado derecho --}}
        <div class="flex gap-2">
            @can('eliminar compra')
                @if($estadoCompra == 2 || $estadoCompra == 1 )
                    <flux:modal.trigger name="confirm-purchase-deletion">
                        <flux:button class="cursor-pointer" variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal-purchase-delete', 'confirm-purchase-deletion')">
                            {{ __('Cancelar compra') }}
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            @endcan

            @can('pagar compra')
                @if($estadoCompra == 1 || $estadoCompra == 2)
                    <button wire:click="guardar"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition cursor-pointer">
                        Guardar
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <flux:modal name="confirm-purchase-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="eliminarCompra" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que deseas cancelar esta compra?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Una vez cancelada, esta compra no podrá recuperarse. Esta acción es permanente. Confirma si deseas continuar.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                @can('eliminar venta')
                    @if($estadoCompra == 2 || $estadoCompra == 1 || $estadoCompra == 3)
                        <flux:button variant="danger" type="submit">{{ __('Continuar') }}</flux:button>
                    @endif
                @endcan
            </div>
        </form>
    </flux:modal>


    <script>
        window.addEventListener('open-modal-comprobante', () => {
            Flux.modal('comprobante-preview').show();
        });
    </script>

</div>
