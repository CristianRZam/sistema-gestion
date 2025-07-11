<div class="p-4 border-b border-gray-300 dark:border-b-gray-600 rounded mb-4">
    <form wire:submit.prevent="filtrar">
        @php
            $hoy = now()->format('Y-m-d');
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-2">

            {{-- Fecha Desde --}}
            <flux:input
                type="date"
                wire:model.live="fechaDesdeFiltro"
                :label="__('Fecha desde')"
                min="{{ $hoy }}"
            />

            {{-- Fecha Hasta --}}
            <flux:input
                type="date"
                wire:model.live="fechaHastaFiltro"
                :label="__('Fecha hasta')"
                min="{{ $hoy }}"
            />
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

        document.addEventListener('DOMContentLoaded', () => {
            tippy('#btnFiltrar', { content: 'Filtrar' });
            tippy('#btnLimpiar', { content: 'Limpiar' });
        });

        document.addEventListener('livewire:navigated', () => {
            tippy('#btnFiltrar', { content: 'Filtrar' });
            tippy('#btnLimpiar', { content: 'Limpiar' });
        });

    </script>
@endpush

<script>

    if (!window._toastFiltroRoomSelector) {
        window._toastFiltroRoomSelector = true;

        window.addEventListener('mostrarToastFechaReserva', (event) => {
            toastr.error(event.detail[0].mensaje);
        });

    }
</script>
