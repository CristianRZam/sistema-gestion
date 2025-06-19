<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Producto') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los productos del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @livewire('products.filter')

    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">

            <!-- Escaneo continuo -->
            @if (Auth::user()->can('crear producto') && Auth::user()->can('editar producto'))
                <button
                    wire:click="toggleModoEscaneoContinuo"
                    x-data
                    x-init="tippy($el, { content: 'Escaneo Continuo' })"
                    class="px-4 py-2 rounded-full cursor-pointer
                    {{ $modoContinuo ? 'bg-green-600 text-white' : 'border border-gray-600 text-gray-600 hover:bg-gray-600 hover:text-white' }}">
                    {{ $modoContinuo ? 'ON' : 'OFF' }}
                </button>
            @endif



            <!-- Importar -->
            @can('importar productos')
                <flux:modal.trigger name="import-product">
                    <button
                        class="rounded-full border border-orange-500 text-orange-500 px-4 py-2 hover:bg-orange-500 hover:text-white transition duration-200 cursor-pointer inline-flex items-center gap-2"
                        x-data
                        x-init="tippy($el, { content: 'Importar Registros' })"
                        x-on:click.prevent="$dispatch('open-modal-product-import')">
                        <i class="fas fa-file-import"></i>
                        <span>{{ __('Importar') }}</span>
                    </button>
                </flux:modal.trigger>
            @endcan

            @can('exportar productos')
                <a href="{{ route('products.exportar-catalogo.pdf', [
                    'nombre' => $nombreFiltro,
                    'categoria_id' => $categoriaFiltro,
                    'stock' => $stockFiltro,
                ]) }}"
                   x-init="tippy($el, { content: 'Generar Catálogo PDF' })"
                   class="rounded-full border border-amber-500 text-amber-500 px-4 py-2 hover:bg-amber-500 hover:text-white transition duration-200 cursor-pointer inline-flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    <span>Catálogo</span>
                </a>

                <!-- Exportar PDF -->
                <a href="{{ route('products.exportar.pdf') }}"
                   class="border border-red-600 text-red-600 px-4 py-2 rounded-full hover:bg-red-600 hover:text-white inline-flex items-center gap-2 justify-center"
                   x-data
                   x-init="tippy($el, { content: 'Exportar PDF' })">
                    <i class="fas fa-file-pdf"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>

                <!-- Exportar Excel -->
                <a href="{{ route('products.exportar.excel') }}"
                   class="border border-green-600 text-green-600 px-4 py-2 rounded-full hover:bg-green-600 hover:text-white inline-flex items-center gap-2 justify-center"
                   x-data
                   x-init="tippy($el, { content: 'Exportar Excel' })">
                    <i class="fas fa-file-excel"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
            @endcan

            <!-- Botón Nuevo -->
            @can('crear producto')
                <flux:modal.trigger name="register-product">
                    <button
                        class="border border-blue-500 text-blue-500 px-4 py-2 rounded-full hover:bg-blue-500 hover:text-white cursor-pointer inline-flex items-center gap-2 justify-center w-full sm:w-auto"
                        x-data
                        x-init="tippy($el, { content: 'Nuevo Registro' })"
                        x-on:click.prevent="$dispatch('open-modal-product')">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('Nuevo') }}</span>
                    </button>
                </flux:modal.trigger>
            @endcan

        </div>
    </div>


    @livewire('products.import')

    <!-- Tabla de categorias -->
    @if ($productos->isEmpty())
        <p>No hay productos registrados.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">Nº</th>
                <th class="border p-2">Código</th>
                <th class="border p-2">Nombre</th>
                <th class="border p-2">Stock</th>
                <th class="border p-2">Precio</th>
                <th class="border p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($productos as $index => $producto)
                <tr wire:key="producto-{{ $producto->id }}">
                    <td class="border p-2 text-center">{{ $loop->iteration + ($productos->currentPage() - 1) * $productos->perPage() }}</td>
                    <td class="border p-2">{{ $producto->codigo }}</td>
                    <td class="border p-2">{{ $producto->nombre }}</td>
                    <td class="border p-2 text-center">{{ $producto->stock }}</td>
                    <td class="border p-2 text-center">{{ $producto->precio }}</td>
                    <td class="border p-2 text-center">
                        <flux:modal.trigger name="register-product">
                            <button
                                class="border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-white mr-2 cursor-pointer"
                                x-data
                                x-init="tippy($el, { content: 'Editar Registro' })"
                                x-on:click.prevent="$dispatch('open-modal-product', { id: {{ $producto->id }} })"
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </flux:modal.trigger>

                        <flux:modal.trigger name="confirm-product-deletion">
                            <button
                                class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-500 hover:text-white cursor-pointer"
                                x-data
                                x-init="tippy($el, { content: 'Eliminar Registro' })"
                                x-on:click.prevent="$dispatch('open-modal-delete-product', { id: {{ $producto->id }} })"
                            >
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </flux:modal.trigger>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $productos->links() }}
        </div>

    @endif

    <flux:modal name="confirm-product-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteProduct" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que quieres eliminar el producto?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Una vez eliminada la categoría, esta acción no se puede deshacer.') }}
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

    @livewire('products.register')

</section>
<script>
    window.addEventListener('cerrarModalDeteleProduct', () => {
        Flux.modal('confirm-product-deletion').close();
    });
</script>
