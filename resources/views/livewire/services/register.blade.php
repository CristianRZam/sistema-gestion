<flux:modal name="register-service" :show="$errors->isNotEmpty()" focusable class="w-full max-w-3xl">
    <form wire:submit="guardar" class="space-y-6">
        <!-- Título -->
        <div>
            <flux:heading size="lg">
                {{ __('Agregar / Editar Servicio') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Complete los campos para registrar o modificar un servicio.') }}
            </flux:subheading>
        </div>

        <!-- Nùmero y tipo -->
        <div class="flex gap-4">
            <div class="w-1/2">
                <flux:input
                    wire:model.defer="nombre"
                    :label="__('Nombre')"
                    type="text"
                    required
                />
            </div>
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
        </div>

        <!-- Descripción (100%) -->
        <div wire:ignore class="w-full">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('Descripción') }}
            </label>
            <textarea id="descripcion" class="w-full border rounded px-3 py-2 dark:bg-zinc-800 dark:text-white"
                      x-ref="summernote">{{ $descripcion }}</textarea>
        </div>

        <!-- Imagen del producto (100%) -->
        <div x-data="{ abrirFile() { $refs.inputImagen.click(); } }" class="space-y-1">
            <flux:label for="imagen">
                {{ __('Imagen del servicio') }}
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
    if (!window._cerrarModalServiceRegistrado) {
        window._cerrarModalServiceRegistrado = true;

        window.addEventListener('cerrarModalService', () => {
            Flux.modal('register-service').close();
            toastr.success('Registro guardado.');
        });
    }



    if (!window._abrirModalService) {
        window._abrirModalService = true;

        window.addEventListener('abrirModalService', () => {
            $('#descripcion').summernote('destroy');
            $('#descripcion').summernote({
                placeholder: 'Descripción',
                tabsize: 2,
                height: 100,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['insert', ['link', 'picture']],
                    ['view', ['codeview']]
                ]
            });

            $('#descripcion').on('summernote.change', function(we, contents, $editable) {
                Livewire.dispatch('setDescripcionSummernote', { data: contents });
            });
        });
    }


    if (!window._inicializarDescripcion) {
        window._inicializarDescripcion = true;

        window.addEventListener('inicializarDescripcion', event => {
            setTimeout(() => {
                $('#descripcion').summernote('code', event.detail[0].descripcion || '');
            }, 100);
        });
    }
</script>
