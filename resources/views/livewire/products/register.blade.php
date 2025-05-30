<flux:modal name="register-product" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
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

        <!-- Código -->
        <flux:input
            wire:model.defer="codigo"
            :label="__('Código')"
            type="text"
            required
        />

        <!-- Nombre -->
        <flux:input
            wire:model.defer="nombre"
            :label="__('Nombre')"
            type="text"
            required
        />

        <!-- Descripción (Textarea) -->
        <flux:textarea
            wire:model.defer="descripcion"
            :label="__('Descripción')"
            rows="5"
            required
        />

        <!-- Precio -->
        <flux:input
            wire:model.defer="precio"
            :label="__('Precio (S/)')"
            type="number"
            min="0"
            step="0.01"
            required
        />

        <!-- Stock -->
        <flux:input
            wire:model.defer="stock"
            :label="__('Stock')"
            type="number"
            min="0"
            required
        />

        <!-- Categoría -->
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


        <!-- Botones -->
        <div class="flex justify-end space-x-2 pt-4">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="primary">
                {{ __('Guardar') }}
            </flux:button>
        </div>
    </form>
</flux:modal>

<!-- Cierre modal desde Livewire -->
<script>
    window.addEventListener('cerrarModalProduct', () => {
        Flux.modal('register-product').close();
    });
</script>
