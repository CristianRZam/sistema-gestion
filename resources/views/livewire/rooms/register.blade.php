<flux:modal name="register-room" :show="$errors->isNotEmpty()" focusable class="w-full max-w-3xl">
    <form wire:submit="guardar" class="space-y-6">
        <!-- Título -->
        <div>
            <flux:heading size="lg">
                {{ __('Agregar / Editar Habitación') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Complete los campos para registrar o modificar una habitación.') }}
            </flux:subheading>
        </div>

        <!-- Nùmero y tipo -->
        <div class="flex gap-4">
            <div class="w-1/2">
                <flux:input
                    wire:model.defer="numero"
                    :label="__('Nº Habitación')"
                    type="text"
                    required
                />
            </div>
            <div class="w-1/2">
                <flux:select
                    wire:model.defer="tipo"
                    :label="__('Tipo de Habitación')"
                >
                    <option value="">{{ __('Seleccione un tipo') }}</option>
                    @foreach($tipos as $id => $nombre)
                        <option value="{{ $id }}">{{ ucfirst($nombre) }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <!-- Nùmero y tipo -->
        <div class="flex gap-4">
            <div class="w-1/2">
                <flux:select
                    wire:model.defer="piso"
                    :label="__('Piso')"
                    required
                >
                    <option value="">{{ __('Seleccione un piso') }}</option>
                    @foreach($pisos as $id => $nombre)
                        <option value="{{ $id }}">{{ ucfirst($nombre) }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-1/2">
                <flux:input
                    wire:model.defer="capacidad"
                    :label="__('Capacidad')"
                    type="number"
                    min="1"
                    step="1"
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
                <flux:input
                    wire:model.defer="precioPromocion"
                    :label="__('Precio promoción (S/)')"
                    type="number"
                    min="0"
                    step="0.01"
                />
            </div>
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
    if (!window._cerrarModalRoomRegistrado) {
        window._cerrarModalRoomRegistrado = true;

        window.addEventListener('cerrarModalRoom', () => {
            Flux.modal('register-room').close();
            toastr.success('Registro guardado.');
        });
    }



    if (!window._abrirModalRoom) {
        window._abrirModalRoom = true;

        window.addEventListener('abrirModalRoom', () => {
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
