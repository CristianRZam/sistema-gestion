<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Platillo') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los platillos del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>


    @livewire('dishes.filter')


    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">

            @can('descargar catalogo productos')
                <a href="{{ route('dishes.carta-menu.pdf', [
                    'nombre' => $nombreFiltro,
                    'categoria_ids' => $categoriaFiltro,
                    'estado' => $estadoFiltro,
                    'dish_ids' => implode(',', $selectedDishes),
                ]) }}"
                   x-init="tippy($el, { content: 'Generar Carta Menu PDF' })"
                   class="rounded-full border border-amber-500 text-amber-500 px-4 py-2 hover:bg-amber-500 hover:text-white transition duration-200 cursor-pointer inline-flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    <span>Carta</span>
                </a>
            @endcan

            @can('exportar productos')
                <!-- Exportar PDF -->
                <a href="{{ route('dishes.exportar.pdf', [
                        'nombre' => $nombreFiltro,
                        'categoria_ids' => $categoriaFiltro,
                        'estado' => $estadoFiltro,
                        'dish_ids' => implode(',', $selectedDishes),
                    ]) }}"
                   class="btn-exportar-pdf"
                   x-data
                   x-init="tippy($el, { content: 'Exportar PDF' })">
                    <i class="fas fa-file-pdf"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>

                <!-- Exportar Excel -->
                <a href="{{ route('dishes.exportar.excel', [
                        'nombre' => $nombreFiltro,
                        'categoria_ids' => $categoriaFiltro,
                        'estado' => $estadoFiltro,
                        'dish_ids' => implode(',', $selectedDishes),
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
                <flux:modal.trigger name="register-dish">
                    <button
                        class="btn-nuevo"
                        x-data
                        x-init="tippy($el, { content: 'Nuevo Registro' })"
                        x-on:click.prevent="$dispatch('open-modal-dish')">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('Nuevo') }}</span>
                    </button>
                </flux:modal.trigger>
            @endcan
        </div>
    </div>

    <!-- Tabla de productos responsiva -->
    @if ($platillos->isEmpty())
        <p>No hay platillos registrados.</p>
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
                    <th class="border p-2 text-center">estado</th>
                    <th class="border p-2 text-center">Precio</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($platillos as $index => $platillo)
                    <tr wire:key="platillo-{{ $platillo->id }}" class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                        <td class="border p-2 text-center">
                            <input type="checkbox"
                                   class="cursor-pointer"
                                   wire:model.live="selectedDishes"
                                   value="{{ $platillo->id }}" />
                        </td>
                        <td class="border p-2 text-center">{{ $loop->iteration + ($platillos->currentPage() - 1) * $platillos->perPage() }}</td>
                        <td class="border p-2">{{ $platillo->codigo }}</td>
                        <td class="border p-2">{{ $platillo->nombre }}</td>
                        <td class="border p-2 text-center">
                            @if ($platillo->activo)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('Disponible') }}
                                </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('No disponible') }}
                                </span>
                            @endif
                        </td>

                        <td class="border p-2 text-center">{{ $platillo->precio }}</td>
                        <td class="border p-2 text-center whitespace-nowrap">
                            <flux:modal.trigger name="register-dish">
                                <button
                                    class="btn-editar-table"
                                    x-data
                                    x-init="tippy($el, { content: 'Editar Registro' })"
                                    x-on:click.prevent="$dispatch('open-modal-dish', { id: {{ $platillo->id }} })"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </flux:modal.trigger>

                            <flux:modal.trigger name="confirm-dish-deletion">
                                <button
                                    class="btn-delete-table"
                                    x-data
                                    x-init="tippy($el, { content: 'Eliminar Registro' })"
                                    x-on:click.prevent="$dispatch('open-modal-delete-dish', { id: {{ $platillo->id }} })"
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
            {{ $platillos->links() }}
        </div>
    @endif


    <flux:modal name="confirm-dish-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="delete" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que quieres eliminar el platillo?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Una vez eliminado el platillo, esta acción no se puede deshacer.') }}
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

    @livewire('dishes.register')

</section>
<script>
    window.addEventListener('cerrarModalDeteleDish', () => {
        Flux.modal('confirm-dish-deletion').close();
    });
</script>
