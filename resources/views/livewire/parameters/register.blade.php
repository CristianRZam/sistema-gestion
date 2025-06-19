<flux:modal name="register-parameter" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form wire:submit="guardarParametro" class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ __('Agregar / Editar Parámetro') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Complete los siguientes campos para registrar un nuevo parametro.') }}
            </flux:subheading>
        </div>

        <!-- ✅ Si tipo === 1 (imagen) -->
        @if ($tipo == 1)
            <div x-data="{ abrirFile() { $refs.inputImagen.click(); } }" class="space-y-1">
                <flux:label for="imagen">
                    {{ __('Imagen') }}
                </flux:label>

                <div
                    class="h-32 w-full rounded cursor-pointer overflow-hidden border transition hover:shadow
        border-gray-300 dark:border-gray-600 mt-2 flex items-center justify-center"
                    @click="abrirFile"
                >
                    @if ($imagen)
                        <img src="{{ $imagen->temporaryUrl() }}" alt="Previsualización"
                             class="block object-contain h-full w-full" />
                    @elseif($imagenActualUrl)
                        <img src="{{ $imagenActualUrl }}" alt="Imagen actual"
                             class="block object-contain h-full w-full" />
                    @else
                        <!-- Ícono por defecto -->
                        <div class="text-gray-400 dark:text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 7v10a4 4 0 004 4h10a4 4 0 004-4V7M3 7l9 6 9-6" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Input de archivo oculto -->
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

            <!-- ✅ Si tipo === 4 (archivo) -->
        @elseif ($tipo == 4)
            <div class="space-y-1">
                <flux:label for="archivo">
                    {{ __('Archivo (PDF, DOCX, Excel)') }}
                </flux:label>

                <input
                    id="archivo"
                    type="file"
                    wire:model="imagen"
                    accept=".pdf,.doc,.docx,.xls,.xlsx"
                    class="w-full px-4 py-2 border rounded dark:bg-gray-900 dark:border-gray-700"
                />

                @error('imagen')
                <p class="text-sm text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- ✅ Para los demás tipos, mostrar input de texto -->
        @else
            <flux:input
                wire:model.defer="nombre"
                :label="__('Nombre')"
                type="text"
                required
            />
        @endif



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

        <!-- ✅ Nuevo campo para tipo -->
        <flux:input
            wire:model.defer="codigoParametro"
            :label="__('Código')"
            type="text"
            required
        />

        <flux:select
            wire:model.live="tipo"
            :label="__('Tipo')"
            required
        >
            <option value="">{{ __('Seleccione un tipo') }}</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->idParametro }}">{{ $tipo->nombre }}</option>
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
    window.addEventListener('cerrarModal', () => {
        Flux.modal('register-parameter').close();
        toastr.success('Registro guardado.');
    });
</script>
