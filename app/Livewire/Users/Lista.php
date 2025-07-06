<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Lista extends Component
{
    use WithPagination;

    public $usuarioIdModal;
    public $estadoUsuarioModal;

    protected $paginationTheme = 'tailwind'; // O bootstrap si usas Bootstrap

    // Filtros
    public $nombreFiltro = '';
    public $rolFiltro = [];
    protected $listeners = [
        'actualiza-lista-usuario' => '$refresh',
        'open-modal-user-disable' => 'abrirModalCambioEstado',
        'filtrosActualizados' => 'actualizarFiltros',
    ];

    public function actualizarFiltros($filtros)
    {
        $this->nombreFiltro = $filtros['nombre'] ?? '';
        $this->rolFiltro = $filtros['rol_ids'] ?? '';
        $this->resetPage(); // Reinicia la paginación al aplicar nuevos filtros
    }
    public function abrirModalCambioEstado($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            session()->flash('error', 'Usuario no encontrado.');
            return;
        }

        $this->usuarioIdModal = $usuario->id;
        $this->estadoUsuarioModal = $usuario->activo;
    }



    public function confirmarCambioEstado()
    {
        $usuario = User::find($this->usuarioIdModal);

        if (!$usuario) {
            session()->flash('error', 'Usuario no encontrado.');
            return;
        }

        $usuario->activo = !$usuario->activo;
        $usuario->save();

        $this->dispatch('actualiza-lista-usuario');
        $this->reset(['usuarioIdModal', 'estadoUsuarioModal']);
        $this->dispatch('cerrarModalUserDisable');
    }



    public function render()
    {
        $query = User::query()->with('roles');

        // Filtro por nombre
        if (!empty($this->nombreFiltro)) {
            $query->where('name', 'like', '%' . $this->nombreFiltro . '%');
        }

        // Filtro por roles
        if (!empty($this->rolFiltro)) {
            $query->whereHas('roles', function ($q) {
                $q->whereIn('id', $this->rolFiltro);
            });
        }

        return view('livewire.users.lista', [
            'usuarios' => $query->paginate(10),
        ]);
    }

}
