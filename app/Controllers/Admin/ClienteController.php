<?php

namespace App\Controllers\Admin;

use App\Models\Cliente;
use Core\Controller;

class ClienteController extends Controller
{
    public function index(): void
    {
        $this->render('admin/clientes/index.twig', [
            'titulo' => 'Listagem de Clientes',
            'clientes' => (new Cliente())->findAll()
        ]);
    }

    public function create(): void
    {
        $this->render('admin/clientes/create.twig', [
            'titulo' => 'Novo Cliente'
        ]);
    }

    private function getRequestData(): array
    {
        $clienteData = [
            'nome' => $_POST['nome'] ?? null,
            'email' => $_POST['email'] ?? null,
            'senha' => $_POST['senha'] ?? null,
            'tipo_pessoa' => $_POST['tipo_pessoa'] ?? 'fisica',
            'documento' => $_POST['documento'] ?? null,
            'telefone' => $_POST['telefone'] ?? null,
            'data_nascimento' => $_POST['data_nascimento'] ?? null,
            'status' => isset($_POST['status']) ? 1 : 0,
        ];
        
        $enderecoData = [
            'id' => $_POST['endereco_id'] ?? null,
            'cep' => $_POST['cep'] ?? null,
            'logradouro' => $_POST['logradouro'] ?? null,
            'numero' => $_POST['numero'] ?? null,
            'complemento' => $_POST['complemento'] ?? null,
            'bairro' => $_POST['bairro'] ?? null,
            'cidade' => $_POST['cidade'] ?? null,
            'estado' => $_POST['estado'] ?? null,
        ];

        return [$clienteData, $enderecoData];
    }
    
    public function store(): void
    {
        [$clienteData, $enderecoData] = $this->getRequestData();

        if ($clienteData['nome'] && $clienteData['email'] && $clienteData['senha']) {
            (new Cliente())->create($clienteData, $enderecoData);
        }

        header('Location: /admin/clientes');
        exit();
    }

    public function edit(int $id): void
    {
        $cliente = (new Cliente())->findById($id);

        if (!$cliente) {
            header('Location: /admin/clientes');
            exit();
        }

        $this->render('admin/clientes/edit.twig', [
            'titulo' => 'Editar Cliente',
            'cliente' => $cliente
        ]);
    }

    public function update(int $id): void
    {
        [$clienteData, $enderecoData] = $this->getRequestData();
        
        if ($clienteData['nome'] && $clienteData['email']) {
            (new Cliente())->update($id, $clienteData, $enderecoData);
        }

        header('Location: /admin/clientes');
        exit();
    }
    
    public function destroy(int $id): void
    {
        if ($id) {
            (new Cliente())->delete($id);
        }
        header('Location: /admin/clientes');
        exit();
    }
}