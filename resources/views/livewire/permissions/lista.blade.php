<div class="p-6">
    <!-- Cabecera -->
    <div class="relative mb-6 w-full flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Permisos') }}</flux:heading>
            <flux:subheading size="lg" class="mb-6">
                {{ __('Gestiona los permisos para el rol de ') }} {{$role->name}}
            </flux:subheading>
        </div>

        <!-- Botón de volver -->
        <a href="{{ route('roles') }}"
           class="border border-red-500 text-red-700 px-4 py-2 rounded w-max
          hover:bg-red-500 hover:text-white
          dark:border-red-400 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">
            ← Volver
        </a>

    </div>

    @if ($message)
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => { show = false; @this.call('clearMessage') }, 2000)"
            x-show="show"
            x-transition.opacity.duration.500ms
            class="mb-4 p-4 rounded-md border
               bg-green-100 border-green-400 text-green-800
               dark:bg-green-900 dark:border-green-700 dark:text-green-300"
            role="alert"
        >
            {{ $message }}
        </div>
    @endif




    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach ($permissions as $permission)
            @php
                $permissionName = $permission['name'];
                $isChecked = in_array($permissionName, $selectedPermissions);
            @endphp
            <label
                class="flex items-start gap-3 p-4 border rounded-lg transition-shadow cursor-pointer
                {{ $isChecked
                    ? 'bg-blue-50 border-blue-400 shadow-md dark:bg-blue-900/20 dark:border-blue-500'
                    : 'bg-white border-gray-300 shadow-sm hover:shadow-md dark:bg-gray-800 dark:border-gray-600 dark:hover:shadow-md' }}"
                        >
                <input
                    type="checkbox"
                    value="{{ $permissionName }}"
                    wire:model="selectedPermissions"
                    wire:change="syncPermissions"
                    class="peer hidden"
                />
                <div class="h-5 w-5 mt-1 rounded-full border-2 border-gray-400 dark:border-gray-500 flex items-center justify-center peer-checked:border-blue-600 peer-checked:bg-blue-600 transition duration-300 ease-in-out">
                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-transform duration-300 transform scale-75 peer-checked:scale-100 rotate-0 peer-checked:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="text-gray-800 dark:text-gray-100 font-medium break-words">
                    {{ ucwords(str_replace('_', ' ', $permissionName)) }}
                </span>
            </label>


        @endforeach
    </div>



</div>

<!--
<label
    class="flex items-start gap-3 p-4 border rounded-lg transition-shadow cursor-pointer
                {{ $isChecked
                    ? 'bg-blue-50 border-blue-400 shadow-md dark:bg-blue-900/20 dark:border-blue-500'
                    : 'bg-white border-gray-300 shadow-sm hover:shadow-md dark:bg-gray-800 dark:border-gray-600 dark:hover:shadow-md' }}"
>
    <input
        type="checkbox"
        value="{{ $permissionName }}"
        wire:model="selectedPermissions"
        wire:change="syncPermissions"
        class="form-checkbox h-5 w-5 text-blue-600 dark:text-blue-400 transition duration-150 ease-in-out mt-1 border rounded-lg"
    >
    <span class="text-gray-800 dark:text-gray-100 font-medium break-words">
                    {{ ucwords(str_replace('_', ' ', $permissionName)) }}
                </span>
</label>
-->

