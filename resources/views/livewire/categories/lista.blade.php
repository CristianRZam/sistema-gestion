<section class="w-full">
    <!-- Cabecera de vista -->
    @include('partials.category-heading')

    <!-- Bototones alineados a la derecha -->
    <div class="mb-4 text-right">
        <a href="#" class="border border-green-500 text-green-500 px-4 py-2 rounded hover:bg-green-500 hover:text-white mr-2">
            Exportar PDF
        </a>
        <a href="#" class="border border-gray-500 text-gray-500 px-4 py-2 rounded hover:bg-gray-500 hover:text-white mr-2">
            Exportar Excel
        </a>
        <!-- Botón Agregar -->
        <flux:modal.trigger name="register-category">
            <button
                id="abrirCategoriaBtn"
                class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-500 hover:text-white cursor-pointer"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'register-category')">
                {{ __('Agregar') }}
            </button>
        </flux:modal.trigger>

    </div>


    <!-- Tabla de categorias -->
    @if ($categorias->isEmpty())
        <p>No hay parámetros de tipo CATEGORIA.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">Nº</th>
                <th class="border p-2">Nombre</th>
                <th class="border p-2">Nombre Corto</th>
                <th class="border p-2">Orden</th>
                <th class="border p-2">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categorias as $index => $categoria)
                <tr>
                    <td class="border p-2">{{ $index + 1 }}</td>
                    <td class="border p-2">{{ $categoria->nombre }}</td>
                    <td class="border p-2">{{ $categoria->nombreCorto }}</td>
                    <td class="border p-2 text-center">{{ $categoria->orden }}</td>
                    <td class="border p-2 text-center">
                        <flux:modal.trigger name="register-category">
                            <button
                                id="abrirCategoriaBtn"
                                class="border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-white mr-2"
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'register-category')">
                                {{ __('Editar') }}
                            </button>
                        </flux:modal.trigger>
                        <button type="submit" class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-500 hover:text-white">
                            Eliminar
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
    @livewire('categories.register')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('abrirCategoriaBtn');
            btn.addEventListener('click', function () {
                Livewire.dispatch('abrir-modal-categoria');
                console.log("jajajaja")
            });
        });
    </script>

</section>

