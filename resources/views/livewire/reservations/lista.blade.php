<section class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Reservas') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona las reservas del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @livewire('reservations.filter')
    <!-- Botones alineados a la derecha -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-2 sm:gap-4">
            @can('exportar ventas')
                <a href="{{ route('reservations.exportar.pdf', [
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
                <a href="{{ route('reservations.exportar.excel', [
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

            @can('crear venta')
                <a href="{{ route('reservations.selector') }}"
                   class="btn-nuevo"
                   x-data
                   x-init="tippy($el, { content: 'Nueva Reserva' })">
                    <i class="fas fa-plus"></i>
                    <span>{{ __('Nueva Reserva') }}</span>
                </a>
            @endcan
        </div>
    </div>
    <!-- Tabla de ventas -->
    @if ($reservas->isEmpty())
        <p>No hay reservas registradas.</p>
    @else
        <div class="w-full overflow-x-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead>
                <tr>
                    <th class="border p-2">Nº</th>
                    <th class="border p-2">Fecha venta</th>
                    <th class="border p-2">Cliente</th>
                    <th class="border p-2">Usuario vendedor</th>
                    <th class="border p-2">Total</th>
                    <th class="border p-2">Estado</th>
                    <th class="border p-2">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($reservas as $index => $reserva)
                    <tr>
                        <td class="border p-2 text-center">{{ $loop->iteration + ($reservas->currentPage() - 1) * $reservas->perPage() }}</td>
                        <td class="border p-2 text-center">
                            {{ $reserva->fecha_reserva ? $reserva->fecha_reserva->format('d/m/Y H:m') : '' }}
                        </td>
                        <td class="border p-2">{{ $reserva->customer?->nombre ?? '-' }}</td>
                        <td class="border p-2">{{ $reserva->usuario?->name ?? '-' }}</td>
                        <td class="border p-2 text-center">
                            S/ {{ number_format($reserva->monto_total, 2) }}
                        </td>
                        <td class="border p-2 text-center">
                            @php
                                $estado = $reserva->estado?->nombre ?? 'Desconocido';
                                $color = match($reserva->estado_id) {
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
                            @if ($reserva->estado_id === 1)
                                {{-- Venta pendiente --}}
                                @can('ver venta')
                                    <a href="{{ route('reservations.register', $reserva->id) }}"
                                       class="border border-yellow-500 text-yellow-500 px-3 py-1 rounded hover:bg-yellow-500 hover:text-white cursor-pointer">
                                        Continuar
                                    </a>
                                @endcan
                            @elseif ($reserva->estado_id === 2 || $reserva->estado_id === 3)
                                {{-- Venta pagada --}}
                                @can('ver venta')
                                    <a href="{{ route('sales.pay', $reserva->id) }}"
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
            {{ $reservas->links() }}
        </div>
    @endif

</section>
