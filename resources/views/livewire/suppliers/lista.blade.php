<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Proveedor') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los proveedores del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @livewire('suppliers.filter')

    <!-- Botones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            @can('exportar proveedores')
                <a href="{{ route('suppliers.exportar.pdf', [
                    'nombre' => $nombreFiltro,
                    'numero_documento' => $numeroDocumentoFiltro,
                ]) }}"
                   class="btn-exportar-pdf"
                   x-data
                   x-init="tippy($el, { content: 'Exportar PDF' })">
                    <i class="fas fa-file-pdf"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
                <a href="{{ route('suppliers.exportar.excel', [
                    'nombre' => $nombreFiltro,
                    'numero_documento' => $numeroDocumentoFiltro,
                ]) }}"
                   class="btn-exportar-excel"
                   x-data
                   x-init="tippy($el, { content: 'Exportar Excel' })">
                    <i class="fas fa-file-excel"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
            @endcan

            <!-- Botón Agregar -->
            @can('crear proveedor')
                <flux:modal.trigger name="register-supplier">
                    <button
                        class="btn-nuevo"
                        x-data
                        x-init="tippy($el, { content: 'Nuevo Registro' })"
                        x-on:click.prevent="$dispatch('open-modal-supplier')">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('Nuevo') }}</span>
                    </button>
                </flux:modal.trigger>
            @endcan
        </div>
    </div>

    <!-- Tabla de proveedores -->
    @if ($suppliers->isEmpty())
        <p>No hay proveedores registrados.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
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
                                        class="btn-editar-table"
                                        x-data
                                        x-init="tippy($el, { content: 'Editar Registro' })"
                                        x-on:click.prevent="$dispatch('open-modal-supplier', { id: {{ $supplier->id }} })"
                                    >
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </flux:modal.trigger>
                            @endcan
                            @can('eliminar proveedor')
                                <flux:modal.trigger name="confirm-supplier-deletion">
                                    <button
                                        class="btn-delete-table"
                                        x-data
                                        x-init="tippy($el, { content: 'Eliminar Registro' })"
                                        x-on:click.prevent="$dispatch('open-modal-delete-supplier', { id: {{ $supplier->id }} })"
                                    >
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </flux:modal.trigger>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $suppliers->links() }}
        </div>
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
    if (!window._cerrarModalDeleteSupplier) {
        window._cerrarModalDeleteSupplier = true;

        window.addEventListener('cerrarModalDeleteSupplier', () => {
            Flux.modal('confirm-supplier-deletion').close();
            toastr.success('Operación exitosa.');
        });
    }
</script>
