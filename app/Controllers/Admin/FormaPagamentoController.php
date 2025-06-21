<?php

namespace App\Controllers\Admin;

use App\Models\FormaPagamento;
use Core\Controller;

class FormaPagamentoController extends Controller
{
    public function index(): void { $this->render('admin/configuracoes/formas_pagamento/index.twig', ['titulo' => 'Formas de Pagamento', 'itens' => (new FormaPagamento())->findAll()]); }
    public function create(): void { $this->render('admin/configuracoes/formas_pagamento/create.twig', ['titulo' => 'Nova Forma de Pagamento']); }
    public function store(): void { $nome = $_POST['nome'] ?? null; $ativo = isset($_POST['ativo']); if ($nome) { (new FormaPagamento())->create($nome, $ativo); } header('Location: /admin/configuracoes/formas-pagamento'); exit(); }
    public function edit(int $id): void { $item = (new FormaPagamento())->findById($id); if (!$item) { header('Location: /admin/configuracoes/formas-pagamento'); exit(); } $this->render('admin/configuracoes/formas_pagamento/edit.twig', ['titulo' => 'Editar Forma de Pagamento', 'item' => $item]); }
    public function update(int $id): void { $nome = $_POST['nome'] ?? null; $ativo = isset($_POST['ativo']); if ($nome) { (new FormaPagamento())->update($id, $nome, $ativo); } header('Location: /admin/configuracoes/formas-pagamento'); exit(); }
    public function destroy(int $id): void { (new FormaPagamento())->delete($id); header('Location: /admin/configuracoes/formas-pagamento'); exit(); }
}