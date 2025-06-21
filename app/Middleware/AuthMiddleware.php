<?php

namespace App\Middleware;

use Core\Auth;

class AuthMiddleware
{
    /**
     * Lida com a requisição. Se o usuário não estiver logado, redireciona.
     */
    public function handle(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit();
        }
    }
}
