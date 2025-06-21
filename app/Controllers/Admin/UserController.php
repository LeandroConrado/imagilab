<?php

namespace App\Controllers\Admin;

use App\Models\User; // <-- CORRIGIDO para usar o Model 'User'
use Core\Auth;
use Core\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!Auth::isAdmin()) {
            header('Location: /admin');
            exit();
        }
    }

    public function index(): void
    {
        $this->render('admin/usuarios/index.twig', [
            'titulo' => 'Gerenciamento de Usuários',
            'usuarios' => (new User())->findAll()
        ]);
    }

    public function create(): void
    {
        $this->render('admin/usuarios/create.twig', ['titulo' => 'Novo Usuário']);
    }

    public function store(): void
    {
        $data = [
            'nome' => $_POST['nome'] ?? null,
            'email' => $_POST['email'] ?? null,
            'password' => $_POST['senha'] ?? null, // Recebe do form como 'senha'
            'role_id' => $_POST['role_id'] ?? 2,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];

        if ($data['nome'] && $data['email'] && $data['password']) {
            (new User())->create($data);
        }
        header('Location: /admin/usuarios');
        exit();
    }

    public function edit(int $id): void
    {
        $usuario = (new User())->findById($id);
        if (!$usuario) {
            header('Location: /admin/usuarios');
            exit();
        }
        $this->render('admin/usuarios/edit.twig', ['titulo' => 'Editar Usuário', 'usuario' => $usuario]);
    }

    public function update(int $id): void
    {
        $data = [
            'nome' => $_POST['nome'] ?? null,
            'email' => $_POST['email'] ?? null,
            'password' => $_POST['senha'] ?? null,
            'role_id' => $_POST['role_id'] ?? 2,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];

        if ($data['nome'] && $data['email']) {
            (new User())->update($id, $data);
        }
        header('Location: /admin/usuarios');
        exit();
    }

    public function destroy(int $id): void
    {
        if ($id === Auth::user()['id']) {
            header('Location: /admin/usuarios');
            exit();
        }
        (new User())->delete($id);
        header('Location: /admin/usuarios');
        exit();
    }
}
