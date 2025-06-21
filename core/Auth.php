<?php

namespace Core;

use App\Models\User; // <-- CORRIGIDO para usar o Model 'User'

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $userModel = new User(); // <-- CORRIGIDO
        $user = $userModel->findByEmail($email);

        // Usa a coluna 'password' do seu banco de dados
        if ($user && $user['ativo'] && password_verify($password, $user['password'])) { 
            self::setUserSession($user);
            return true;
        }

        return false;
    }

    private static function setUserSession(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_role_id'] = $user['role_id'];
    }
    
    public static function logout(): void {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array {
        if (self::check()) {
            return [
                'id' => $_SESSION['user_id'],
                'nome' => $_SESSION['user_nome'],
                'role_id' => $_SESSION['user_role_id']
            ];
        }
        return null;
    }

    public static function isAdmin(): bool {
        $user = self::user();
        return $user && $user['role_id'] == 1;
    }
}
