<section class="w-full">
    <!-- Cabecera de vista (puedes crear tu propio partial si deseas) -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Usuario') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los usuarios del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>


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
    </div>

    <!-- Tabla de usuarios -->
    @if ($usuarios->isEmpty())
        <p>No hay usuarios registrados.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">Nº</th>
                <th class="border p-2">Nombre</th>
                <th class="border p-2">Correo</th>
                <th class="border p-2">Rol</th>
                <th class="border p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($usuarios as $index => $usuario)
                <tr>
                    <td class="border p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border p-2">{{ $usuario->name }}</td>
                    <td class="border p-2">{{ $usuario->email }}</td>
                    <td class="border p-2 text-center">
                        {{ $usuario->getRoleNames()->first() ?? 'Sin rol' }}
                    </td>
                    <td class="border p-2 text-center">
                        <flux:modal.trigger name="edit-user">
                            <button
                                class="border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-white mr-2 cursor-pointer"
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', { id: {{ $usuario->id }} })"
                            >
                                {{ __('Editar') }}
                            </button>
                        </flux:modal.trigger>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <!-- Modal para editar usuario (puedes completarlo luego) -->
    <flux:modal name="edit-user" class="max-w-lg">
        <div class="p-4">
            <flux:heading size="lg">{{ __('Editar usuario') }}</flux:heading>
            <flux:subheading>
                {{ __('Esta funcionalidad estará disponible próximamente.') }}
            </flux:subheading>

            <div class="flex justify-end mt-4">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cerrar') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</section>
