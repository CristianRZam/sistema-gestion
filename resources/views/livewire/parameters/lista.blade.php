<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Parámetro') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los parámetros del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>


    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            @can('exportar parametros')
                <a href="{{ route('parameters.exportar.pdf') }}" id="btnExportarPdf"
                   class="border border-red-600 text-red-600 px-4 py-2 rounded-full hover:bg-red-600 hover:text-white inline-flex items-center gap-2 justify-center">
                    <i class="fas fa-file-pdf"></i> <span>{{ __('Exportar') }}</span>
                </a>
                <a href="{{ route('parameters.exportar.excel') }}" id="btnExportarExcel"
                   class="border border-green-600 text-green-600 px-4 py-2 rounded-full hover:bg-green-600 hover:text-white inline-flex items-center gap-2 justify-center">
                    <i class="fas fa-file-excel"></i> <span>{{ __('Exportar') }}</span>
                </a>
            @endcan

            @can('crear parametro')
                <flux:modal.trigger name="register-parameter">
                    <button
                        id="btnNuevo"
                        class="border border-blue-500 text-blue-500 px-4 py-2 rounded-full hover:bg-blue-500 hover:text-white cursor-pointer inline-flex items-center gap-2 justify-center w-full sm:w-auto"
                        x-data=""
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
        <div class="overflow-x-auto rounded-lg shadow-sm">
            <table class="min-w-full table-auto border-collapse text-sm sm:text-base">
                <thead class="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                <tr>
                    <th class="border p-2 text-left dark:border-gray-700">Nº</th>
                    <th class="border p-2 text-left dark:border-gray-700">Nombre</th>
                    <th class="border p-2 text-left dark:border-gray-700">Nombre Corto</th>
                    <th class="border p-2 text-center dark:border-gray-700">Orden</th>
                    <th class="border p-2 text-center dark:border-gray-700">Código</th>
                    <th class="border p-2 text-center dark:border-gray-700">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($parametros as $index => $parametro)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="border p-2 text-center dark:border-gray-700">
                            {{ $loop->iteration + ($parametros->currentPage() - 1) * $parametros->perPage() }}
                        </td>
                        <td class="border p-2 dark:border-gray-700">{{ $parametro->nombre }}</td>
                        <td class="border p-2 dark:border-gray-700">{{ $parametro->nombreCorto }}</td>
                        <td class="border p-2 text-center dark:border-gray-700">{{ $parametro->orden }}</td>
                        <td class="border p-2 text-center dark:border-gray-700">{{ $parametro->codigoParametro }}</td>
                        <td class="border p-2 text-center dark:border-gray-700">
                            @can('editar parametro')
                                <flux:modal.trigger name="register-parameter">
                                    <button
                                        class="border border-yellow-500 text-yellow-500 px-3 py-1.5 rounded-full cursor-pointer hover:bg-yellow-500 hover:text-white dark:hover:text-black mr-2 inline-flex items-center gap-1"
                                        x-data=""
                                        id="btnEditar"
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

    document.addEventListener('livewire:navigated', function () {
        tippy('#btnExportarPdf', {
            content: 'Exportar PDF',
        });

        tippy('#btnExportarExcel', {
            content: 'Exportar Excel',
        });

        tippy('#btnNuevo', {
            content: 'Nuevo Registro',
        });

        tippy('#btnEditar', {
            content: 'Editar Registro',
        });
    });
</script>
