<?php

namespace App\Controllers;

use Core\Auth;
use Core\Controller;

class AuthController extends Controller
{
    /**
     * Mostra o formulário de login.
     */
    public function showLoginForm(): void
    {
        if (Auth::check()) {
            header('Location: /admin');
            exit();
        }
        $this->render('auth/login.twig', ['titulo' => 'Login']);
    }

    /**
     * Processa a tentativa de login.
     */
    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (Auth::attempt($email, $senha)) {
            // Se o login for bem-sucedido, redireciona para o dashboard
            header('Location: /admin');
            exit();
        }

        // Se falhar, volta para o login
        header('Location: /login');
        exit();
    }

    /**
     * Faz o logout do usuário.
     */
    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit();
    }
}
