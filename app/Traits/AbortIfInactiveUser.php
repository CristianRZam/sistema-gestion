<?php

namespace App\Traits;

trait AbortIfInactiveUser
{
    protected function abortIfInactiveUser()
    {
        if (auth()->check() && ! auth()->user()->activo) {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();

            // Esto detiene TODO Livewire y redirige al login:
            $this->redirectRoute('login');
        }
    }
}
