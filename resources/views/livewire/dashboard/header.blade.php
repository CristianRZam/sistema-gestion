<div class="shadow space-y-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Resumen General</h2>

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
    </div>
</div>
