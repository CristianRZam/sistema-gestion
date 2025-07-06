<section class="w-full">
    <!-- Cabecera -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Roles') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los roles del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            <a href="{{ route('roles.exportar.pdf') }}"
               class="btn-exportar-pdf"
               x-data
               x-init="tippy($el, { content: 'Exportar PDF' })">
                <i class="fas fa-file-pdf"></i>
                <span>{{ __('Exportar') }}</span>
            </a>
            <a href="{{ route('roles.exportar.excel') }}"
               class="btn-exportar-excel"
               x-data
               x-init="tippy($el, { content: 'Exportar Excel' })">
                <i class="fas fa-file-excel"></i>
                <span>{{ __('Exportar') }}</span>
            </a>

            <flux:modal.trigger name="register-role">
                <button
                    class="btn-nuevo"
                    x-data
                    x-init="tippy($el, { content: 'Nuevo Registro' })"
                    x-on:click.prevent="$dispatch('open-modal-role')">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('Nuevo') }}</span>
                </button>
            </flux:modal.trigger>
        </div>
    </div>

    <!-- Tabla de roles -->
    @if ($roles->isEmpty())
        <p>No hay roles registrados.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="border p-2 text-center">Nº</th>
                    <th class="border p-2 text-left">Nombre</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($roles as $index => $role)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                        <td class="border p-2 text-center">{{ $index + 1 }}</td>
                        <td class="border p-2 text-left">{{ $role->name }}</td>
                        <td class="border p-2 text-center whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                                {{-- Botón Editar --}}
                                <flux:modal.trigger name="register-role">
                                    <button
                                        class="btn-editar-table"
                                        x-data
                                        x-init="tippy($el, { content: 'Editar Registro' })"
                                        x-on:click.prevent="$dispatch('open-modal-role', { id: {{ $role->id }} })"
                                    >
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </flux:modal.trigger>

                                {{-- Botón Permisos --}}
                                <a
                                    href="{{ route('permissions', $role->id) }}"
                                    class="btn-nuevo-table"
                                    x-data
                                    x-init="tippy($el, { content: 'Editar Permisos' })"
                                >
                                    <i class="fa-solid fa-user-shield"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $roles->links() }}
        </div>
    @endif

    @livewire('roles.register')
</section>
