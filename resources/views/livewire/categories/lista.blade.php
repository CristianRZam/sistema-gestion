<section class="w-full">
    <!-- Cabecera de vista -->
    @include('partials.category-heading')

    <!-- Bototones alineados a la derecha -->
    <div class="mb-4 text-right">
        <a href="{{ route('categories.exportar.pdf') }}"
           class="border border-red-600 text-red-600 px-4 py-2 rounded hover:bg-red-600 hover:text-white mr-2">
            Exportar PDF
        </a>
        <a href="{{ route('categories.exportar.excel') }}"
           class="border border-green-600 text-green-600 px-4 py-2 rounded hover:bg-green-600 hover:text-white mr-2">
            Exportar Excel
        </a>


        <!-- Botón Agregar -->
        <flux:modal.trigger name="register-category">
            <button
                class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-500 hover:text-white cursor-pointer"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal')">
                {{ __('Agregar') }}
            </button>
        </flux:modal.trigger>

    </div>


    <!-- Tabla de categorias -->
    @if ($categorias->isEmpty())
        <p>No hay parámetros de tipo CATEGORIA.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">Nº</th>
                <th class="border p-2">Nombre</th>
                <th class="border p-2">Nombre Corto</th>
                <th class="border p-2">Orden</th>
                <th class="border p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categorias as $index => $categoria)
                <tr>
                    <td class="border p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border p-2">{{ $categoria->nombre }}</td>
                    <td class="border p-2">{{ $categoria->nombreCorto }}</td>
                    <td class="border p-2 text-center">{{ $categoria->orden }}</td>
                    <td class="border p-2 text-center">
                        <flux:modal.trigger name="register-category">
                            <button
                                class="border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-white mr-2 cursor-pointer"
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', { id: {{ $categoria->id }} })"
                            >
                                {{ __('Editar') }}
                            </button>
                        </flux:modal.trigger>
                        <flux:modal.trigger name="confirm-category-deletion">
                            <button
                                class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-500 hover:text-white cursor-pointer"
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal-delete-category', { id: {{ $categoria->id }} })"
                            >
                                {{ __('Eliminar') }}
                            </button>
                        </flux:modal.trigger>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <flux:modal name="confirm-category-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteCategory" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que quieres eliminar la categoría?') }}</flux:heading>

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

    @livewire('categories.register')

</section>
<script>
    window.addEventListener('cerrarModalDeteleCategory', () => {
        Flux.modal('confirm-category-deletion').close();
    });
</script>
