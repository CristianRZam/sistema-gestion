<flux:modal name="product-detail" class="max-w-3xl" focusable>
    <div class="flex flex-col space-y-6 p-6">
        <div class="flex space-x-6">
            <!-- Imagen producto (50%) -->
            <div class="w-1/2 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-md overflow-hidden">
                <img
                    src="{{ $productoSeleccionado['imagen'] ?? 'https://img.kwcdn.com/product/Fancyalgo/VirtualModelMatting/c4c714885c2839352082b265af3d3352.jpg?imageView2/2/w/1300/q/90/format/webp' }}"
                    alt="{{ $productoSeleccionado['nombre'] ?? 'Producto' }}"
                    class="object-contain max-h-64 w-full"
                />
            </div>

            <!-- Detalles producto (50%) -->
            <div class="w-1/2 flex flex-col justify-start">
                <!-- Título -->
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $productoSeleccionado['nombre'] ?? 'Nombre del producto' }}
                </h2>

                <!-- Descripción -->
                <p class="mt-3 text-gray-700 dark:text-gray-300">
                    {{ $productoSeleccionado['descripcion'] ?? 'Descripción no disponible.' }}
                </p>

                <!-- Precio -->
                <p class="mt-4 text-lg font-semibold text-gray-800 dark:text-white">
                    {{ __('Precio:') }} S/ {{ number_format($productoSeleccionado['precio'] ?? 0, 2) }}
                </p>

                <!-- Stock -->
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Stock disponible:') }} {{ $productoSeleccionado['stock'] ?? 0 }}
                </p>

                <!-- Estrellas -->
                <div class="mt-3 flex space-x-1 text-yellow-400">
                    @php
                        $rating = $productoSeleccionado['rating'] ?? 5;
                    @endphp
                    @for($i = 0; $i < 5; $i++)
                        @if($i < $rating)
                            <flux:icon name="star" class="w-5 h-5" />
                        @else
                            <flux:icon name="star" class="w-5 h-5 text-gray-300 dark:text-gray-600" />
                        @endif
                    @endfor
                </div>

                <!-- Selección de cantidad -->
                <div class="w-full mt-4">
                    <flux:select
                        wire:model.defer="cantidadSeleccionada"
                        :label="__('Cantidad')"
                        required
                    >
                        @php
                            $stockDisponible = $productoSeleccionado['stock'] ?? 0;
                        @endphp

                        @for($i = 1; $i <= $stockDisponible; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor

                    </flux:select>
                </div>
            </div>
        </div>

        <!-- Botón Agregar al carrito -->
        <div class="pt-4">
            <flux:button class="w-full cursor-pointer" variant="primary" wire:click="agregarProductoConCantidad">
                {{ __('Agregar al carrito') }}
            </flux:button>
        </div>

    </div>
</flux:modal>

<script>
    // Si quieres cerrar el modal desde Livewire:
    window.addEventListener('cerrarModalProductDetail', () => {
        Flux.modal('product-detail').close();
    });
</script>
