<section>
    <div class="flex justify-between h-screen overflow-hidden pl-4 pr-[15rem] pt-0">
        <!-- Contenido central -->
        <div class="flex-1 max-w-full pr-8 flex flex-col">
            <div class="space-y-8">
                <!-- Detalles fijos (Fecha, Cliente, etc.) -->
                <div>
                    <flux:heading size="lg">
                        {{ __('Registrar nueva venta') }}
                    </flux:heading>
                    <flux:subheading>
                        {{ __('Complete los campos para registrar una nueva venta.') }}
                    </flux:subheading>
                </div>

                <!-- Información del cliente -->
                <div class="relative flex border border-gray-300 dark:border-gray-700 border-l-0 rounded-lg bg-white dark:bg-zinc-900 overflow-hidden">
                    <!-- Botón de editar con ícono -->
                    @if(!$esGeneradoPorReserva)
                        <div class="absolute top-2 right-2 flex items-center gap-3">
                            {{-- Botón Editar Cliente --}}
                            <flux:modal.trigger name="register-customer">
                                <button
                                    class="text-blue-600 hover:text-blue-800 transition cursor-pointer"
                                    x-on:click.prevent="$dispatch('open-modal-customer', { id: null, modo: 'venta' })"
                                    aria-label="{{ __('Editar cliente') }}"
                                >
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                            </flux:modal.trigger>

                            {{-- Botón Eliminar Cliente --}}
                            <button
                                wire:click="eliminarClienteSeleccionado"
                                class="text-red-600 hover:text-red-800 transition cursor-pointer"
                                aria-label="{{ __('Eliminar cliente') }}"
                            >
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    @endif


                    <!-- Franja izquierda intercalada, espaciada y con altura completa -->
                    <div class="w-1 flex flex-col justify-between py-0">
                        <div class="h-4 bg-red-500"></div>
                        <div class="h-4 bg-cyan-500"></div>
                        <div class="h-4 bg-red-500"></div>
                        <div class="h-4 bg-cyan-500"></div>
                    </div>

                    <!-- Contenido informativo del cliente -->
                    <div class="flex-1 p-4">
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>{{ __('Nombre:') }}</strong> {{ $cliente_seleccionado['nombre'] ?? __('No seleccionado') }}
                        </p>
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>{{ __('Documento:') }}</strong> {{ $cliente_seleccionado['dni'] ?? '---' }}
                        </p>
                        <p class="text-gray-700 dark:text-gray-300">
                            <strong>{{ __('Dirección:') }}</strong> {{ $cliente_seleccionado['direccion'] ?? '---' }}
                        </p>

                        @if($esGeneradoPorReserva)
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 italic">
                                * El cliente no puede modificarse porque esta orden proviene de una reserva.
                            </p>
                        @endif
                    </div>
                </div>

                <flux:input
                    wire:model.defer="producto_buscar"
                    :label="__('Buscar producto')"
                    placeholder="{{ __('Escribe para buscar productos...') }}"
                    wire:keydown.enter="buscarProducto"
                >
                    <x-slot name="iconTrailing">
                        <flux:button
                            size="sm"
                            variant="subtle"
                            icon="magnifying-glass"
                            wire:click="buscarProducto"
                            title="{{ __('Buscar') }}"
                            class="-mr-1"
                        />
                    </x-slot>
                </flux:input>
            </div>

            @livewire('customers.register')

            @php
                $productosPagina = $this->productosDisponiblesFiltrados['items'];
                $totalFiltrados = $this->productosDisponiblesFiltrados['total'];
                $totalPaginas = ceil($totalFiltrados / $porPagina);
            @endphp

            @if(count($productosPagina))
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mt-4">
                    @foreach($productosPagina as $producto)
                        <flux:modal.trigger name="product-detail">
                            <div
                                class="cursor-pointer rounded-md overflow-hidden hover:shadow-md hover:bg-blue-50 dark:hover:bg-blue-900 transition duration-150 ease-in-out bg-white dark:bg-zinc-900"
                                x-data
                                x-on:click.prevent="$dispatch('open-modal-product-detail', { productoId: {{ $producto['id'] }} })"
                            >
                                <img
                                    src="{{ $producto['imagen']
                    ? asset('storage/' . $producto['imagen'])
                    : 'https://img.kwcdn.com/product/open/0d9d4e1aff5a4660a8cd4f2805bb66cc-goods.jpeg?imageView2/2/w/1300/q/90/format/webp' }}"
                                    alt="{{ $producto['nombre'] }}"
                                    class="w-full h-32 object-cover"
                                />

                                <div class="px-2 py-2 space-y-2">
                                    <p class="text-xs text-gray-600 dark:text-gray-300 truncate">{{ $producto['nombre'] }}</p>

                                    <div class="flex items-center justify-between space-x-2">
                                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                                            S/ {{ number_format($producto['precio'], 2) }}
                                        </p>

                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('Stock:') }} {{ $producto['stock'] ?? __('N/A') }}
                                        </p>

                                        <button
                                            class="p-1 rounded-full bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-500 dark:hover:bg-blue-600 cursor-pointer"
                                            title="{{ __('Añadir') }}"
                                            wire:click.stop="agregarProducto({{ $producto['id'] }})"
                                        >
                                            <flux:icon name="plus" class="w-5 h-5"/>
                                        </button>
                                    </div>

                                    <div class="flex space-x-1">
                                        @for($i = 0; $i < 5; $i++)
                                            <flux:icon name="star" class="w-4 h-4 text-yellow-400"/>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </flux:modal.trigger>
                    @endforeach


                </div>

                <!-- Paginación mejorada -->
                <div class="mt-6 flex flex-wrap justify-center items-center gap-1 text-sm">

                    <!-- Botón anterior -->
                    <button
                        wire:click="irAPagina({{ max(1, $pagina - 1) }})"
                        class="px-2 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-50"
                        @if($pagina === 1) disabled @endif
                    >
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    @php
                        $rango = 2; // cantidad de páginas a mostrar a los lados
                        $inicio = max(1, $pagina - $rango);
                        $fin = min($totalPaginas, $pagina + $rango);
                    @endphp

                    @if($inicio > 1)
                        <button wire:click="irAPagina(1)"
                                class="px-3 py-1 rounded border border-gray-300 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                            1
                        </button>
                        @if($inicio > 2)
                            <span class="px-2">...</span>
                        @endif
                    @endif

                    @for($i = $inicio; $i <= $fin; $i++)
                        <button wire:click="irAPagina({{ $i }})"
                                class="px-3 py-1 rounded {{ $i == $pagina ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            {{ $i }}
                        </button>
                    @endfor

                    @if($fin < $totalPaginas)
                        @if($fin < $totalPaginas - 1)
                            <span class="px-2">...</span>
                        @endif
                        <button wire:click="irAPagina({{ $totalPaginas }})"
                                class="px-3 py-1 rounded border border-gray-300 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                            {{ $totalPaginas }}
                        </button>
                    @endif

                    <!-- Botón siguiente -->
                    <button
                        wire:click="irAPagina({{ min($totalPaginas, $pagina + 1) }})"
                        class="px-2 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-50"
                        @if($pagina === $totalPaginas) disabled @endif
                    >
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No se encontraron productos.') }}</p>
            @endif
        </div>

        <input
            type="text"
            id="codigoEscaneoVenta"
            wire:model.live="codigoEscaneadoVenta"
            wire:keydown.enter="agregarProductoPorCodigo"
            class="absolute opacity-0 pointer-events-none"
            autocomplete="off"
        />

        <script>
            (() => {
                let buffer = '';
                let lastTime = Date.now();

                document.addEventListener('keydown', (e) => {
                    const currentTime = Date.now();
                    const diff = currentTime - lastTime;

                    if (diff > 100) {
                        buffer = '';
                    }

                    lastTime = currentTime;

                    if (e.key !== 'Enter') {
                        buffer += e.key;
                        return;
                    }

                    if (buffer.length >= 4) {
                        const input = document.getElementById('codigoEscaneoVenta');
                        input.value = buffer;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
                        buffer = '';
                    }
                });
            })();
        </script>


        @livewire('sales.product-detail')
        <!-- Sidebar derecho fijo -->
        <aside class="w-64 bg-white dark:bg-zinc-900 rounded-lg shadow-lg flex flex-col h-screen fixed right-0 top-0 z-10">
            <div class="p-6 flex flex-col flex-grow overflow-hidden">
                <!-- Subtotal y botón fijos -->
                <div class="mb-6 shrink-0">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Resumen de la venta') }}</h3>

                    <p class="text-xl font-bold mb-4">
                        {{ __('Subtotal:') }} S/ {{ number_format($total, 2) }}
                    </p>

                    <flux:button
                        wire:click="registrarVenta"
                        class="w-full cursor-pointer"
                        variant="primary"
                    >
                        {{ __('Continuar venta') }}
                    </flux:button>
                </div>

                @error('productos')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                @error('stock')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror


                <!-- Productos agregados con scroll oculto -->
                <div
                    class="flex-grow border-t border-gray-200 dark:border-gray-700 pt-4 space-y-4 overflow-auto"
                    style="scrollbar-width: none; -ms-overflow-style: none;"
                >
                    <style>
                        aside div::-webkit-scrollbar {
                            display: none;
                        }
                    </style>

                    <h4 class="font-semibold mb-2">{{ __('Productos agregados') }}</h4>

                    @forelse($productos as $index => $producto)
                        @php
                            $imagenUrl = $producto['imagen']
                                ? asset('storage/' . $producto['imagen'])
                                : 'https://img.kwcdn.com/product/Fancyalgo/VirtualModelMatting/c4c714885c2839352082b265af3d3352.jpg?imageView2/2/w/1300/q/90/format/webp';

                            $tooltip = "{$producto['nombre']}";
                        @endphp

                        <div
                            class="relative border rounded-lg p-3 text-center transition duration-150 ease-in-out
        {{ $producto['stock'] == 0 ? 'border-red-400 bg-red-50 dark:bg-red-100/10' : 'border-gray-200 dark:border-gray-700' }}"
                            title="{{ $tooltip }}"
                        >

                        {{-- Botón eliminar solo si stock es 0 --}}
                            @if ($producto['stock'] == 0)
                                <button
                                    wire:click="eliminarProducto({{ $index }})"
                                    class=" cursor-pointer absolute top-2 right-2 w-8 h-8 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-full shadow-md transition duration-150 ease-in-out"
                                    title="Eliminar producto"
                                >
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                            @endif

                            <img
                                src="{{ $imagenUrl }}"
                                alt="{{ $producto['nombre'] }}"
                                class="mx-auto w-20 h-20 object-cover rounded"
                            />

                            <p class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                S/ {{ number_format($producto['precio'], 2) }}
                            </p>

                            <div class="mt-2">
                                <flux:select
                                    label="{{ __('Cantidad') }}"
                                    wire:model.defer="productos.{{ $index }}.cantidad"
                                    wire:change="actualizarCantidad({{ $index }}, $event.target.value)"
                                >
                                    @for ($i = 0; $i <= $producto['stock']; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </flux:select>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-gray-400">{{ __('No hay productos agregados') }}</p>
                    @endforelse

                </div>
            </div>
        </aside>
    </div>
</section>

<script>
    if (!window._errorRegisterSale) {
        window._errorRegisterSale = true;

        window.addEventListener('errorRegisterSale', (event) => {
            toastr.error(event.detail[0].mensaje);
        });
    }
</script>
