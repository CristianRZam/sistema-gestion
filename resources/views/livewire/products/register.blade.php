<flux:modal name="register-product" :show="$errors->isNotEmpty()" focusable class="w-full max-w-3xl">
    <form wire:submit="guardarProducto" class="space-y-6">
        <!-- Título -->
        <div>
            <flux:heading size="lg">
                {{ __('Agregar / Editar Producto') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Complete los campos para registrar o modificar un producto.') }}
            </flux:subheading>
        </div>

        <!-- Código y Nombre -->
        <div class="flex gap-4">
            <div class="w-1/2">
                <flux:input
                    wire:model.defer="codigo"
                    :label="__('Código')"
                    type="text"
                    required
                />
            </div>
            <div class="w-1/2">
                <flux:input
                    wire:model.defer="nombre"
                    :label="__('Nombre')"
                    type="text"
                    required
                />
            </div>
        </div>

        <!-- Descripción (100%) -->
        <flux:textarea
            wire:model.defer="descripcion"
            :label="__('Descripción')"
            rows="5"
            required
        />

        <!-- Precio y Stock -->
        <div class="flex gap-4">
            <div class="w-1/2">
                <flux:input
                    wire:model.defer="precio"
                    :label="__('Precio (S/)')"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                />
            </div>
            <div class="w-1/2">
                <flux:select
                    wire:model.defer="categoria"
                    :label="__('Categoría')"
                    required
                >
                    <option value="">{{ __('Seleccione una categoría') }}</option>
                    @foreach($categoriasDisponibles as $id => $nombre)
                        <option value="{{ $id }}">{{ ucfirst($nombre) }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>


        <!-- Imagen del producto (100%) -->
        <div x-data="{ abrirFile() { $refs.inputImagen.click(); } }" class="space-y-1">
            <flux:label for="imagen">
                {{ __('Imagen del producto') }}
            </flux:label>
            <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">
                Formatos permitidos: <strong>JPEG, JPG, PNG, WEBP</strong>. Tamaño máximo: <strong>2MB</strong>.
            </span>

            <div
                class="h-32 w-full rounded cursor-pointer overflow-hidden border transition hover:shadow
           border-gray-300 dark:border-gray-600
           mt-2 flex items-center justify-center"
                @click="abrirFile"
            >
                @if ($this->imagenPreviewUrl)
                    <img src="{{ $this->imagenPreviewUrl }}" alt="Previsualización"
                         class="block object-contain h-full w-full" />
                @elseif ($imagenActualUrl)
                    <img src="{{ $imagenActualUrl }}" alt="Imagen actual"
                         class="block object-contain h-full w-full" />
                @elseif ($imagen)
                    <div class="text-red-500 text-sm text-center px-4">
                        El archivo seleccionado no es una imagen válida (jpg, png, webp).
                    </div>
                @else
                    <div class="text-gray-400 dark:text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 7v10a4 4 0 004 4h10a4 4 0 004-4V7M3 7l9 6 9-6" />
                        </svg>
                    </div>
                @endif
            </div>


            <input
                x-ref="inputImagen"
                id="imagen"
                type="file"
                wire:model="imagen"
                accept="image/*"
                class="hidden"
            />

            @error('imagen')
            <p class="text-sm text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-2 pt-4">
            <flux:modal.close>
                <flux:button class="cursor-pointer" variant="filled">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="primary" class="cursor-pointer">
                {{ __('Guardar') }}
            </flux:button>
        </div>
    </form>
</flux:modal>

<!-- Script para cerrar modal -->
<script>
    if (!window._cerrarModalProductRegistrado) {
        window._cerrarModalProductRegistrado = true;

        window.addEventListener('cerrarModalProduct', () => {
            Flux.modal('register-product').close();
            toastr.success('Registro guardado.');
        });
    }


    if (!window._abrirModalScaneoRegistrado) {
        window._abrirModalScaneoRegistrado = true;

        window.addEventListener('abrirModalScaneo', () => {
            setTimeout(() => {
                Flux.modal('register-product').show();
            }, 100);
        });
    }
</script>


