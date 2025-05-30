<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Proveedor') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los proveedores del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>


    <!-- Botones alineados a la derecha -->
    <div class="mb-4 text-right">
        @can('exportar proveedores')
            <a href="{{ route('suppliers.exportar.pdf') }}"
               class="border border-red-600 text-red-600 px-4 py-2 rounded hover:bg-red-600 hover:text-white mr-2">
                Exportar PDF
            </a>
            <a href="{{ route('suppliers.exportar.excel') }}"
               class="border border-green-600 text-green-600 px-4 py-2 rounded hover:bg-green-600 hover:text-white mr-2">
                Exportar Excel
            </a>
        @endcan

        <!-- Botón Agregar -->
        @can('crear proveedor')
            <flux:modal.trigger name="register-supplier">
                <button
                    class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-500 hover:text-white cursor-pointer"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal-supplier')">
                    {{ __('Agregar') }}
                </button>
            </flux:modal.trigger>
        @endcan
    </div>

    <!-- Tabla de proveedores -->
    @if ($suppliers->isEmpty())
        <p>No hay proveedores registrados.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">Nº</th>
                <th class="border p-2">Nombre</th>
                <th class="border p-2">Documento</th>
                <th class="border p-2">Teléfono</th>
                <th class="border p-2">Email</th>
                <th class="border p-2">Dirección</th>
                <th class="border p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($suppliers as $index => $supplier)
                <tr>
                    <td class="border p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border p-2">{{ $supplier->nombre }}</td>
                    <td class="border p-2">{{ $supplier->documento }}</td>
                    <td class="border p-2">{{ $supplier->telefono ?? '-' }}</td>
                    <td class="border p-2">{{ $supplier->email ?? '-' }}</td>
                    <td class="border p-2">{{ $supplier->direccion ?? '-' }}</td>
                    <td class="border p-2 text-center">
                        @can('editar proveedor')
                            <flux:modal.trigger name="register-supplier">
                                <button
                                    class="border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-white mr-2 cursor-pointer"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal', { id: {{ $supplier->id }} })"
                                >
                                    {{ __('Editar') }}
                                </button>
                            </flux:modal.trigger>
                        @endcan
                        @can('eliminar proveedor')
                            <flux:modal.trigger name="confirm-supplier-deletion">
                                <button
                                    class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-500 hover:text-white cursor-pointer"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal-delete-supplier', { id: {{ $supplier->id }} })"
                                >
                                    {{ __('Eliminar') }}
                                </button>
                            </flux:modal.trigger>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <flux:modal name="confirm-supplier-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteSupplier" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que quieres eliminar al proveedor?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Una vez eliminado el proveedor, esta acción no se puede deshacer.') }}
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

    @livewire('suppliers.register')

</section>

<script>
    window.addEventListener('cerrarModalDeleteSupplier', () => {
        Flux.modal('confirm-supplier-deletion').close();
    });
</script>
