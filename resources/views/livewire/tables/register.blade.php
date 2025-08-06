<flux:modal name="register-table" :show="$errors->isNotEmpty()" focusable class="w-full max-w-3xl">
    <form wire:submit="guardar" class="space-y-6">
        <!-- Título -->
        <div>
            <flux:heading size="lg">
                {{ __('Agregar / Editar Mesa') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Complete los campos para registrar o modificar una mesa.') }}
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


        <!-- Precio y Stock -->
        <div class="flex gap-4">
            <div class="w-1/2">
                <flux:select
                    wire:model.defer="piso"
                    :label="__('Piso')"
                    required
                >
                    <option value="">{{ __('Seleccione un piso') }}</option>
                    @foreach($pisosDisponibles as $id => $nombre)
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
                />
            </div>
            <div class="w-1/2">
                <flux:select
                    wire:model.defer="estado"
                    :label="__('Estado')"
                    required
                >
                    <option value="">{{ __('Seleccione un estado') }}</option>
                    <option value="1">Disponible</option>
                    <option value="2">No disponible</option>
                </flux:select>
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
    if (!window._cerrarModalTableRegistrado) {
        window._cerrarModalTableRegistrado = true;

        window.addEventListener('cerrarModalTable', () => {
            Flux.modal('register-table').close();
            toastr.success('Registro guardado.');
        });
    }
</script>
