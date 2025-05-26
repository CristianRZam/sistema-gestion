<section class="w-full">
    <!-- Cabecera -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Roles') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los roles del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <!-- Bototones alineados a la derecha -->
    <div class="mb-4 text-right">
        <a href="{{ route('roles.exportar.pdf') }}"
           class="border border-red-600 text-red-600 px-4 py-2 rounded hover:bg-red-600 hover:text-white mr-2">
            Exportar PDF
        </a>
        <a href="{{ route('roles.exportar.excel') }}"
           class="border border-green-600 text-green-600 px-4 py-2 rounded hover:bg-green-600 hover:text-white mr-2">
            Exportar Excel
        </a>

        <flux:modal.trigger name="register-role">
            <button
                class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-500 hover:text-white cursor-pointer"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal-role')">
                {{ __('Agregar') }}
            </button>
        </flux:modal.trigger>
    </div>
    <!-- Tabla de roles -->
    @if ($roles->isEmpty())
        <p>No hay roles registrados.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">Nº</th>
                <th class="border p-2">Nombre</th>
                <th class="border p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($roles as $index => $role)
                <tr>
                    <td class="border p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border p-2">{{ $role->name }}</td>
                    <td class="border p-2 text-center">
                        <flux:modal.trigger name="register-role">
                            <button
                                class="border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-white mr-2 cursor-pointer"
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal-role', { id: {{ $role->id }} })"
                            >
                                {{ __('Editar') }}
                            </button>
                        </flux:modal.trigger>
                        <a
                            href="{{ route('permissions', $role->id) }}"
                            class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-500 hover:text-white"
                        >
                            Permisos
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @livewire('roles.register')
</section>
