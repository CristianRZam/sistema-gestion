<div class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Finalizar Reserva') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('Completa los datos restantes para registrar una reserva válida. Aquí podrás asignar cliente, revisar habitaciones, establecer fechas, añadir notas y registrar los pagos.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <!-- Información del cliente -->
    <div class="relative flex border border-gray-300 dark:border-gray-700 border-l-0 rounded-lg bg-white dark:bg-zinc-900 overflow-hidden">
        <!-- Botón de editar con ícono -->
        <div class="absolute top-2 right-2 flex items-center gap-3">
            {{-- Botón Editar Cliente --}}
            <flux:modal.trigger name="register-customer">
                <button
                    class="text-blue-600 hover:text-blue-800 transition cursor-pointer"
                    x-on:click.prevent="$dispatch('open-modal-customer', { id: null, modo: 'venta' })"
                    aria-label="{{ __('Editar cliente') }}"
                >
                    <i class="fa-solid fa-pencil"></i>
                </button>
            </flux:modal.trigger>

            {{-- Botón Eliminar Cliente --}}
            <button
                wire:click="eliminarClienteSeleccionado"
                class="text-red-600 hover:text-red-800 transition cursor-pointer"
                aria-label="{{ __('Eliminar cliente') }}"
            >
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>


        <!-- Franja izquierda intercalada, espaciada y con altura completa -->
        <div class="w-1 flex flex-col justify-between py-0">
            <div class="h-4 bg-red-500"></div>
            <div class="h-4 bg-cyan-500"></div>
            <div class="h-4 bg-red-500"></div>
            <div class="h-4 bg-cyan-500"></div>
        </div>

        <!-- Contenido informativo del cliente -->
        <div class="flex-1 p-4">
            <p class="text-gray-700 dark:text-gray-300">
                <strong>{{ __('Nombre:') }}</strong> {{ $cliente_seleccionado['nombre'] ?? __('No seleccionado') }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
                <strong>{{ __('Documento:') }}</strong> {{ $cliente_seleccionado['dni'] ?? '---' }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
                <strong>{{ __('Dirección:') }}</strong> {{ $cliente_seleccionado['direccion'] ?? '---' }}
            </p>
        </div>
    </div>
    @livewire('customers.register')


    <!-- Detalles de habitaciones reservadas -->
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Habitaciones seleccionadas</h2>

        <div class="overflow-auto rounded-md border border-gray-300 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">N°</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Habitación</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Fecha inicio</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Fecha fin</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Personas</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Precio</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($detallesHabitaciones as $index => $detalle)
                    <tr>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            {{ $detalle['room']['numero'] ?? '---' }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($detalle['fecha_inicio'])->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($detalle['fecha_fin'])->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            {{ $detalle['cantidad_personas'] }}
                        </td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                            S/ {{ number_format($detalle['precio'], 2) }}
                        </td>
                    </tr>
                @endforeach

                @if(empty($detallesHabitaciones))
                    <tr>
                        <td colspan="6" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                            No hay habitaciones registradas en esta reserva.
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

</div>
