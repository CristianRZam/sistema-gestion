<div class="p-4 border-b border-gray-300 dark:border-b-gray-600 rounded mb-4">
    <form wire:submit.prevent="filtrar">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <flux:input
                wire:model.defer="nombreFiltro"
                placeholder="Nombre del producto"
                :label="__('Nombre')"
                type="text"
                min="0"
            />

            <div wire:ignore class="flex flex-col">
                <label for="categoriaFiltroSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    {{ __('Categoría') }}
                </label>
                <div id="categoriaFiltroSelect" class="w-full mt-1"></div>
            </div>

            <flux:input
                :label="__('Stock mínimo')"
                wire:model.defer="stockFiltro"
                placeholder="Stock mínimo"
                type="number"
                min="0"
            />

        </div>

        <div class="mt-4 flex justify-center gap-2 flex-col sm:flex-row">
            <button type="submit"
                    id="btnFiltrar"
                    class="flex items-center justify-center gap-2 bg-blue-600 text-white px-10 py-2 rounded-full hover:bg-blue-700 transition dark:bg-blue-500 dark:hover:bg-blue-600 cursor-pointer">
                <i class="fas fa-filter"></i>
            </button>
            <button type="button"
                    id="btnLimpiar"
                    wire:click="resetFiltros"
                    class="flex items-center justify-center gap-2 border border-gray-400 text-gray-800 px-10 py-2 rounded-full hover:bg-gray-100 transition dark:border-gray-500 dark:text-white dark:hover:bg-gray-700 cursor-pointer">
                <i class="fas fa-broom"></i>
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        window.initCategoriaFiltroVirtualSelect = function () {
            const contenedor = document.querySelector('#categoriaFiltroSelect');
            if (!contenedor) {
                // Si el elemento no existe aún, reintenta luego
                return;
            }

            // Si ya fue inicializado, evita volver a inicializar
            if (contenedor.classList.contains('vscomp-initialized')) return;

            // Asegúrate de que la función existe
            if (typeof inicializarVirtualSelect === 'function') {
                const opciones = [{!!
                    collect($categorias)
                        ->map(fn($c) => "{ label: '".e($c->nombre)."', value: '".e($c->idParametro)."' }")
                        ->implode(',')
                !!}];

                inicializarVirtualSelect('categoriaFiltroSelect', opciones, {
                    multiple: true,
                });

                contenedor.addEventListener('change', function () {
                    Livewire.dispatch('actualizarCategoriasDesdeJS', { valores: this.value });
                });
            } else {
                // Función aún no está lista
                setTimeout(window.initCategoriaFiltroVirtualSelect, 100);
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            window.initCategoriaFiltroVirtualSelect();
            tippy('#btnFiltrar', { content: 'Filtrar' });
            tippy('#btnLimpiar', { content: 'Limpiar' });
        });

        document.addEventListener('livewire:navigated', () => {
            window.initCategoriaFiltroVirtualSelect();
            tippy('#btnFiltrar', { content: 'Filtrar' });
            tippy('#btnLimpiar', { content: 'Limpiar' });
        });
    </script>
@endpush


<script>
    document.addEventListener('livewire:navigated', function () {
        Livewire.on('limpiarFiltros', () => {
            document.querySelector('#categoriaFiltroSelect').reset();
        });
    });
</script>
