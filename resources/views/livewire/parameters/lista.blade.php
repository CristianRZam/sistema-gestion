<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Parámetro') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los parámetros del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @livewire('parameters.filter')

    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            @can('exportar parametros')
                <a href="{{ route('parameters.exportar.pdf', [
                    'nombre' => $nombreFiltro,
                    'tipo_ids' => $tipoFiltro,
                    'codigo' => $codigoFiltro,
                ]) }}"
                   class="btn-exportar-pdf"
                   x-data
                   x-init="tippy($el, { content: 'Exportar PDF' })">
                    <i class="fas fa-file-pdf"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
                <a href="{{ route('parameters.exportar.excel', [
                    'nombre' => $nombreFiltro,
                    'tipo_ids' => $tipoFiltro,
                    'codigo' => $codigoFiltro,
                ]) }}" id="btnExportarExcel"
                   class="btn-exportar-excel"
                   x-data
                   x-init="tippy($el, { content: 'Exportar Excel' })">
                    <i class="fas fa-file-excel"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
            @endcan

            @can('crear parametro')
                <flux:modal.trigger name="register-parameter">
                    <button
                        class="btn-nuevo"
                        x-data
                        x-init="tippy($el, { content: 'Nuevo Registro' })"
                        x-on:click.prevent="$dispatch('open-modal')">
                        <i class="fas fa-plus"></i> <span>{{ __('Nuevo') }}</span>
                    </button>
                </flux:modal.trigger>
            @endcan
        </div>
    </div>



    @if ($parametros->isEmpty())
        <p class="text-center text-gray-600 dark:text-gray-300">No hay parámetros registrados.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead>
                <tr>
                    <th class="border p-2 ">Nº</th>
                    <th class="border p-2 ">Nombre</th>
                    <th class="border p-2 ">Nombre Corto</th>
                    <th class="border p-2 ">Orden</th>
                    <th class="border p-2 ">Código</th>
                    <th class="border p-2 ">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($parametros as $index => $parametro)
                    <tr>
                        <td class="border p-2 text-center ">
                            {{ $loop->iteration + ($parametros->currentPage() - 1) * $parametros->perPage() }}
                        </td>
                        <td class="border p-2 ">{{ $parametro->nombre }}</td>
                        <td class="border p-2 ">{{ $parametro->nombreCorto }}</td>
                        <td class="border p-2 text-center ">{{ $parametro->orden }}</td>
                        <td class="border p-2 text-center ">{{ $parametro->codigoParametro }}</td>
                        <td class="border p-2 text-center ">
                            @can('editar parametro')
                                <flux:modal.trigger name="register-parameter">
                                    <button
                                        class="btn-editar-table"
                                        x-data
                                        x-init="tippy($el, { content: 'Editar Registro' })"
                                        x-on:click.prevent="$dispatch('open-modal', { id: {{ $parametro->id }} })"
                                    >
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </flux:modal.trigger>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Paginación -->
    <div class="mt-4 text-gray-700 dark:text-gray-300">
        {{ $parametros->links() }}
    </div>


    <flux:modal name="confirm-parameter-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteParameter" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que quieres eliminar el parámetro?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Una vez eliminado el parametro, esta acción no se puede deshacer.') }}
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

    @livewire('parameters.register')

</section>
<script>
    window.addEventListener('cerrarModalDeteleParameter', () => {
        Flux.modal('confirm-parameter-deletion').close();
    });
</script>
