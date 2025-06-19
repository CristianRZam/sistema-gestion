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

            <flux:select
                wire:model.defer="categoriaFiltro"
                placeholder="Selecciona una categoría"
                :label="__('Categoría')"
            >
                <option value="">-- Categoría --</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->idParametro }}">{{ $categoria->nombre }}</option>
                @endforeach
            </flux:select>

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

<script>
    document.addEventListener('livewire:navigated', function () {
        tippy('#btnFiltrar', {
            content: 'Filtrar',
        });

        tippy('#btnLimpiar', {
            content: 'Limpiar',
        });
    });
</script>
