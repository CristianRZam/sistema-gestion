<flux:modal name="register-user" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form wire:submit="guardarUser" class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ __('Agregar / Editar Usuario') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Complete los siguientes campos para registrar un nuevo usuario.') }}
            </flux:subheading>
        </div>

        <flux:input
            wire:model.defer="nombre"
            :label="__('Nombre')"
            type="text"
            required
        />

        <flux:input
            wire:model.defer="email"
            :label="__('Correo Electrónico')"
            type="email"
            required
        />

        <flux:select
            wire:model.defer="role"
            :label="__('Perfil de Usuario')"
            required
        >
            <option value="">{{ __('Seleccione un perfil') }}</option>
            @foreach($rolesDisponibles as $rol)
                <option value="{{ $rol }}">{{ ucfirst($rol) }}</option>
            @endforeach
        </flux:select>


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
    window.addEventListener('cerrarModalUser', () => {
        Flux.modal('register-user').close();
    });
</script>
