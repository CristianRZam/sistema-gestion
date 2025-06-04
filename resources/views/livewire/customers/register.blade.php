<flux:modal name="register-customer" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form wire:submit="guardarCustomer" class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ __('Agregar / Editar Cliente') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Complete los siguientes campos para registrar un nuevo cliente.') }}
            </flux:subheading>
        </div>

        <flux:input
            wire:model.defer="documento"
            :label="__('Documento (DNI o RUC)')"
            type="text"
            required
            wire:keydown.enter="buscarClientePorDocumento"
        >
            @if($modo === 'venta')
                <x-slot name="iconTrailing">
                    <flux:button
                        size="sm"
                        variant="subtle"
                        icon="magnifying-glass"
                        wire:click="buscarClientePorDocumento"
                        title="Buscar"
                        class="-mr-1"
                    />
                </x-slot>
            @endif
        </flux:input>


        <flux:input
            wire:model.defer="nombre"
            :label="__('Nombre')"
            type="text"
            required
        />

        <flux:input
            wire:model.defer="telefono"
            :label="__('Teléfono')"
            type="text"
            required="false"
        />

        <flux:input
            wire:model.defer="email"
            :label="__('Correo electrónico')"
            type="email"
            required="false"
        />

        <flux:input
            wire:model.defer="direccion"
            :label="__('Dirección')"
            type="text"
            required="false"
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
    window.addEventListener('cerrarModalCustomer', () => {
        Flux.modal('register-customer').close();
    });
</script>
