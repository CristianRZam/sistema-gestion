<section class="w-full">
    <!-- Cabecera de vista (puedes crear tu propio partial si deseas) -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Habitación') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona las habitaciones del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>


    @livewire('rooms.filter')
    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            <a href="{{ route('rooms.exportar.pdf', [
                    'tipo_ids' => $tipoFiltro,
                    'piso_ids' => $pisoFiltro,
                    'estado_ids' => $estadoFiltro,
                ]) }}"
               class="btn-exportar-pdf"
               x-data
               x-init="tippy($el, { content: 'Exportar PDF' })">
                <i class="fas fa-file-pdf"></i>
                <span>{{ __('Exportar') }}</span>
            </a>
            <a href="{{ route('rooms.exportar.excel', [
                    'tipo_ids' => $tipoFiltro,
                    'piso_ids' => $pisoFiltro,
                    'estado_ids' => $estadoFiltro,
                ]) }}"
               class="btn-exportar-excel"
               x-data
               x-init="tippy($el, { content: 'Exportar Excel' })">
                <i class="fas fa-file-excel"></i>
                <span>{{ __('Exportar') }}</span>
            </a>

            <flux:modal.trigger name="register-room">
                <button
                    class="btn-nuevo"
                    x-data
                    x-init="tippy($el, { content: 'Nuevo Registro' })"
                    x-on:click.prevent="$dispatch('open-modal-room')">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('Nuevo') }}</span>
                </button>
            </flux:modal.trigger>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    @if ($habitaciones->isEmpty())
        <p>No hay habitaciones registradas.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="border p-2 text-center">Nº</th>
                    <th class="border p-2 text-left">Nº Habitación</th>
                    <th class="border p-2 text-left">Tipo</th>
                    <th class="border p-2 text-center">Piso</th>
                    <th class="border p-2 text-center">Precio</th>
                    <th class="border p-2 text-center">Estado</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($habitaciones as $index => $habitacion)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                        <td class="border p-2 text-center">
                            {{ $loop->iteration + ($habitaciones->currentPage() - 1) * $habitaciones->perPage() }}
                        </td>
                        <td class="border p-2">
                            {{ $habitacion->numero }}
                        </td>
                        <td class="border p-2">
                            {{ $habitacion->tipo?->nombre ?? '-' }}
                        </td>
                        <td class="border p-2 text-center">
                            {{ $habitacion->piso?->nombreCorto ?? '-' }}
                        </td>
                        <td class="border p-2 text-center">
                            {{ $habitacion->precio }}
                        </td>
                        <td class="border p-2 text-center">
                            @php
                                $estado = $habitacion->estado?->nombre;

                                $clase = match ($estado) {
                                    'Disponible' => 'bg-green-600 text-white',
                                    'Ocupada' => 'bg-red-600 text-white',
                                    'Reservada' => 'bg-yellow-500 text-white',
                                    'Limpieza' => 'bg-blue-600 text-white',
                                    'Mantenimiento' => 'bg-purple-600 text-white',
                                    'Inhabilitada' => 'bg-gray-600 text-white',
                                    'Check-out pendiente' => 'bg-orange-600 text-white',
                                    default => 'bg-gray-500 text-white',
                                };
                            @endphp

                            @if ($estado)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $clase }}">
                                    {{ $estado }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>


                        <td class="border p-2 text-center whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                                {{-- Botón Editar --}}
                                <flux:modal.trigger name="register-room">
                                    <button
                                        class="btn-editar-table"
                                        x-data
                                        x-init="tippy($el, { content: 'Editar Registro' })"
                                        x-on:click.prevent="$dispatch('open-modal-room', { id: {{ $habitacion->id }} })"
                                    >
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </flux:modal.trigger>

                                {{-- Botón Habilitar/Deshabilitar --}}
                                <flux:modal.trigger name="confirm-room-disable">
                                    <button
                                        x-data
                                        x-init="tippy($el, { content: '{{ $habitacion->estado_id != 3 ? 'Habilitado' : 'Deshabilitado' }}' })"
                                        x-on:click.prevent="$dispatch('open-modal-room-disable', { id: {{ $habitacion->id }} })"
                                        class="cursor-pointer {{ $habitacion->estado_id != 3
            ? 'border border-green-500 text-green-500 hover:bg-green-500 hover:text-white'
            : 'border border-red-500 text-red-500 hover:bg-red-500 hover:text-white' }} px-3 py-1.5 rounded transition"
                                    >
                                        {{ $habitacion->estado_id != 3 ? 'Habilitado' : 'Deshabilitado' }}
                                    </button>
                                </flux:modal.trigger>

                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $habitaciones->links() }}
        </div>

    @endif

    @livewire('rooms.register')

    <flux:modal name="confirm-room-disable" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="confirmarCambioEstado" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $estadoHabitacionModal != 3 ? '¿Estás seguro de que quieres deshabilitar la habitación?' : '¿Estás seguro de que quieres habilitar la habitación?' }}
                </flux:heading>

                <flux:subheading>
                    {{ $estadoHabitacionModal != 3
                        ? 'Una vez deshabilitado, la habitación no estará disponible.'
                        : 'Al habilitar la habitación, podrá ser reservada nuevamente.' }}
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
    if (!window._cerrarModalRoomDisable) {
        window._cerrarModalRoomDisable = true;

        window.addEventListener('cerrarModalRoomDisable', () => {
            Flux.modal('confirm-room-disable').close();
            toastr.success('Operación exitosa.');
        });
    }

</script>
