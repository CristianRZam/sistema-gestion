<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Clientes') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los clientes del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @livewire('customers.filter')

    <!-- Bototones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            @can('exportar clientes')
                <a href="{{ route('customers.exportar.pdf', [
                    'nombre' => $nombreFiltro,
                    'numero_documento' => $numeroDocumentoFiltro,
                ]) }}"
                   class="btn-exportar-pdf"
                   x-data
                   x-init="tippy($el, { content: 'Exportar PDF' })">
                    <i class="fas fa-file-pdf"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
                <a href="{{ route('customers.exportar.excel', [
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
            @can('crear cliente')
                <flux:modal.trigger name="register-customer">
                    <button
                        class="btn-nuevo"
                        x-data
                        x-init="tippy($el, { content: 'Nuevo Registro' })"
                        x-on:click.prevent="$dispatch('open-modal-customer')">
                        <i class="fas fa-plus"></i>
                        <span>{{ __('Nuevo') }}</span>
                    </button>
                </flux:modal.trigger>
            @endcan
        </div>
    </div>


    <!-- Tabla de clientes -->
    @if ($customers->isEmpty())
        <p>No hay clientes registrados.</p>
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
                @foreach($customers as $index => $customer)
                    <tr>
                        <td class="border p-2 text-center">{{ $index + 1 }}</td>
                        <td class="border p-2">{{ $customer->nombre }}</td>
                        <td class="border p-2">{{ $customer->documento }}</td>
                        <td class="border p-2">{{ $customer->telefono ?? '-' }}</td>
                        <td class="border p-2">{{ $customer->email ?? '-' }}</td>
                        <td class="border p-2">{{ $customer->direccion ?? '-' }}</td>
                        <td class="border p-2 text-center whitespace-nowrap">
                            <div class="flex justify-center gap-2">
                                @can('editar cliente')
                                    <flux:modal.trigger name="register-customer">
                                        <button
                                            class="btn-editar-table"
                                            x-data
                                            x-init="tippy($el, { content: 'Editar Registro' })"
                                            x-on:click.prevent="$dispatch('open-modal-customer', { id: {{ $customer->id }} })"
                                        >
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    </flux:modal.trigger>
                                @endcan

                                @can('eliminar cliente')
                                    <flux:modal.trigger name="confirm-customer-deletion">
                                        <button
                                            class="btn-delete-table"
                                            x-data
                                            x-init="tippy($el, { content: 'Eliminar Registro' })"
                                            x-on:click.prevent="$dispatch('open-modal-delete-customer', { id: {{ $customer->id }} })"
                                        >
                                            <i class="fa-solid fa-trash-can"></i>
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
            {{ $customers->links() }}
        </div>
    @endif


    <flux:modal name="confirm-customer-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteCustomer" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que quieres eliminar al cliente?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Una vez eliminado el cliente, esta acción no se puede deshacer.') }}
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

    @livewire('customers.register')

</section>
<script>
    if (!window._cerrarModalDeleteCustomer) {
        window._cerrarModalDeleteCustomer = true;

        window.addEventListener('cerrarModalDeteleCustomer', () => {
            Flux.modal('confirm-customer-deletion').close();
            toastr.success('Operación exitosa.');
        });
    }
</script>
