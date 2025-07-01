<div class="shadow space-y-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Resumen General</h2>

    <!-- Filtro de fechas -->
    <div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 rounded-xl shadow p-4 mt-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Botones -->
            <div class="flex flex-wrap gap-2">
                @foreach (['hoy' => 'Hoy', 'semana' => 'Esta semana', 'mes' => 'Este mes', 'personalizado' => 'Personalizado'] as $clave => $texto)
                    <button
                        wire:click="$set('filtroFecha', '{{ $clave }}')"
                        class="cursor-pointer px-4 py-1.5 rounded-full text-sm font-medium transition-all border border-gray-300 dark:border-gray-600 hover:bg-blue-50 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:focus:ring-blue-500
            {{ $filtroFecha === $clave ? 'bg-blue-600 text-white dark:bg-blue-500 dark:text-white' : 'text-gray-700 dark:text-gray-200 bg-transparent' }}"
                    >
                        {{ $texto }}
                    </button>
                @endforeach
            </div>

            <!-- Rango personalizado -->
            @if($filtroFecha === 'personalizado')
                <div class="flex flex-col sm:flex-row gap-4 items-center mt-2 md:mt-0">
                    <div class="flex flex-col">
                        <label for="fechaInicio" class="text-sm text-gray-600 dark:text-gray-300 mb-1">Desde</label>
                        <input type="date" wire:model.live="fechaInicio" id="fechaInicio"
                               class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-zinc-800 dark:text-white text-sm px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex flex-col">
                        <label for="fechaFin" class="text-sm text-gray-600 dark:text-gray-300 mb-1">Hasta</label>
                        <input type="date" wire:model.live="fechaFin" id="fechaFin"
                               class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-zinc-800 dark:text-white text-sm px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                @if($fechaInicio && $fechaFin && \Carbon\Carbon::parse($fechaInicio)->gt(\Carbon\Carbon::parse($fechaFin)))
                    <p class="text-red-500 text-sm mt-2">La fecha de inicio no puede ser mayor que la fecha de fin.</p>
                @endif
            @endif
        </div>
    </div>



    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Clientes -->
        <div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 shadow rounded-xl p-4 flex items-center space-x-4">
            <div class="bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-6a4 4 0 110-8 4 4 0 010 8z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-sm text-gray-500 dark:text-gray-400">Clientes</h4>
                <p class="text-xl font-semibold text-gray-800 dark:text-white">
                    {{ $cantidadClientes }}
                </p>
            </div>
        </div>

        <!-- Ventas -->
        <div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 shadow rounded-xl p-4 flex items-center space-x-4">
            <div class="bg-green-200 dark:bg-green-900 text-green-800 dark:text-green-300 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 9H4l1-9z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm text-gray-700 dark:text-gray-300">Ventas hoy</h4>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $cantidadVentas }}</p>
            </div>
        </div>

        <!-- Productos vendidos -->
        <div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 shadow rounded-xl p-4 flex items-center space-x-4">
            <div class="bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 12V6a2 2 0 00-2-2h-3.172a2 2 0 01-1.414-.586l-.828-.828A2 2 0 0011.172 2H9a2 2 0 00-2 2v2H6a2 2 0 00-2 2v6m0 0v6a2 2 0 002 2h12a2 2 0 002-2v-6H4z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm text-gray-500 dark:text-gray-400">Productos vendidos hoy</h4>
                <p class="text-xl font-semibold text-gray-800 dark:text-white">{{ $cantidadProductosVendidos }}</p>
            </div>
        </div>

        <!-- Ingresos del día -->
        <div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 shadow rounded-xl p-4 flex items-center space-x-4">
            <div class="bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-300 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 12V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2v-4zm-7 1a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm text-gray-500 dark:text-gray-400">Ingresos hoy</h4>
                <p class="text-xl font-semibold text-gray-800 dark:text-white">S/ {{ number_format($ingresosHoy, 2) }}</p>
            </div>
        </div>

        <!-- Ganancias del día -->
        <div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 shadow rounded-xl p-4 flex items-center space-x-4">
            <div class="bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.1 0-2 .9-2 2v4H8v2h2v2h2v-2h2v-2h-2v-4h2V8h-2z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm text-gray-500 dark:text-gray-400">Ganancias hoy</h4>
                <p class="text-xl font-semibold text-gray-800 dark:text-white">
                    S/ {{ number_format($gananciasHoy, 2) }}
                </p>
            </div>
        </div>
    </div>
</div>
