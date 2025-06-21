<?php
namespace App\Controllers\Admin;
use App\Models\CondicaoPagamento;
use Core\Controller;
class CondicaoPagamentoController extends Controller
{
    private function getRequestData(): array { return ['nome' => $_POST['nome'] ?? null, 'numero_parcelas' => $_POST['numero_parcelas'] ?? 1, 'intervalo_dias' => $_POST['intervalo_dias'] ?? null, 'ativo' => isset($_POST['ativo']) ? 1 : 0]; }
    public function index(): void { $this->render('admin/configuracoes/condicoes_pagamento/index.twig', ['titulo' => 'Condições de Pagamento', 'itens' => (new CondicaoPagamento())->findAll()]); }
    public function create(): void { $this->render('admin/configuracoes/condicoes_pagamento/create.twig', ['titulo' => 'Nova Condição de Pagamento']); }
    public function store(): void { $data = $this->getRequestData(); if ($data['nome']) { (new CondicaoPagamento())->create($data); } header('Location: /admin/configuracoes/condicoes-pagamento'); exit(); }
    public function edit(int $id): void { $item = (new CondicaoPagamento())->findById($id); if (!$item) { header('Location: /admin/configuracoes/condicoes-pagamento'); exit(); } $this->render('admin/configuracoes/condicoes_pagamento/edit.twig', ['titulo' => 'Editar Condição de Pagamento', 'item' => $item]); }
    public function update(int $id): void { $data = $this->getRequestData(); if ($data['nome']) { (new CondicaoPagamento())->update($id, $data); } header('Location: /admin/configuracoes/condicoes-pagamento'); exit(); }
    public function destroy(int $id): void { (new CondicaoPagamento())->delete($id); header('Location: /admin/configuracoes/condicoes-pagamento'); exit(); }
}