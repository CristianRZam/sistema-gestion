<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Ventas') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona las ventas del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <!-- Botones alineados a la derecha -->
    <div class="mb-4 text-right">
        @can('exportar ventas')
            <a href="{{ route('sales.exportar.pdf') }}"
               class="border border-red-600 text-red-600 px-4 py-2 rounded hover:bg-red-600 hover:text-white mr-2">
                Exportar PDF
            </a>
            <a href="{{ route('sales.exportar.excel') }}"
               class="border border-green-600 text-green-600 px-4 py-2 rounded hover:bg-green-600 hover:text-white mr-2">
                Exportar Excel
            </a>
        @endcan

        @can('crear venta')
                <a href="{{ route('sales.register') }}"
                   class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-500 hover:text-white cursor-pointer">
                    {{ __('Nueva Venta') }}
                </a>
            @endcan
    </div>

    <!-- Tabla de ventas -->
    @if ($ventas->isEmpty())
        <p>No hay ventas registradas.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">Nº</th>
                <th class="border p-2">Fecha</th>
                <th class="border p-2">Cliente</th>
                <th class="border p-2">Usuario vendedor</th>
                <th class="border p-2">Total</th>
                <th class="border p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($ventas as $index => $venta)
                <tr>
                    <td class="border p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border p-2 text-center">{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                    <td class="border p-2">{{ $venta->customer?->nombre ?? '-' }}</td>
                    <td class="border p-2">{{ $venta->vendedor?->name ?? '-' }}</td>
                    <td class="border p-2 text-center">S/ {{ number_format($venta->total, 2) }}</td>
                    <td class="border p-2 text-center">
                        @can('ver venta')
                            <a href="{{ route('sales.show', $venta->id) }}"
                               class="border border-blue-500 text-blue-500 px-3 py-1 rounded hover:bg-blue-500 hover:text-white mr-2 cursor-pointer">
                                Ver
                            </a>
                        @endcan
                        @can('eliminar venta')
                            <flux:modal.trigger name="confirm-sale-deletion">
                                <button
                                    class="border border-red-500 text-red-500 px-3 py-1 rounded hover:bg-red-500 hover:text-white cursor-pointer"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal-delete-sale', { id: {{ $venta->id }} })"
                                >
                                    Eliminar
                                </button>
                            </flux:modal.trigger>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <flux:modal name="confirm-sale-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteSale" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que quieres eliminar esta venta?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Una vez eliminada la venta, esta acción no se puede deshacer.') }}
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


</section>

<script>
    window.addEventListener('cerrarModalDeteleSale', () => {
        Flux.modal('confirm-sale-deletion').close();
    });
</script>
