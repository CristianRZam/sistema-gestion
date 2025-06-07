<div>
    @livewire('dashboard.header')

    <div class="bg-white dark:bg-zinc-900 border dark:border-gray-700 shadow rounded-xl p-4 mt-4">
        <div class="flex">
            <!-- Primera mitad -->
            <div class="w-1/2 pr-2">
                @livewire('dashboard.payment-method')
            </div>

            <!-- Segunda mitad -->
            <div class="w-1/2 pl-2">
                @livewire('dashboard.top-product')
            </div>
        </div>
    </div>

    @livewire('dashboard.sales-hour')

</div>
