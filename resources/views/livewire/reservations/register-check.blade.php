<flux:modal name="register-check" focusable class="w-full max-w-xl">
    <form wire:submit.prevent="guardar" class="space-y-6">
        <!-- Título -->
        <div>
            <flux:heading size="lg">
                {{ $tipoCheck === 'check-in' ? 'Registro de Check-in' : 'Registro de Check-out' }}
            </flux:heading>
            <flux:subheading>
                {{ $yaRegistrado ? 'Este check ya ha sido registrado y no puede modificarse.' : 'Seleccione la fecha para el registro.' }}
            </flux:subheading>
        </div>

        <!-- Fecha -->
        <flux:input
            wire:model.defer="fecha"
            :label="__('Fecha')"
            :type="'datetime-local'"
            :disabled="$yaRegistrado"
            required
        />

        <!-- Botones -->
        <div class="flex justify-end space-x-2 pt-4">
            <flux:modal.close>
                <flux:button class="cursor-pointer" variant="filled">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>

            @unless($yaRegistrado)
                <flux:button type="submit" variant="primary" class="cursor-pointer">
                    {{ __('Guardar') }}
                </flux:button>
            @endunless
        </div>
    </form>
</flux:modal>

<script>
    if (!window._cerrarModalCheck) {
        window._cerrarModalCheck = true;
        window.addEventListener('cerrarModalCheck', () => {
            Flux.modal('register-check').close();
            toastr.success('Registro guardado.');
        });
    }
</script>
