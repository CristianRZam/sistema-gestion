<div class="w-full">

    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Reservas') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Gestiona las reservas del sistema') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @livewire('reservations.filter-room-selector')

    @php
        function estadoColor($codigo) {
            return match($codigo) {
                1 => 'text-green-500 dark:text-green-400', // Disponible
                2 => 'text-orange-500 dark:text-orange-400', // Check-out pendiente
                3 => 'text-gray-500 dark:text-gray-400', // Inhabilitada
                4 => 'text-yellow-500 dark:text-yellow-400', // Limpieza
                5 => 'text-amber-600 dark:text-amber-500', // Mantenimiento
                6 => 'text-red-500 dark:text-red-400', // Ocupada
                7 => 'text-blue-500 dark:text-blue-400', // Reservada
                default => 'text-zinc-500 dark:text-zinc-400',
            };
        }

        function estadoIcono($codigo) {
        return match($codigo) {
            1 => 'fa-circle-check',      // Disponible
            2 => 'fa-clock',             // Check-out pendiente
            3 => 'fa-ban',               // Inhabilitada
            4 => 'fa-broom',             // Limpieza
            5 => 'fa-tools',             // Mantenimiento
            6 => 'fa-bed',               // Ocupada
            7 => 'fa-calendar-check',    // Reservada
            default => 'fa-circle-question',
        };
    }
    @endphp


        <!-- Tabs de pisos con ícono -->
    <div class="flex flex-wrap gap-3 border-b pb-2 border-gray-300 dark:border-zinc-700">
        @foreach($pisos as $piso)
            <button
                wire:click="setPisoActivo({{ $piso->idParametro }})"
                class="cursor-pointer Zflex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-offset-2
                   {{ $pisoActivo === $piso->idParametro
                       ? 'bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600'
                       : 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700' }}">
                {{ $piso->nombreCorto }} <i class="fas fa-building text-base"></i>
                <span>{{ $piso->valor }}</span>
            </button>
        @endforeach
    </div>


    <!-- Habitaciones del piso activo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
        @forelse($habitaciones as $habitacion)
            <div class="border rounded-xl p-4 shadow-sm bg-white dark:bg-zinc-800 transition hover:shadow-md h-full flex flex-col justify-between">
                <!-- Parte superior: Número y Tipo -->
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                        <i class="fa-solid fa-door-open"></i>
                        Nº {{ $habitacion->numero }}
                    </h3>
                    <p class="text-sm flex items-center gap-1 text-gray-700 dark:text-gray-200">
                        <i class="fas fa-bed"></i>
                        <span class="font-medium">{{ $habitacion->tipo->nombre ?? '-' }}</span>
                    </p>
                </div>

                <!-- Parte inferior: Estado y Precio -->
                <div class="flex justify-between items-center mt-4">
                    <p class="text-sm flex items-center gap-2">
                        <i class="fas {{ estadoIcono($habitacion->estado_id ?? '') }} {{ estadoColor($habitacion->estado_id ?? '') }}"></i>
                        <span class="font-bold text-gray-700 dark:text-gray-300">
                        {{ $habitacion->estado_nombre ?? '-' }}
                    </span>
                    </p>
                    <p class="text-sm flex items-center gap-2 text-gray-700 dark:text-gray-200 font-semibold">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        S/ {{ number_format($habitacion->precio, 2) }}
                    </p>
                </div>
            </div>
        @empty
            <p class="col-span-1 sm:col-span-2 md:col-span-4 text-center text-gray-600 dark:text-gray-300">
                No hay habitaciones registradas en este piso.
            </p>
        @endforelse
    </div>



</div>
