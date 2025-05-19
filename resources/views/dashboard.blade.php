<x-layouts.app :title="__('Dashboard')">
    <div class="flex flex-col gap-6 p-4">
        {{-- Encabezado --}}
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-300">Resumen general del sistema</p>
        </div>

        {{-- Tarjetas resumen en 3 columnas --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach(range(1, 3) as $i)
                <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="relative aspect-video overflow-hidden rounded-lg">
                        <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div class="mt-4">
                        <h2 class="text-lg font-medium text-gray-800 dark:text-white">Módulo {{ $i }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-300">Descripción breve del módulo {{ $i }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Sección inferior expandida --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <h2 class="text-xl font-medium text-gray-800 dark:text-white mb-4">Estadísticas generales</h2>
            <div class="relative h-64 overflow-hidden rounded-lg">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
    </div>
</x-layouts.app>
