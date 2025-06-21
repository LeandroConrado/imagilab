<?php
namespace App\Controllers\Admin;
use App\Models\PedidoStatus;
use Core\Controller;
class PedidoStatusController extends Controller
{
    public function index(): void { $this->render('admin/configuracoes/pedido_status/index.twig', ['titulo' => 'Status de Pedido', 'itens' => (new PedidoStatus())->findAll()]); }
    public function create(): void { $this->render('admin/configuracoes/pedido_status/create.twig', ['titulo' => 'Novo Status de Pedido']); }
    public function store(): void { $nome = $_POST['nome'] ?? null; $cor = $_POST['cor_badge'] ?? 'secondary'; if ($nome) { (new PedidoStatus())->create($nome, $cor); } header('Location: /admin/configuracoes/pedido-status'); exit(); }
    public function edit(int $id): void { $item = (new PedidoStatus())->findById($id); if (!$item) { header('Location: /admin/configuracoes/pedido-status'); exit(); } $this->render('admin/configuracoes/pedido_status/edit.twig', ['titulo' => 'Editar Status de Pedido', 'item' => $item]); }
    public function update(int $id): void { $nome = $_POST['nome'] ?? null; $cor = $_POST['cor_badge'] ?? 'secondary'; if ($nome) { (new PedidoStatus())->update($id, $nome, $cor); } header('Location: /admin/configuracoes/pedido-status'); exit(); }
    public function destroy(int $id): void { (new PedidoStatus())->delete($id); header('Location: /admin/configuracoes/pedido-status'); exit(); }
}