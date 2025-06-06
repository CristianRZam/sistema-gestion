<div class="p-6 bg-white dark:bg-zinc-900 rounded-lg shadow-md space-y-6">

    {{-- Título y botón de imprimir --}}
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Detalles de pago</h2>

        <div class="flex items-center gap-2">
            @can('crear venta')
                <a href="{{ route('sales.register') }}"
                   class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition cursor-pointer">
                    Realizar nueva venta
                </a>
            @endcan

            @if($estadoVenta == 2)
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
                wire:model.live="cliente_nombre"
                :label="__('Nombre del Cliente')"
                type="text"
                disabled
            />
        </div>
        <div>
            <flux:input
                wire:model.live="cliente_documento"
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
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Producto</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Precio</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Cantidad</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @forelse($productos as $producto)
                <tr class="border-t border-gray-200 dark:border-gray-600">
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">{{ $producto['nombre'] }}</td>
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">S/ {{ number_format($producto['precio'], 2) }}</td>
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">{{ $producto['cantidad'] }}</td>
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">S/ {{ number_format($producto['precio'] * $producto['cantidad'], 2) }}</td>
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

    {{-- Método de pago y resumen --}}
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row justify-between gap-4">
            <div class="flex-1">
                <flux:select
                    wire:model.defer="metodoPago"
                    :label="__('Método de Pago')"
                    required
                >
                    <option value="">{{ __('Seleccione') }}</option>
                    @foreach($metodosPago as $metodo)
                        <option value="{{ $metodo->idParametro }}">
                            {{ $metodo->nombre }}
                        </option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex-1">
                <flux:input
                    wire:model.live="descuento"
                    :label="__('Descuento (S/) ')"
                    type="number"
                    min="0"
                />
            </div>
        </div>

        @error('stock')
        <div class="text-red-600 dark:text-red-400 text-sm mt-2">{{ $message }}</div>
        @enderror

        <div class="flex justify-between font-semibold text-lg mt-4">
            <span class="text-gray-800 dark:text-gray-100">Total a Pagar:</span>
            <span class="text-green-600 dark:text-green-400">S/ {{ number_format($this->totalConDescuento, 2) }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input
                wire:model.live="pago_con"
                :label="__('Con cuánto pagó (S/)')"
                type="number"
                min="0"
                step="0.01"
            />

            <flux:input
                :label="__('Vuelto (S/)')"
                :value="'S/ ' . number_format($this->vuelto, 2)"
                type="text"
                disabled
            />
        </div>

    </div>

    {{-- Botón de acción --}}
    <div class="flex justify-between items-center mt-6">
        {{-- Lado izquierdo --}}
        @can('editar venta')
            <div>
                @if($estadoVenta == 1)
                    <a href="{{ route('sales.edit', $venta) }}" class="text-sm text-blue-600 dark:text-white hover:underline">
                        ← Editar venta
                    </a>
                @endif
            </div>
        @endcan

        {{-- Lado derecho --}}
        <div class="flex gap-2">
            @can('eliminar venta')
                @if($estadoVenta == 2 || $estadoVenta == 1)
                    <flux:modal.trigger name="confirm-sale-deletion">
                        <flux:button class="cursor-pointer" variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal-sale-delete', 'confirm-sale-deletion')">
                            {{ __('Cancelar venta') }}
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            @endcan

            @can('pagar venta')
                @if($estadoVenta == 1)
                    <button wire:click="procesarPago"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition cursor-pointer">
                        Pagar
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <flux:modal name="confirm-sale-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="eliminarVenta" class="space-y-6">
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
                    @if($estadoVenta == 2 || $estadoVenta == 1)
                        <flux:button variant="danger" type="submit">{{ __('Continuar') }}</flux:button>
                    @endif
                @endcan
            </div>
        </form>
    </flux:modal>


    <script>
        window.addEventListener('open-modal-comprobante', () => {
            console.log("hahaha")
            Flux.modal('comprobante-preview').show();
        });
    </script>

</div>
