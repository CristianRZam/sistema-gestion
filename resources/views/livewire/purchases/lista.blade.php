<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Compras') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona las compras del sistema') }}</flux:subheading>
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
    @if ($compras->isEmpty())
        <p>No hay compras registradas.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">Nº</th>
                <th class="border p-2">Fecha</th>
                <th class="border p-2">Proveedor</th>
                <th class="border p-2">Usuario comprador</th>
                <th class="border p-2">Total</th>
                <th class="border p-2">Estado</th>
                <th class="border p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($compras as $index => $compra)
                <tr>
                    <td class="border p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border p-2 text-center">{{ $compra->fecha_compra->format('d/m/Y') }}</td>
                    <td class="border p-2">{{ $compra->supplier?->nombre ?? '-' }}</td>
                    <td class="border p-2">{{ $compra->comprador?->name ?? '-' }}</td>
                    <td class="border p-2 text-center">S/ {{ number_format($compra->total, 2) }}</td>
                    <td class="border p-2 text-center">
                        @php
                            $estado = $compra->estadoCompra?->nombre ?? 'Desconocido';
                            $color = match($venta->estado_compra_id) {
                                1 => 'bg-yellow-500 text-white', // Pendiente
                                2 => 'bg-green-600 text-white',  // Pagada
                                3 => 'bg-red-600 text-white',    // Anulada
                                default => 'bg-gray-600 text-white',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded text-sm font-semibold {{ $color }}">
                            {{ $estado }}
                        </span>
                    </td>


                    <td class="border p-2 text-center">
                        {{-- Botón "Ver" disponible siempre si el usuario tiene permiso --}}
                        {{-- Acciones según estado_venta_id --}}
                        @if ($compra->estado_compra_id === 1)
                            {{-- Venta pendiente --}}
                            @can('ver venta')
                                <a href="{{ route('sales.pay', $compra->id) }}"
                                   class="border border-yellow-500 text-yellow-500 px-3 py-1 rounded hover:bg-yellow-500 hover:text-white cursor-pointer">
                                    Continuar venta
                                </a>
                            @endcan
                        @elseif ($compra->estado_compra_id === 2 || $venta->estado_compra_id === 3)
                            {{-- Venta pagada --}}
                            @can('ver venta')
                                <a href="{{ route('sales.pay', $compra->id) }}"
                                   class="border border-blue-500 text-blue-500 px-3 py-1 rounded hover:bg-blue-500 hover:text-white mr-2 cursor-pointer">
                                    Ver
                                </a>
                            @endcan
                        @endif
                    </td>

                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

</section>
