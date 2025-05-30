<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Clientes') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona los clientes del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>


    <!-- Bototones alineados a la derecha -->
    <div class="mb-4 text-right">
        @can('exportar clientes')
            <a href="{{ route('customers.exportar.pdf') }}"
               class="border border-red-600 text-red-600 px-4 py-2 rounded hover:bg-red-600 hover:text-white mr-2">
                Exportar PDF
            </a>
            <a href="{{ route('customers.exportar.excel') }}"
               class="border border-green-600 text-green-600 px-4 py-2 rounded hover:bg-green-600 hover:text-white mr-2">
                Exportar Excel
            </a>
        @endcan

        <!-- Botón Agregar -->
        @can('crear cliente')
            <flux:modal.trigger name="register-customer">
                <button
                    class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-500 hover:text-white cursor-pointer"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal-customer')">
                    {{ __('Agregar') }}
                </button>
            </flux:modal.trigger>
        @endcan
    </div>


    <!-- Tabla de clientes -->
    @if ($customers->isEmpty())
        <p>No hay clientes registrados.</p>
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
            @foreach($customers as $index => $customer)
                <tr>
                    <td class="border p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border p-2">{{ $customer->nombre }}</td>
                    <td class="border p-2">{{ $customer->documento }}</td>
                    <td class="border p-2">{{ $customer->telefono ?? '-' }}</td>
                    <td class="border p-2">{{ $customer->email ?? '-' }}</td>
                    <td class="border p-2">{{ $customer->direccion ?? '-' }}</td>
                    <td class="border p-2 text-center">
                        @can('editar cliente')
                            <flux:modal.trigger name="register-customer">
                                <button
                                    class="border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-white mr-2 cursor-pointer"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal', { id: {{ $customer->id }} })"
                                >
                                    {{ __('Editar') }}
                                </button>
                            </flux:modal.trigger>
                        @endcan
                        @can('eliminar cliente')
                            <flux:modal.trigger name="confirm-customer-deletion">
                                <button
                                    class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-500 hover:text-white cursor-pointer"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal-delete-customer', { id: {{ $customer->id }} })"
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
    window.addEventListener('cerrarModalDeteleCustomer', () => {
        Flux.modal('confirm-customer-deletion').close();
    });
</script>
