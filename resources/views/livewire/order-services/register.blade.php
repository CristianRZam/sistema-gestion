<section>
    <div class="flex justify-between h-screen overflow-hidden pl-4 pr-[15rem] pt-0">
        <!-- Contenido central -->
        <div class="flex-1 max-w-full pr-8 flex flex-col">
            <div class="space-y-8">
                <!-- Detalles fijos (Fecha, Cliente, etc.) -->
                <div>
                    <flux:heading size="lg">
                        {{ __('Registrar nueva orden') }}
                    </flux:heading>
                    <flux:subheading>
                        {{ __('Complete los campos para registrar una nueva orden.') }}
                    </flux:subheading>
                </div>

                <!-- Información del cliente -->
                <div class="relative flex border border-gray-300 dark:border-gray-700 border-l-0 rounded-lg bg-white dark:bg-zinc-900 overflow-hidden">
                    <!-- Botón de editar con ícono -->
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
                    </div>
                </div>

                <flux:input
                    wire:model.defer="servicio_buscar"
                    :label="__('Buscar servicio')"
                    placeholder="{{ __('Escribe para buscar servicios...') }}"
                    wire:keydown.enter="buscarServicio"
                >
                    <x-slot name="iconTrailing">
                        <flux:button
                            size="sm"
                            variant="subtle"
                            icon="magnifying-glass"
                            wire:click="buscarServicio"
                            title="{{ __('Buscar') }}"
                            class="-mr-1"
                        />
                    </x-slot>
                </flux:input>
            </div>

            @livewire('customers.register')

            @php
                $serviciosPagina = $this->serviciosDisponiblesFiltrados['items'];
                $totalFiltrados = $this->serviciosDisponiblesFiltrados['total'];
                $totalPaginas = ceil($totalFiltrados / $porPagina);
            @endphp

            @if(count($serviciosPagina))
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mt-4">
                    @foreach($serviciosPagina as $servicio)
                        <div
                            class="cursor-pointer rounded-md overflow-hidden hover:shadow-md hover:bg-blue-50 dark:hover:bg-blue-900 transition duration-150 ease-in-out bg-white dark:bg-zinc-900"
                            x-data
                        >
                            <img
                                src="{{ $servicio['imagen']
                ? asset('storage/' . $servicio['imagen'])
                : 'https://img.kwcdn.com/product/open/0d9d4e1aff5a4660a8cd4f2805bb66cc-goods.jpeg?imageView2/2/w/1300/q/90/format/webp' }}"
                                alt="{{ $servicio['nombre'] }}"
                                class="w-full h-32 object-cover"
                            />

                            <div class="px-2 py-2 space-y-2">
                                <p class="text-xs text-gray-600 dark:text-gray-300 truncate">{{ $servicio['nombre'] }}</p>

                                <div class="flex items-center justify-between space-x-2">
                                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                                        S/ {{ number_format($servicio['precio'], 2) }}
                                    </p>


                                    <button
                                        class="p-1 rounded-full bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-500 dark:hover:bg-blue-600 cursor-pointer"
                                        title="{{ __('Añadir') }}"
                                        wire:click.stop="agregar({{ $servicio['id'] }})"
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
                    @endforeach


                </div>

                <!-- Paginación dinámica Livewire -->
                <div class="mt-4 flex justify-center items-center space-x-2">
                    @for($i = 1; $i <= $totalPaginas; $i++)
                        <button wire:click="irAPagina({{ $i }})"
                                class="px-3 py-1 rounded cursor-pointer {{ $i == $pagina ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ $i }}
                        </button>
                    @endfor
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No se encontraron servicios.') }}</p>
            @endif
        </div>

        <!-- Sidebar derecho fijo -->
        <aside class="w-64 bg-white dark:bg-zinc-900 rounded-lg shadow-lg flex flex-col h-screen fixed right-0 top-0 z-10">
            <div class="p-6 flex flex-col flex-grow overflow-hidden">
                <!-- Subtotal y botón fijos -->
                <div class="mb-6 shrink-0">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Resumen de la orden') }}</h3>

                    <p class="text-xl font-bold mb-4">
                        {{ __('Subtotal:') }} S/ {{ number_format($total, 2) }}
                    </p>

                    <flux:button
                        wire:click="registrar"
                        class="w-full cursor-pointer"
                        variant="primary"
                    >
                        {{ __('Continuar') }}
                    </flux:button>
                </div>

                @error('servicios')
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

                    <h4 class="font-semibold mb-2">{{ __('Servicios agregados') }}</h4>

                    @forelse($servicios as $index => $servicio)
                        @php
                            $imagenUrl = $servicio['imagen']
                                ? asset('storage/' . $servicio['imagen'])
                                : 'https://img.kwcdn.com/product/Fancyalgo/VirtualModelMatting/c4c714885c2839352082b265af3d3352.jpg?imageView2/2/w/1300/q/90/format/webp';

                            $tooltip = "{$servicio['nombre']}";
                        @endphp

                        <div
                            class="relative border rounded-lg p-3 text-center transition duration-150 ease-in-out
                            'border-gray-200 dark:border-gray-700' }}"
                            title="{{ $tooltip }}"
                        >

                            {{-- Botón eliminar solo si stock es 0 --}}
                            <button
                                wire:click="eliminar({{ $index }})"
                                class=" cursor-pointer absolute top-2 right-2 w-8 h-8 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-full shadow-md transition duration-150 ease-in-out"
                                title="Eliminar servicio"
                            >
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>

                            <img
                                src="{{ $imagenUrl }}"
                                alt="{{ $servicio['nombre'] }}"
                                class="mx-auto w-20 h-20 object-cover rounded"
                            />

                            <p class="mt-2 text-lg font-semibold text-gray-800 dark:text-gray-200">
                                S/ {{ number_format($servicio['precio'], 2) }}
                            </p>

                            <div class="mt-2">
                                <flux:select
                                    label="{{ __('Cantidad') }}"
                                    wire:model.defer="servicios.{{ $index }}.cantidad"
                                    wire:change="actualizarCantidad({{ $index }}, $event.target.value)"
                                >
                                    @for ($i = 0; $i <= 100; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </flux:select>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-gray-400">{{ __('No hay servicios agregados') }}</p>
                    @endforelse

                </div>
            </div>
        </aside>
    </div>
</section>

<script>
    if (!window._errorRegisterSale) {
        window._errorRegisterSale = true;

        window.addEventListener('errorRegisterOrder', (event) => {
            toastr.error(event.detail[0].mensaje);
        });
    }
</script>
