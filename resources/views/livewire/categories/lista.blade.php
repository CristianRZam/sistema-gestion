<section class="w-full">
    @include('partials.category-heading')

    <!-- Botón para agregar nueva categoría alineado a la derecha -->
    <div class="mb-4 text-right">
        <a href="#" class="border border-green-500 text-green-500 px-4 py-2 rounded hover:bg-green-500 hover:text-white mr-2">
            Exportar PDF
        </a>
        <a href="#" class="border border-gray-500 text-gray-500 px-4 py-2 rounded hover:bg-gray-500 hover:text-white mr-2">
            Exportar Excel
        </a>
        <a href="#" class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-500 hover:text-white">
            Agregar
        </a>
    </div>


    @if ($categorias->isEmpty())
        <p>No hay parámetros de tipo CATEGORIA.</p>
    @else
        <table class="w-full table-auto border-collapse">
            <thead>
            <tr>
                <th class="border p-2">#</th> <!-- Contador -->
                <th class="border p-2">Nombre</th>
                <th class="border p-2">Nombre Corto</th>
                <th class="border p-2">Orden</th>
                <th class="border p-2">Acciones</th> <!-- Botones de Acciones -->
            </tr>
            </thead>
            <tbody>
            @foreach($categorias as $index => $categoria)
                <tr>
                    <td class="border p-2">{{ $index + 1 }}</td> <!-- Contador -->
                    <td class="border p-2">{{ $categoria->nombre }}</td>
                    <td class="border p-2">{{ $categoria->nombreCorto }}</td>
                    <td class="border p-2 text-center">{{ $categoria->orden }}</td> <!-- Centrando el orden -->
                    <td class="border p-2 text-center">
                        <!-- Botones para Editar y Eliminar -->
                        <a href="#" class="border border-yellow-500 text-yellow-500 px-4 py-2 rounded hover:bg-yellow-500 hover:text-white mr-2">
                            Editar
                        </a>
                        <button type="submit" class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-500 hover:text-white">
                            Eliminar
                        </button>
                    </td> <!-- Centrando los botones -->
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</section>

