<?php
namespace App\Controllers\Admin;
use App\Models\TipoFrete;
use Core\Controller;
class TipoFreteController extends Controller
{
    private function getRequestData(): array { return ['nome' => $_POST['nome'] ?? null, 'tipo_valor' => $_POST['tipo_valor'] ?? 'gratis', 'valor' => $_POST['valor'] ?? null, 'ativo' => isset($_POST['ativo']) ? 1 : 0]; }
    public function index(): void { $this->render('admin/configuracoes/tipos_frete/index.twig', ['titulo' => 'Tipos de Frete', 'itens' => (new TipoFrete())->findAll()]); }
    public function create(): void { $this->render('admin/configuracoes/tipos_frete/create.twig', ['titulo' => 'Novo Tipo de Frete']); }
    public function store(): void { $data = $this->getRequestData(); if ($data['nome']) { (new TipoFrete())->create($data); } header('Location: /admin/configuracoes/tipos-frete'); exit(); }
    public function edit(int $id): void { $item = (new TipoFrete())->findById($id); if (!$item) { header('Location: /admin/configuracoes/tipos-frete'); exit(); } $this->render('admin/configuracoes/tipos_frete/edit.twig', ['titulo' => 'Editar Tipo de Frete', 'item' => $item]); }
    public function update(int $id): void { $data = $this->getRequestData(); if ($data['nome']) { (new TipoFrete())->update($id, $data); } header('Location: /admin/configuracoes/tipos-frete'); exit(); }
    public function destroy(int $id): void { (new TipoFrete())->delete($id); header('Location: /admin/configuracoes/tipos-frete'); exit(); }
}