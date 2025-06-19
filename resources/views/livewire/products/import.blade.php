<flux:modal name="import-product" :show="$errors->isNotEmpty()" focusable class="w-full max-w-lg">
    <form wire:submit="guardarProducto" class="space-y-8 p-6" enctype="multipart/form-data">
        <!-- Encabezado -->
        <div class="space-y-2">
            <flux:heading size="lg">
                {{ __('Importar productos') }}
            </flux:heading>
            <flux:subheading>
                {{ __('Selecciona un archivo Excel con los datos y una carpeta con las imágenes.') }}
            </flux:subheading>
        </div>

        <!-- Botón para descargar plantilla -->
        <div>
            <a href="{{ route('parametros.descargar', ['codigoParametro' => 'PLANTILLA_PRODUCTO']) }}">
                <flux:button variant="primary" class="cursor-pointer" >
                    {{ __('Descargar plantilla Excel') }}
                </flux:button>
            </a>
        </div>


        <!-- Archivo Excel -->
        <div class="space-y-1">
            <label for="excelFile" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ __('Archivo Excel de productos') }}
            </label>
            <input
                id="excelFile"
                wire:model="excelFile"
                type="file"
                accept=".xlsx,.xls"
                class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition"
            />
        </div>

        <!-- Carpeta de imágenes -->
        <div class="space-y-1">
            <label for="imagenes" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ __('Carpeta de imágenes') }}
            </label>
            <input
                id="imagenes"
                wire:model="imagenes"
                type="file"
                webkitdirectory
                directory
                multiple
                class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-secondary-50 file:text-secondary-700 hover:file:bg-secondary-100 transition"
            />

            @error('imagenes')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

        </div>

        <!-- Acciones -->
        <div class="flex justify-end pt-4 space-x-3">
            <flux:modal.close>
                <flux:button variant="filled" class="cursor-pointer">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="primary" class="cursor-pointer">
                {{ __('Iniciar importación') }}
            </flux:button>
        </div>
    </form>
</flux:modal>

<!-- Script para cerrar el modal desde Livewire -->
<script>
    window.addEventListener('cerrarModalProductImport', () => {
        Flux.modal('import-product').close();
        toastr.success('Productos importados.');
    });
</script>
