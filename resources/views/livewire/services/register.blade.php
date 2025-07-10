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
