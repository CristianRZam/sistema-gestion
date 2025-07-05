<section class="w-full">
    <!-- Cabecera de vista (puedes crear tu propio partial si deseas) -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Usuario') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los usuarios del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>


    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            <a href="{{ route('users.exportar.pdf') }}"
               class="btn-exportar-pdf"
               x-data
               x-init="tippy($el, { content: 'Exportar PDF' })">
                <i class="fas fa-file-pdf"></i>
                <span>{{ __('Exportar') }}</span>
            </a>
            <a href="{{ route('users.exportar.excel') }}"
               class="btn-exportar-excel"
               x-data
               x-init="tippy($el, { content: 'Exportar Excel' })">
                <i class="fas fa-file-excel"></i>
                <span>{{ __('Exportar') }}</span>
            </a>

            <flux:modal.trigger name="register-user">
                <button
                    class="btn-nuevo"
                    x-data
                    x-init="tippy($el, { content: 'Nuevo Registro' })"
                    x-on:click.prevent="$dispatch('open-modal-user')">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('Nuevo') }}</span>
                </button>
            </flux:modal.trigger>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    @if ($usuarios->isEmpty())
        <p>No hay usuarios registrados.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="border p-2 text-center">Nº</th>
                    <th class="border p-2 text-left">Nombre</th>
                    <th class="border p-2 text-left">Correo</th>
                    <th class="border p-2 text-center">Rol</th>
                    <th class="border p-2 text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($usuarios as $index => $usuario)
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                        <td class="border p-2 text-center">
                            {{ $index + 1 }}
                        </td>
                        <td class="border p-2">
                            {{ $usuario->name }}
                        </td>
                        <td class="border p-2">
                            {{ $usuario->email }}
                        </td>
                        <td class="border p-2 text-center">
                            {{ $usuario->getRoleNames()->first() ?? 'Sin rol' }}
                        </td>
                        <td class="border p-2 text-center whitespace-nowrap">
                            <flux:modal.trigger name="register-user">
                                <button
                                    class="btn-editar-table"
                                    x-data
                                    x-init="tippy($el, { content: 'Editar Registro' })"
                                    x-on:click.prevent="$dispatch('open-modal-user', { id: {{ $usuario->id }} })"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </flux:modal.trigger>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>

    @endif


    @livewire('users.register')

</section>
