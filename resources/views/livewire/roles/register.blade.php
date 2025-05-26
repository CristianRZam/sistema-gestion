<flux:modal name="register-role" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form wire:submit="guardarRole" class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ __('Agregar / Editar Rol') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Complete los siguientes campos para registrar un nuevo rol.') }}
            </flux:subheading>
        </div>

        <flux:input
            wire:model.defer="nombre"
            :label="__('Nombre')"
            type="text"
            required
        />

        <div class="flex justify-end space-x-2">
            <flux:modal.close>
                <flux:button class="cursor-pointer" variant="filled">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>

            <flux:button class="cursor-pointer" type="submit" variant="primary">
                {{ __('Guardar') }}
            </flux:button>
        </div>
    </form>
</flux:modal>


<script>
    window.addEventListener('cerrarModalRole', () => {
        Flux.modal('register-role').close();
    });
</script>
