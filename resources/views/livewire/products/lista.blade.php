<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Producto') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los productos del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>


    <!-- Bototones alineados a la derecha -->
    <div class="mb-4 text-right">
        @can('exportar productos')
            <a href="{{ route('products.exportar.pdf') }}"
               class="border border-red-600 text-red-600 px-4 py-2 rounded hover:bg-red-600 hover:text-white mr-2">
                Exportar PDF
            </a>
            <a href="{{ route('products.exportar.excel') }}"
               class="border border-green-600 text-green-600 px-4 py-2 rounded hover:bg-green-600 hover:text-white mr-2">
                Exportar Excel
            </a>
        @endcan

        <!-- Botón Agregar -->
        @can('crear producto')
            <flux:modal.trigger name="register-product">
                <button
                    class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-500 hover:text-white cursor-pointer"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal-product')">
                    {{ __('Agregar') }}
                </button>
            </flux:modal.trigger>
        @endcan
    </div>


    <!-- Tabla de categorias -->
    @if ($productos->isEmpty())
        <p>No hay productos registrados.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">Nº</th>
                <th class="border p-2">Nombre</th>
                <th class="border p-2">Descripción</th>
                <th class="border p-2">Stock</th>
                <th class="border p-2">Precio</th>
                <th class="border p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($productos as $index => $producto)
                <tr>
                    <td class="border p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border p-2">{{ $producto->nombre }}</td>
                    <td class="border p-2">{{ $producto->descripcion }}</td>
                    <td class="border p-2 text-center">{{ $producto->stock }}</td>
                    <td class="border p-2 text-center">{{ $producto->precio }}</td>
                    <td class="border p-2 text-center">
                        @can('editar producto')
                            <flux:modal.trigger name="register-product">
                                <button
                                    class="border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-white mr-2 cursor-pointer"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal-product', { id: {{ $producto->id }} })"
                                >
                                    {{ __('Editar') }}
                                </button>
                            </flux:modal.trigger>
                        @endcan
                        @can('eliminar producto')
                            <flux:modal.trigger name="confirm-product-deletion">
                                <button
                                    class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-500 hover:text-white cursor-pointer"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal-delete-product', { id: {{ $producto->id }} })"
                                >
                                    {{ __('Eliminar') }}
                                </button>
                            </flux:modal.trigger>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
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
