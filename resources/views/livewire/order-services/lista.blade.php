<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Ordenes de Servicio') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona las ordenes de servicio del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @livewire('order-services.filter')
    <!-- Botones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            @can('exportar ventas')
                <a href="{{ route('sales.exportar.pdf', [
                        'fecha_desde' => $fechaDesdeFiltro,
                        'fecha_hasta' => $fechaHastaFiltro,
                        'estado_ids' => $estadoFiltro,
                    ]) }}"
                   class="btn-exportar-pdf"
                   x-data
                   x-init="tippy($el, { content: 'Exportar PDF' })">
                    <i class="fas fa-file-pdf"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
                <a href="{{ route('sales.exportar.excel', [
                        'fecha_desde' => $fechaDesdeFiltro,
                        'fecha_hasta' => $fechaHastaFiltro,
                        'estado_ids' => $estadoFiltro,
                    ]) }}"
                   class="btn-exportar-excel"
                   x-data
                   x-init="tippy($el, { content: 'Exportar Excel' })">
                    <i class="fas fa-file-excel"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
            @endcan

            @can('crear venta')
                <a href="{{ route('order-services.register') }}"
                   class="btn-nuevo"
                   x-data
                   x-init="tippy($el, { content: 'Nueva Orden' })">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('Nueva Orden') }}</span>
                </a>
            @endcan
        </div>
    </div>

    @if ($servicios->isEmpty())
        <p>No hay servicios registrados.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead>
                <tr>
                    <th class="border p-2">Nº</th>
                    <th class="border p-2">Fecha</th>
                    <th class="border p-2">Cliente</th>
                    <th class="border p-2">Usuario vendedor</th>
                    <th class="border p-2">Total</th>
                    <th class="border p-2">Estado</th>
                    <th class="border p-2">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($servicios as $index => $servicio)
                    <tr>
                        <td class="border p-2 text-center">{{ $loop->iteration + ($servicios->currentPage() - 1) * $servicios->perPage() }}</td>
                        <td class="border p-2 text-center">
                            {{ $servicio->fecha ? $servicio->fecha->format('d/m/Y H:m:s') : '' }}
                        </td>
                        <td class="border p-2">{{ $servicio->customer?->nombre ?? '-' }}</td>
                        <td class="border p-2">{{ $servicio->vendedor?->name ?? '-' }}</td>
                        <td class="border p-2 text-center">
                            S/ {{ number_format($servicio->total - $servicio->descuento, 2) }}
                        </td>
                        <td class="border p-2 text-center">
                            @php
                                $estado = $servicio->estado?->nombre ?? 'Desconocido';
                                $color = match($venta->estado_id) {
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
                            @if ($servicio->estado_id === 1)
                                {{-- Venta pendiente --}}
                                @can('ver venta')
                                    <a href="{{ route('sales.pay', $servicio->id) }}"
                                       class="border border-yellow-500 text-yellow-500 px-3 py-1 rounded hover:bg-yellow-500 hover:text-white cursor-pointer">
                                        Continuar
                                    </a>
                                @endcan
                            @elseif ($servicio->estado_id === 2 || $servicio->estado_id === 3)
                                {{-- Venta pagada --}}
                                @can('ver venta')
                                    <a href="{{ route('sales.pay', $servicio->id) }}"
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
        </div>
        <div class="mt-4">
            {{ $servicios->links() }}
        </div>
    @endif
</section>
