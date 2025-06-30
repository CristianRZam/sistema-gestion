<div>
    <!-- Modal de pérdida -->
    <flux:modal name="product-loss" focusable class="max-w-lg">
        <form class="flex flex-col space-y-6 p-6">
            <!-- Título -->
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Registrar pérdida de producto
            </h2>

            <!-- Información del producto -->
            <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-md space-y-1">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <strong>Producto:</strong> {{ $productoSeleccionado['nombre'] ?? 'Producto' }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <strong>Cantidad comprada:</strong> {{ $productoSeleccionado['cantidad_comprada'] ?? 0 }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    <strong>Cantidad vendida:</strong> {{ $productoSeleccionado['cantidad_utilizada'] ?? 0 }}
                </p>
            </div>

            <flux:select wire:model.live="tipoPerdida" :label="__('Tipo de pérdida')" required>
                <option value="">{{ __('Seleccione un tipo') }}</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->idParametro }}">{{ $tipo->nombre }}</option>
                @endforeach
            </flux:select>

            <!-- Mensaje explicativo debajo del tipo de pérdida -->
            @if ($tipoPerdida == 1)
                <p class="text-sm text-blue-600 dark:text-blue-400">
                    El producto se devuelve al proveedor, se descuenta del stock y se recupera el dinero.
                </p>
            @elseif ($tipoPerdida == 2)
                <p class="text-sm text-yellow-600 dark:text-yellow-400">
                    El producto se pierde definitivamente. No se recupera el dinero y se descuenta del stock.
                </p>
            @elseif ($tipoPerdida == 3)
                <p class="text-sm text-green-600 dark:text-green-400">
                    Se devuelve el dinero, pero el producto permanece en stock.
                </p>
            @endif


            <flux:select wire:model.live="motivoPerdida" :label="__('Motivo de la pérdida')" required>
                <option value="">{{ __('Seleccione un motivo') }}</option>
                @foreach($motivos as $motivo)
                    <option value="{{ $motivo->idParametro }}">{{ $motivo->nombre }}</option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model="cantidadFallida"
                type="number"
                :label="__('Cantidad fallida')"
                min="1"
                :max="$productoSeleccionado['stock_disponible'] ?? 0"
                required
            />

            <!-- Botón de guardar -->
            <div class="pt-4">
                <flux:button class="w-full cursor-pointer" variant="danger" wire:click="guardarPerdidaVenta">
                    {{ __('Guardar pérdida') }}
                </flux:button>
            </div>

            @if ($purchaseLossId)
                <div class="pt-2">
                    <flux:modal.trigger name="confirm-customer-deletion">
                        <flux:button class="w-full cursor-pointer" variant="filled" confirm>
                            {{ __('Eliminar pérdida') }}
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            @endif
        </form>
    </flux:modal>

    <!-- Modal de confirmación -->
    <flux:modal name="confirm-customer-deletion" focusable class="max-w-lg">
        <form wire:submit.prevent="eliminarPerdidaVenta" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que quieres eliminar el registro de pérdida?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Una vez eliminado, esta acción no se puede deshacer.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button class="cursor-pointer" variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button class="cursor-pointer" variant="danger" type="submit">{{ __('Eliminar') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Script para cerrar ambos modales -->
    <script>
        window.addEventListener('cerrarModalProductLoss', () => {
            Flux.modal('product-loss').close();
            Flux.modal('confirm-customer-deletion').close();
        });
    </script>
</div>
