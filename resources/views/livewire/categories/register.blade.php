<flux:modal name="register-category" :show="$show" focusable class="max-w-lg">
    <form wire:submit="guardarCategoria" class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ __('Agregar / Editar Categoría') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Complete los siguientes campos para registrar una nueva categoría.') }}
            </flux:subheading>
        </div>

        <flux:input
            wire:model.defer="nombre"
            :label="__('Nombre')"
            type="text"
            required
        />

        <flux:input
            wire:model.defer="nombreCorto"
            :label="__('Nombre Corto')"
            type="text"
            required
        />

        <flux:input
            wire:model.defer="orden"
            :label="__('Orden')"
            type="number"
            min="1"
            required
        />

        <div class="flex justify-end space-x-2">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="primary">
                {{ __('Guardar') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
