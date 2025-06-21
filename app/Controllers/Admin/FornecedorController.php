<?php

namespace App\Controllers\Admin;

use App\Models\Fornecedor;
use Core\Controller;

class FornecedorController extends Controller
{
    public function index(): void
    {
        $this->render('admin/fornecedores/index.twig', [
            'titulo' => 'Listagem de Fornecedores',
            'fornecedores' => (new Fornecedor())->findAll()
        ]);
    }

    public function create(): void
    {
        $this->render('admin/fornecedores/create.twig', [
            'titulo' => 'Novo Fornecedor'
        ]);
    }

    private function getRequestData(): array
    {
        $fornecedorData = [
            'nome' => $_POST['nome'] ?? null,
            'tipo_pessoa' => $_POST['tipo_pessoa'] ?? 'juridica',
            'documento' => $_POST['documento'] ?? null,
            'contato_email' => $_POST['contato_email'] ?? null,
            'contato_telefone' => $_POST['contato_telefone'] ?? null,
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

        return [$fornecedorData, $enderecoData];
    }

    public function store(): void
    {
        [$fornecedorData, $enderecoData] = $this->getRequestData();

        if ($fornecedorData['nome']) {
            (new Fornecedor())->create($fornecedorData, $enderecoData);
        }

        header('Location: /admin/fornecedores');
        exit();
    }

    public function edit(int $id): void
    {
        $fornecedor = (new Fornecedor())->findById($id);

        if (!$fornecedor) {
            header('Location: /admin/fornecedores');
            exit();
        }

        $this->render('admin/fornecedores/edit.twig', [
            'titulo' => 'Editar Fornecedor',
            'fornecedor' => $fornecedor
        ]);
    }

    public function update(int $id): void
    {
        [$fornecedorData, $enderecoData] = $this->getRequestData();
        
        if ($fornecedorData['nome']) {
            (new Fornecedor())->update($id, $fornecedorData, $enderecoData);
        }

        header('Location: /admin/fornecedores');
        exit();
    }
    
    public function destroy(int $id): void
    {
        if ($id) {
            (new Fornecedor())->delete($id);
        }
        header('Location: /admin/fornecedores');
        exit();
    }
}