<section class="w-full">
    <!-- Cabecera de vista (puedes crear tu propio partial si deseas) -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Servicio') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los servicios del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            @can('exportar servicios')
                <a href="{{ route('services.exportar.pdf') }}"
                   class="btn-exportar-pdf"
                   x-data
                   x-init="tippy($el, { content: 'Exportar PDF' })">
                    <i class="fas fa-file-pdf"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
                <a href="{{ route('services.exportar.excel') }}"
                   class="btn-exportar-excel"
                   x-data
                   x-init="tippy($el, { content: 'Exportar Excel' })">
                    <i class="fas fa-file-excel"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
            @endcan

            <!-- Botón Nuevo -->
            @can('crear servicio')
                <flux:modal.trigger name="register-service">
                    <button
                        class="btn-nuevo"
                        x-data
                        x-init="tippy($el, { content: 'Nuevo Registro' })"
                        x-on:click.prevent="$dispatch('open-modal-service')">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('Nuevo') }}</span>
                    </button>
                </flux:modal.trigger>
            @endcan
        </div>
    </div>

    <!-- Tabla de usuarios -->
    @if ($servicios->isEmpty())
        <p>No hay servicios registrados.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="border p-2 text-center">Nº</th>
                    <th class="border p-2 text-left">Nombre</th>
                    <th class="border p-2 text-center">Precio</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($servicios as $index => $servicio)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                        <td class="border p-2 text-center">
                            {{ $loop->iteration + ($servicios->currentPage() - 1) * $servicios->perPage() }}
                        </td>
                        <td class="border p-2">
                            {{ $servicio->nombre }}
                        </td>
                        <td class="border p-2 text-center">
                            {{ $servicio->precio }}
                        </td>
                        <td class="border p-2 text-center whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                                @can('editar servicio')
                                    {{-- Botón Editar --}}
                                    <flux:modal.trigger name="register-service">
                                        <button
                                            class="btn-editar-table"
                                            x-data
                                            x-init="tippy($el, { content: 'Editar Registro' })"
                                            x-on:click.prevent="$dispatch('open-modal-service', { id: {{ $servicio->id }} })"
                                        >
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    </flux:modal.trigger>

                                    {{-- Botón Habilitar/Deshabilitar --}}
                                    <flux:modal.trigger name="confirm-service-disable">
                                        <button
                                            x-data
                                            x-init="tippy($el, { content: '{{ $servicio->activo ? 'Habilitado' : 'Deshabilitado' }}' })"
                                            x-on:click.prevent="$dispatch('open-modal-service-disable', { id: {{ $servicio->id }} })"
                                            class="cursor-pointer {{ $servicio->activo
                ? 'border border-green-500 text-green-500 hover:bg-green-500 hover:text-white'
                : 'border border-red-500 text-red-500 hover:bg-red-500 hover:text-white' }} px-3 py-1.5 rounded transition"
                                        >
                                            {{ $servicio->activo ? 'Habilitado' : 'Deshabilitado' }}
                                        </button>
                                    </flux:modal.trigger>
                                @endcan

                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $servicios->links() }}
        </div>

    @endif

    @livewire('services.register')

    <flux:modal name="confirm-service-disable" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="confirmarCambioEstado" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $estadoServicioModal ? '¿Estás seguro de que quieres deshabilitar el servicio?' : '¿Estás seguro de que quieres habilitar el servicio?' }}
                </flux:heading>

                <flux:subheading>
                    {{ $estadoServicioModal
                        ? 'Una vez deshabilitado, el servicio no estará disponible.'
                        : 'Al habilitar el servicio, estará disponible nuevamente.' }}
                </flux:subheading>
            </div>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button class="cursor-pointer" variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button class="cursor-pointer" variant="danger" type="submit">{{ __('Continuar') }}</flux:button>
            </div>
        </form>
    </flux:modal>

</section>


<script>
    if (!window._cerrarModalServiceDisable) {
        window._cerrarModalServiceDisable = true;

        window.addEventListener('cerrarModalServicioDisable', () => {
            Flux.modal('confirm-service-disable').close();
            toastr.success('Operación exitosa.');
        });
    }

</script>
