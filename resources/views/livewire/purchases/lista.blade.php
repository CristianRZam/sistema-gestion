<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Compras') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona las compras del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @livewire('purchases.filter')
    <!-- Botones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            @can('exportar compras')
                <a href="{{ route('purchases.exportar.pdf', [
                        'fecha_desde' => $fechaDesdeFiltro,
                        'fecha_hasta' => $fechaHastaFiltro,
                        'estado_ids' => $estadoFiltro,
                        'usuario_ids' => $usuarioFiltro,
                    ]) }}"
                   class="btn-exportar-pdf"
                   x-data
                   x-init="tippy($el, { content: 'Exportar PDF' })">
                    <i class="fas fa-file-pdf"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
                <a href="{{ route('purchases.exportar.excel', [
                        'fecha_desde' => $fechaDesdeFiltro,
                        'fecha_hasta' => $fechaHastaFiltro,
                        'estado_ids' => $estadoFiltro,
                        'usuario_ids' => $usuarioFiltro,
                    ]) }}"
                   class="btn-exportar-excel"
                   x-data
                   x-init="tippy($el, { content: 'Exportar Excel' })">
                    <i class="fas fa-file-excel"></i>
                    <span>{{ __('Exportar') }}</span>
                </a>
            @endcan

            @can('crear compra')
                <a  href="{{ route('purchases.register') }}"
                    class="btn-nuevo"
                    x-data
                    x-init="tippy($el, { content: 'Nueva Compra' })">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('Nueva Compra') }}</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Tabla de ventas -->
    @if ($compras->isEmpty())
        <p>No hay compras registradas.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead>
                <tr>
                    <th class="border p-2">Nº</th>
                    <th class="border p-2">Fecha compra</th>
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
                        <td class="border p-2 text-center">{{ $loop->iteration + ($compras->currentPage() - 1) * $compras->perPage() }}</td>
                        <td class="border p-2 text-center">
                            {{ optional($compra->fecha_compra)->format('d/m/Y H:i') }}
                        </td>
                        <td class="border p-2">{{ $compra->supplier?->nombre ?? '-' }}</td>
                        <td class="border p-2">{{ $compra->comprador?->name ?? '-' }}</td>
                        <td class="border p-2 text-center">S/ {{ number_format($compra->total, 2) }}</td>
                        <td class="border p-2 text-center">
                            @php
                                $estado = $compra->estadoCompra?->nombre ?? 'Desconocido';
                                $color = match($compra->estado_compra_id) {
                                    1 => 'bg-yellow-500 text-black',  // Pendiente
                                    2 => 'bg-blue-500 text-white',    // Pagada
                                    3 => 'bg-green-600 text-white',   // Completada
                                    4 => 'bg-red-600 text-white',     // Cancelada
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
                            @if ($compra->estado_compra_id === 1 || $compra->estado_compra_id === 2)
                                {{-- Venta pendiente --}}
                                @can('ver compra')
                                    <a href="{{ route('purchases.pay', $compra->id) }}"
                                       class="border border-yellow-500 text-yellow-500 px-3 py-1 rounded hover:bg-yellow-500 hover:text-white cursor-pointer">
                                        Continuar compra
                                    </a>
                                @endcan
                            @elseif ($compra->estado_compra_id === 3 || $compra->estado_compra_id === 4)
                                {{-- Venta pagada --}}
                                @can('ver compra')
                                    <a href="{{ route('purchases.pay', $compra->id) }}"
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
            {{ $compras->links() }}
        </div>
    @endif

</section>
