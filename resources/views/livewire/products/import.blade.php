<flux:modal name="import-product" :show="$errors->isNotEmpty()" focusable class="w-full max-w-lg">
    <form wire:submit.prevent="guardarProducto" class="space-y-8 p-6" enctype="multipart/form-data">
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
            <flux:button variant="primary" class="cursor-pointer" onclick="descargarPlantillaExcel()">
                {{ __('Descargar plantilla Excel') }}
            </flux:button>
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
            @error('excelFile')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Archivo ZIP de imágenes -->
        <div class="space-y-1">
            <label for="imagenesZip" class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ __('Archivo ZIP de imágenes') }}
            </label>
            <input
                id="imagenesZip"
                wire:model="imagenesZip"
                type="file"
                accept=".zip"
                class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-secondary-50 file:text-secondary-700 hover:file:bg-secondary-100 transition"
            />
            @error('imagenesZip')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Indicador de carga -->
        <div wire:loading wire:target="excelFile,imagenesZip" class="text-sm text-yellow-600">
            Subiendo archivos, por favor espera...
        </div>

        <!-- Acciones -->
        <div class="flex justify-end pt-4 space-x-3">
            <flux:modal.close>
                <flux:button variant="filled" class="cursor-pointer">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>

            <flux:button
                type="submit"
                variant="primary"
                class="cursor-pointer"
                wire:loading.attr="disabled"
                wire:target="excelFile,imagenesZip,guardarProducto"
            >
                {{ __('Iniciar importación') }}
            </flux:button>
        </div>
    </form>
</flux:modal>

<script>
    if (!window._importacionProductoEventosRegistrados) {
        window._importacionProductoEventosRegistrados = true;

        window.addEventListener('cerrarModalProductImport', () => {
            Flux.modal('import-product').close();
        });

        window.addEventListener('mostrarExitoImportacion', event => {
            toastr.success(event.detail.mensaje || 'Importación completada.');
        });

        window.addEventListener('mostrarErrorImportacion', event => {
            const errores = event.detail;

            // Asegúrate de que sea un array y tenga al menos un elemento
            if (Array.isArray(errores) && errores.length > 0 && errores[0].mensaje) {
                toastr.error(errores[0].mensaje, 'Error al importar');
            } else {
                toastr.error('Error al importar productos.', 'Error');
            }
        });

    }

    window.addEventListener('abrirModalProductImport', () => {
        document.getElementById('excelFile').value = '';
        document.getElementById('imagenesZip').value = '';
    });

    function descargarPlantillaExcel() {
        const url = "{{ route('parametros.descargar', ['codigoParametro' => 'PLANTILLA_PRODUCTO']) }}";

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.mensaje || 'Error al descargar la plantilla.');
                    });
                }
                return response.blob();
            })
            .then(blob => {
                const a = document.createElement('a');
                const objectUrl = URL.createObjectURL(blob);
                a.href = objectUrl;
                a.download = 'plantilla-producto.xlsx';
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(objectUrl);
            })
            .catch(error => {
                toastr.error(error.message);
            });
    }
</script>

