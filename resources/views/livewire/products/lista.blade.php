<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Producto') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los productos del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <input
        type="text"
        wire:model.live="codigoEscaneado"
        wire:keydown.enter="procesarCodigoEscaneado"
        x-data
        x-ref="inputCodigo"
        x-on:keydown.enter="$nextTick(() => { $refs.inputCodigo.value = '' })"
        id="codigoEscaneo"
        class="absolute opacity-0 pointer-events-none"
        autocomplete="off"
    />
    <input
        type="text"
        id="codigoEscaneo"
        wire:model.live="codigoEscaneado"
        wire:keydown.enter="procesarCodigoEscaneado"
        class="absolute opacity-0 pointer-events-none"
        autocomplete="off"
    />

    <script>
        (() => {
            let buffer = '';
            let lastTime = Date.now();
            let timeout;

            document.addEventListener('keydown', (e) => {
                const currentTime = Date.now();
                const diff = currentTime - lastTime;

                if (diff > 100) {
                    buffer = ''; // Tecla muy lenta, asumimos que es humano
                }

                lastTime = currentTime;

                if (e.key !== 'Enter') {
                    buffer += e.key;
                    return;
                }

                // Si llega aquí, es porque se presionó Enter
                if (buffer.length >= 5) {
                    // Asumimos que es escáner (rápido y con longitud mínima)
                    const input = document.getElementById('codigoEscaneo');
                    input.value = buffer;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));

                    buffer = '';
                }
            });
        })();
    </script>


    @livewire('products.filter')


    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">

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
                <a href="{{ route('products.descargar.imagenes', [
                        'nombre' => $nombreFiltro,
                        'categoria_id' => $categoriaFiltro,
                        'stock' => $stockFiltro,
                    ]) }}"
                   x-data
                   x-init="tippy($el, { content: 'Descargar Imágenes' })"
                   class="rounded-full border border-purple-600 text-purple-600 px-4 py-2 hover:bg-purple-600 hover:text-white transition duration-200 cursor-pointer inline-flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>{{ __('Imágenes') }}</span>
                </a>
            @endcan

            @can('exportar productos')
                <a href="{{ route('products.exportar-catalogo.pdf', [
                    'nombre' => $nombreFiltro,
                    'categoria_ids' => $categoriaFiltro,
                    'stock' => $stockFiltro,
                    'product_ids' => implode(',', $selectedProducts),
                ]) }}"
                   x-init="tippy($el, { content: 'Generar Catálogo PDF' })"
                   class="rounded-full border border-amber-500 text-amber-500 px-4 py-2 hover:bg-amber-500 hover:text-white transition duration-200 cursor-pointer inline-flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    <span>Catálogo</span>
                </a>

                <!-- Exportar PDF -->
                <a href="{{ route('products.exportar.pdf', [
                        'nombre' => $nombreFiltro,
                        'categoria_ids' => $categoriaFiltro,
                        'stock' => $stockFiltro,
                        'product_ids' => implode(',', $selectedProducts),
                    ]) }}"
                   class="btn-exportar-pdf"
                   x-data
                   x-init="tippy($el, { content: 'Exportar PDF' })">
                    <i class="fas fa-file-pdf"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>

                <!-- Exportar Excel -->
                <a href="{{ route('products.exportar.excel', [
                        'nombre' => $nombreFiltro,
                        'categoria_ids' => $categoriaFiltro,
                        'stock' => $stockFiltro,
                        'product_ids' => implode(',', $selectedProducts),
                    ]) }}"
                   class="btn-exportar-excel"
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
                        class="btn-nuevo"
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

    <!-- Tabla de productos responsiva -->
    @if ($productos->isEmpty())
        <p>No hay productos registrados.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="border p-2 text-center">
                        <input type="checkbox"
                               class="cursor-pointer"
                               wire:model.live="selectAll"
                               title="Seleccionar todos los productos filtrados" />
                    </th>
                    <th class="border p-2 text-center">Nº</th>
                    <th class="border p-2">Código</th>
                    <th class="border p-2">Nombre</th>
                    <th class="border p-2 text-center">Stock</th>
                    <th class="border p-2 text-center">Precio</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($productos as $index => $producto)
                    <tr wire:key="producto-{{ $producto->id }}" class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                        <td class="border p-2 text-center">
                            <input type="checkbox"
                                   class="cursor-pointer"
                                   wire:model.live="selectedProducts"
                                   value="{{ $producto->id }}" />
                        </td>
                        <td class="border p-2 text-center">{{ $loop->iteration + ($productos->currentPage() - 1) * $productos->perPage() }}</td>
                        <td class="border p-2">{{ $producto->codigo }}</td>
                        <td class="border p-2">{{ $producto->nombre }}</td>
                        <td class="border p-2 text-center">{{ $producto->stock }}</td>
                        <td class="border p-2 text-center">{{ $producto->precio }}</td>
                        <td class="border p-2 text-center whitespace-nowrap">
                            <flux:modal.trigger name="register-product">
                                <button
                                    class="btn-editar-table"
                                    x-data
                                    x-init="tippy($el, { content: 'Editar Registro' })"
                                    x-on:click.prevent="$dispatch('open-modal-product', { id: {{ $producto->id }} })"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </flux:modal.trigger>

                            <flux:modal.trigger name="confirm-product-deletion">
                                <button
                                    class="border border-red-500 text-red-500 px-3 py-1.5 rounded hover:bg-red-500 hover:text-white cursor-pointer"
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
        </div>

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
