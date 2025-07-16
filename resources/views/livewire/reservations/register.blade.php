<div class="w-full">
    <!-- Cabecera de vista -->
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Finalizar Reserva') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('Completa los datos restantes para registrar una reserva válida. Aquí podrás asignar cliente, revisar habitaciones, establecer fechas, añadir notas y registrar los pagos.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <!-- Información del cliente -->
    <div class="relative flex border border-gray-300 dark:border-gray-700 border-l-0 rounded-lg bg-white dark:bg-zinc-900 overflow-hidden">
        <!-- Botón de editar con ícono -->
        <div class="absolute top-2 right-2 flex items-center gap-3">
            {{-- Botón Editar Cliente --}}
            <flux:modal.trigger name="register-customer">
                <button
                    class="text-blue-600 hover:text-blue-800 transition cursor-pointer"
                    x-on:click.prevent="$dispatch('open-modal-customer', { id: null, modo: 'venta' })"
                    aria-label="{{ __('Editar cliente') }}"
                >
                    <i class="fa-solid fa-pencil"></i>
                </button>
            </flux:modal.trigger>

            {{-- Botón Eliminar Cliente --}}
            <button
                wire:click="eliminarClienteSeleccionado"
                class="text-red-600 hover:text-red-800 transition cursor-pointer"
                aria-label="{{ __('Eliminar cliente') }}"
            >
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>


        <!-- Franja izquierda intercalada, espaciada y con altura completa -->
        <div class="w-1 flex flex-col justify-between py-0">
            <div class="h-4 bg-red-500"></div>
            <div class="h-4 bg-cyan-500"></div>
            <div class="h-4 bg-red-500"></div>
            <div class="h-4 bg-cyan-500"></div>
        </div>

        <!-- Contenido informativo del cliente -->
        <div class="flex-1 p-4">
            <p class="text-gray-700 dark:text-gray-300">
                <strong>{{ __('Nombre:') }}</strong> {{ $cliente_seleccionado['nombre'] ?? __('No seleccionado') }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
                <strong>{{ __('Documento:') }}</strong> {{ $cliente_seleccionado['dni'] ?? '---' }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
                <strong>{{ __('Dirección:') }}</strong> {{ $cliente_seleccionado['direccion'] ?? '---' }}
            </p>
        </div>
    </div>
    @livewire('customers.register')


    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Habitaciones seleccionadas</h2>

        <div class="overflow-auto rounded-md border border-gray-200 dark:border-gray-700">
            <table class="min-w-full table-auto border-collapse text-sm">
                <thead>
                <tr>
                    <th class="border p-2">#</th>
                    <th class="border p-2">Habitación</th>
                    <th class="border p-2">Fecha inicio</th>
                    <th class="border p-2">Fecha fin</th>
                    <th class="border p-2">Personas</th>
                    <th class="border p-2">Precio x día</th>
                    <th class="border p-2">Subtotal</th>
                    <th class="border p-2">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @php $total = 0; @endphp

                @foreach($detallesHabitaciones as $index => $detalle)
                    @php
                        $subtotal = $detalle['subtotal'] ?? 0;
                        $total += $subtotal;
                    @endphp

                        <!-- Fila principal -->
                    <tr>
                        <td class="border p-2 text-center">{{ $index + 1 }}</td>
                        <td class="border p-2 text-center">{{ $detalle['room']['numero'] ?? '---' }}</td>

                        @php
                            $fechaInicio = \Carbon\Carbon::parse($detalle['fecha_inicio']);
                            $hoy = \Carbon\Carbon::today();
                            $deshabilitarFechaInicio = $fechaInicio->lt($hoy);
                        @endphp

                        <td class="border p-2 text-center">
                            <input
                                type="date"
                                min="{{ date('Y-m-d') }}"
                                wire:model.live="detallesHabitaciones.{{ $index }}.fecha_inicio"
                                class="form-input w-full text-sm"
                                @if($deshabilitarFechaInicio) disabled @endif
                            />
                        </td>


                        <td class="border p-2 text-center">
                            <input
                                type="date"
                                min="{{ date('Y-m-d') }}"
                                wire:model.live="detallesHabitaciones.{{ $index }}.fecha_fin"
                                class="form-input w-full text-sm"
                            />
                        </td>

                        <td class="border p-2 text-center">
                            <input type="number" min="1"
                                   wire:model.defer="detallesHabitaciones.{{ $index }}.cantidad_personas"
                                   class="form-input w-16 text-sm text-center" />
                        </td>

                        <td class="border p-2 text-center">
                            S/ {{ number_format($detalle['precio'] ?? 0, 2) }}
                        </td>

                        <td class="border p-2 text-center font-semibold text-green-700 dark:text-green-400">
                            S/ {{ number_format($subtotal, 2) }}
                        </td>

                        <td class="border p-2 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="flex gap-3">
                                    <!-- Botón guardar -->
                                    <flux:modal.trigger name="confirm-update-detail-{{ $detalle['id'] }}">
                                        <button wire:click.prevent="abrirConfirmacionDetalle({{ $detalle['id'] }})" class="btn-nuevo-table" title="Guardar cambios">
                                            <i class="fa-regular fa-floppy-disk text-xl"></i>
                                        </button>
                                    </flux:modal.trigger>

                                    <!-- Botón eliminar -->
                                    <flux:modal.trigger name="confirm-delete-detail-{{ $detalle['id'] }}">
                                        <button wire:click.prevent="abrirConfirmacionEliminacion({{ $detalle['id'] }})" class="btn-delete-table" title="Eliminar fila">
                                            <i class="fa-regular fa-trash-can text-xl"></i>
                                        </button>
                                    </flux:modal.trigger>
                                </div>

                                <div class="flex gap-2 mt-1">
                                    <!-- Botón check-in -->
                                    <flux:modal.trigger name="register-check">
                                        <button
                                            x-on:click.prevent="$dispatch('open-modal-check', { id: {{ $detalle['id'] }}, tipo: 'check-in' })"
                                            class="text-xs bg-green-100 hover:bg-green-200 text-green-700 px-2 py-1 rounded cursor-pointer"
                                        >
                                            <i class="fa-solid fa-door-open mr-1"></i> Check-in
                                        </button>
                                    </flux:modal.trigger>

                                    <!-- Botón check-out -->
                                    <flux:modal.trigger name="register-check">
                                        <button
                                            x-on:click.prevent="$dispatch('open-modal-check', { id: {{ $detalle['id'] }}, tipo: 'check-out' })"
                                            class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded cursor-pointer"
                                        >
                                            <i class="fa-solid fa-door-closed mr-1"></i> Check-out
                                        </button>
                                    </flux:modal.trigger>
                                </div>
                            </div>

                            <!-- Modal confirmar guardar -->
                            <flux:modal name="confirm-update-detail-{{ $detalle['id'] }}" focusable class="max-w-lg">
                                <form wire:submit.prevent="guardarDetalle" class="space-y-6">
                                    <div>
                                        <flux:heading size="lg">{{ __('¿Deseas modificar los detalles de esta reserva?') }}</flux:heading>
                                        <flux:subheading>
                                            {{ __('Esta acción actualizará la información de la habitación seleccionada. ¿Estás seguro de continuar?') }}
                                        </flux:subheading>
                                    </div>
                                    <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                                        <flux:modal.close>
                                            <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                                        </flux:modal.close>
                                        <flux:modal.close>
                                            <flux:button variant="danger" type="submit">{{ __('Continuar') }}</flux:button>
                                        </flux:modal.close>
                                    </div>
                                </form>
                            </flux:modal>

                            <!-- Modal confirmar eliminar -->
                            <flux:modal name="confirm-delete-detail-{{ $detalle['id'] }}" focusable class="max-w-lg">
                                <form wire:submit.prevent="confirmarEliminarDetalle" class="space-y-6">
                                    <div>
                                        <flux:heading size="lg">{{ __('¿Deseas eliminar este detalle?') }}</flux:heading>
                                        <flux:subheading>
                                            {{ __('Esta acción no se puede deshacer. ¿Estás seguro de que quieres eliminar este detalle de la reserva?') }}
                                        </flux:subheading>
                                    </div>
                                    <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                                        <flux:modal.close>
                                            <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                                        </flux:modal.close>
                                        <flux:modal.close>
                                            <flux:button variant="danger" type="submit">{{ __('Eliminar') }}</flux:button>
                                        </flux:modal.close>
                                    </div>
                                </form>
                            </flux:modal>
                        </td>
                    </tr>

                    <!-- Fila expandible -->
                    <tr>
                        <td colspan="8" class="border px-4 py-3 bg-gray-50 dark:bg-zinc-900">
                            <div class="flex justify-between items-center mb-3">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Detalles de habitación {{ $detalle['room']['numero'] ?? '---' }}
                            </span>
                                <button wire:click="toggleServicios({{ $detalle['id'] }})" class="btn-nuevo">
                                    <i class="fa-solid fa-chevron-{{ $mostrarServicios[$detalle['id']] ?? false ? 'up' : 'down' }} mr-1"></i>
                                    {{ $mostrarServicios[$detalle['id']] ?? false ? 'Ocultar servicios y productos' : 'Ver servicios y productos' }}
                                </button>
                            </div>

                            @if($mostrarServicios[$detalle['id']] ?? false)
                                <div class="space-y-4">
                                    <!-- Servicios -->
                                    <div>
                                        <h4 class="font-semibold text-gray-600 dark:text-gray-400 mb-1">Servicios:</h4>
                                        <ul class="list-disc ml-5 text-sm text-gray-700 dark:text-gray-300">
                                            @forelse($detalle['servicios'] ?? [] as $servicio)
                                                <li>{{ $servicio['nombre'] }} - S/ {{ number_format($servicio['precio'], 2) }}</li>
                                            @empty
                                                <li>No hay servicios registrados</li>
                                            @endforelse
                                        </ul>
                                    </div>

                                    <!-- Productos -->
                                    <div>
                                        <h4 class="font-semibold text-gray-600 dark:text-gray-400 mb-1">Productos:</h4>
                                        <ul class="list-disc ml-5 text-sm text-gray-700 dark:text-gray-300">
                                            @forelse($detalle['productos'] ?? [] as $producto)
                                                <li>{{ $producto['nombre'] }} (x{{ $producto['cantidad'] }}) - S/ {{ number_format($producto['total'], 2) }}</li>
                                            @empty
                                                <li>No hay productos registrados</li>
                                            @endforelse
                                        </ul>
                                    </div>

                                    <div class="flex justify-end gap-3">
                                        <button wire:click="agregarServicio({{ $detalle['id'] }})" class="btn-nuevo">
                                            <i class="fa-solid fa-plus-circle mr-1"></i> Agregar servicio
                                        </button>
                                        <button wire:click="agregarProducto({{ $detalle['id'] }})" class="btn-nuevo">
                                            <i class="fa-solid fa-box mr-1"></i> Agregar producto
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach

                @if(empty($detallesHabitaciones))
                    <tr>
                        <td colspan="8" class="border p-2 text-center">No hay habitaciones registradas en esta reserva.</td>
                    </tr>
                @endif
                </tbody>

                @if(!empty($detallesHabitaciones))
                    <tfoot class="bg-gray-100 dark:bg-zinc-800 font-semibold">
                    <tr>
                        <td colspan="6" class="border p-2 text-center">Total:</td>
                        <td class="border p-2 text-center text-green-700 dark:text-green-400">
                            S/ {{ number_format($total, 2) }}
                        </td>
                        <td class="border p-2 text-center"></td>
                    </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @livewire('reservations.register-check')

</div>
<script>
    if (!window._errorRegisterReservation) {
        window._errorRegisterReservation = true;

        window.addEventListener('errorRegisterReservation', (event) => {
            toastr.error(event.detail[0].mensaje);
        });
    }

    if (!window._successRegisterReservation) {
        window._successRegisterReservation = true;

        window.addEventListener('successRegisterReservation', (event) => {
            toastr.success(event.detail[0].mensaje);
        });
    }
</script>
