<div class="p-6 bg-white dark:bg-zinc-900 rounded-lg shadow-md space-y-6">

    {{-- Título --}}
    <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">Pagar Orden de Servicio</h2>

    {{-- Datos del cliente --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <flux:input
                :label="__('Cliente')"
                :value="$orden->customer?->nombre ?? '---'"
                type="text"
                disabled
            />
        </div>
        <div>
            <flux:input
                :label="__('DNI / RUC')"
                :value="$orden->customer?->documento ?? '---'"
                type="text"
                disabled
            />
        </div>
    </div>

    {{-- Servicios ordenados --}}
    <div>
        <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">Servicios Solicitados</h3>
        <table class="w-full text-left border-t border-gray-200 dark:border-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Servicio</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Precio</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Cantidad</th>
                <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @foreach($orden->detalles as $detalle)
                <tr class="border-t border-gray-200 dark:border-gray-600">
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">{{ $detalle->servicio?->nombre }}</td>
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">S/ {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">{{ $detalle->cantidad }}</td>
                    <td class="py-2 px-4 text-gray-800 dark:text-gray-100">S/ {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Resumen de totales --}}
    <div class="text-right space-y-2 text-gray-800 dark:text-gray-100">

        {{-- Subtotal --}}
        <div>Subtotal:
            <strong>S/ {{ number_format($orden->total, 2) }}</strong>
        </div>

        {{-- Descuento --}}
        <div class="text-left md:text-right">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descuento (S/)</label>
            @if(($orden->total - $totalPagado) > 0)
                <input
                    type="number"
                    min="0"
                    step="0.01"
                    wire:model.live.debounce="descuentoInput"
                    class="mt-1 w-full md:w-48 text-right rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 dark:bg-zinc-800 dark:text-white"
                    placeholder="0.00"
                />
            @else
                <div class="text-green-600 dark:text-green-400 font-semibold">
                    S/ {{ number_format($orden->descuento, 2) }}
                </div>
            @endif
        </div>

        {{-- Total a pagar --}}
        <div>Total a pagar:
            <strong class="text-green-600 dark:text-green-400">
                S/ {{ number_format($orden->total - $orden->descuento, 2) }}
            </strong>
        </div>

        {{-- Pagado --}}
        <div>Pagado:
            <strong class="text-blue-600 dark:text-blue-400">
                S/ {{ number_format($totalPagado, 2) }}
            </strong>
        </div>

        {{-- Restante --}}
        <div>Restante:
            <strong class="text-red-600 dark:text-red-400">
                S/ {{ number_format($orden->total - $orden->descuento - $totalPagado, 2) }}
            </strong>
        </div>
    </div>


    {{-- Formulario de nuevo pago --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Nuevo Pago</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <flux:select
                    wire:model.live="metodoPago"
                    :label="__('Método de Pago')"
                    required
                >
                    <option value="">{{ __('Seleccione...') }}</option>
                    @foreach($metodos as $m)
                        <option value="{{ $m->idParametro }}">{{ $m->nombre }}</option>
                    @endforeach
                </flux:select>
                @error('metodoPago')
                <div class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <flux:input
                    wire:model.live="pago_con"
                    :label="__('Monto (S/)')"
                    type="number"
                    step="0.01"
                    min="0"
                />
                @error('pago_con')
                <div class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @if(($orden->total - $orden->descuento - $totalPagado) > 0)
            <div class="text-right mt-2 text-green-600 dark:text-green-400">
                Vuelto: S/ {{ number_format($vuelto, 2) }}
            </div>
        @endif

        <div class="text-right mt-4">
            @if(($orden->total - $orden->descuento - $totalPagado) > 0)
                <button
                    wire:click="procesarPago"
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition cursor-pointer"
                >
                    Registrar Pago
                </button>
            @else
                <div class="inline-block px-6 py-2 bg-green-100 text-green-700 rounded-md">
                    Pago completado
                </div>
            @endif
        </div>

    </div>

    {{-- Historial de pagos --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Historial de Pagos</h3>

        @if(count($pagos))
            <table class="w-full text-left border-t border-gray-200 dark:border-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Fecha</th>
                    <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Método</th>
                    <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300">Monto</th>
                    <th class="py-2 px-4 text-sm text-gray-600 dark:text-gray-300 text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($pagos as $p)
                    <tr class="border-t border-gray-200 dark:border-gray-600">
                        <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                            {{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                            {{ $p->metodoPago?->nombre ?? 'N/A' }}
                        </td>
                        <td class="py-2 px-4 text-gray-800 dark:text-gray-100">
                            S/ {{ number_format($p->monto_pagado, 2) }}
                        </td>
                        <td class="py-2 px-4 text-center">
                            <button
                                wire:click="eliminarPago({{ $p->id }})"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-600 text-sm cursor-pointer"
                                onclick="return confirm('¿Estás seguro de eliminar este pago?')"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        @else
            <p class="text-gray-500 dark:text-gray-400">No hay pagos registrados.</p>
        @endif
    </div>

    {{-- Mensaje flash --}}
    @if (session()->has('success'))
        <div class="mt-4 text-green-600 dark:text-green-400 font-semibold">
            {{ session('success') }}
        </div>
    @endif

</div>
