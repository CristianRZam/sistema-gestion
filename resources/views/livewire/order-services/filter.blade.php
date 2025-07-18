<div class="p-4 border-b border-gray-300 dark:border-b-gray-600 rounded mb-4">
    <form wire:submit.prevent="filtrar">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">

            {{-- Fecha Desde --}}
            <flux:input
                type="date"
                wire:model.live="fechaDesdeFiltro"
                :label="__('Fecha desde')"
            />

            {{-- Fecha Hasta --}}
            <flux:input
                type="date"
                wire:model.live="fechaHastaFiltro"
                :label="__('Fecha hasta')"
            />

            {{-- Estados (virtual select) --}}
            <div wire:ignore class="flex flex-col">
                <label for="estadoFiltroSelect" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                    {{ __('Estado de orden') }}
                </label>
                <div id="estadoFiltroSelect" class="w-full mt-1"></div>
            </div>

        </div>

        <div class="mt-4 flex justify-center gap-2 flex-col sm:flex-row">
            <button type="submit"
                    id="btnFiltrar"
                    class="flex items-center justify-center gap-2 bg-blue-600 text-white px-10 py-2 rounded-full hover:bg-blue-700 transition dark:bg-blue-500 dark:hover:bg-blue-600 cursor-pointer">
                <i class="fas fa-filter"></i>
                <span>Filtrar</span>
            </button>

            <button type="button"
                    id="btnLimpiar"
                    wire:click="resetFiltros"
                    class="flex items-center justify-center gap-2 border border-gray-400 text-gray-800 px-10 py-2 rounded-full hover:bg-gray-100 transition dark:border-gray-500 dark:text-white dark:hover:bg-gray-700 cursor-pointer">
                <i class="fas fa-broom"></i>
                <span>Limpiar</span>
            </button>
        </div>
    </form>
</div>
@push('scripts')
    <script>
        function initVirtualSelect(id, opciones, multiple = true) {
            const el = document.getElementById(id);
            if (!el || el.classList.contains('vscomp-initialized')) return;

            if (typeof inicializarVirtualSelect !== 'function') {
                setTimeout(() => initVirtualSelect(id, opciones, multiple), 100);
                return;
            }

            inicializarVirtualSelect(id, opciones, { multiple });

            el.addEventListener('change', function () {
                const values = this.value || [];
                if (id === 'estadoFiltroSelect') {
                    Livewire.dispatch('actualizarEstadosDesdeJS', { valores: values });
                }
            });
        }

        window.initFiltros = function () {
            // Cargar opciones desde PHP (estados y usuarios)
            const estados = [{!!
            collect($estados)->map(fn($e) => "{ label: '".e($e->nombre)."', value: '".e($e->idParametro)."' }")->implode(',')
        !!}];


            initVirtualSelect('estadoFiltroSelect', estados);
        };

        document.addEventListener('DOMContentLoaded', () => {
            window.initFiltros();
            tippy('#btnFiltrar', { content: 'Filtrar' });
            tippy('#btnLimpiar', { content: 'Limpiar' });
        });

        document.addEventListener('livewire:navigated', () => {
            window.initFiltros();
        });

    </script>
@endpush

<script>
    document.addEventListener('livewire:navigated', function () {
        Livewire.on('limpiarFiltros', () => {
            document.querySelector('#estadoFiltroSelect').reset();
        });
    });

    if (!window._toastFiltroVentasRegistrado) {
        window._toastFiltroVentasRegistrado = true;

        window.addEventListener('mostrarToastFechaVenta', (event) => {
            toastr.error(event.detail[0].mensaje);
        });

    }
</script>
