<?php

namespace App\Controllers\Admin;

use App\Helpers\Slugger;
use App\Helpers\Uploader;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\ProdutoImagem;
use Core\Controller;

class ProdutoController extends Controller
{
    public function index(): void
    {
        $this->render('admin/produtos/index.twig', [
            'titulo' => 'Listagem de Produtos',
            'produtos' => (new Produto())->findAll()
        ]);
    }

    public function create(): void
    {
        $this->render('admin/produtos/create.twig', [
            'titulo' => 'Novo Produto',
            'categorias' => (new Categoria())->findAll(),
            'fornecedores' => (new Fornecedor())->findAll()
        ]);
    }

    private function getProdutoDataFromPost(): array
    {
        $nome = $_POST['nome'] ?? null;
        return [
            'categoria_id' => $_POST['categoria_id'] ?: null,
            'fornecedor_id' => $_POST['fornecedor_id'] ?: null,
            'nome' => $nome,
            'descricao' => $_POST['descricao'] ?? '',
            'preco' => str_replace(',', '.', $_POST['preco'] ?? 0),
            'preco_custo' => str_replace(',', '.', $_POST['preco_custo'] ?? 0),
            'preco_promocional' => str_replace(',', '.', $_POST['preco_promocional'] ?? 0),
            'estoque' => $_POST['estoque'] ?? 0,
            'estoque_minimo' => $_POST['estoque_minimo'] ?? 0,
            'peso_kg' => str_replace(',', '.', $_POST['peso_kg'] ?? 0),
            'altura_cm' => $_POST['altura_cm'] ?? 0,
            'largura_cm' => $_POST['largura_cm'] ?? 0,
            'profundidade_cm' => $_POST['profundidade_cm'] ?? 0,
            'anunciar' => isset($_POST['anunciar']) ? 1 : 0,
            'destaque' => isset($_POST['destaque']) ? 1 : 0,
        ];
    }

    // AQUI ESTÁ A CORREÇÃO: Adicionamos um '?' antes de 'int'
    private function handleImageUploads(?int $produtoId = null): array
    {
        $imagensSalvas = [];
        if (isset($_FILES['imagens'])) {
            $files = $_FILES['imagens'];
            $totalFiles = count($files['name']);

            for ($i = 0; $i < $totalFiles; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];

                if ($file['error'] == UPLOAD_ERR_OK) {
                    $imagensSalvas[] = Uploader::upload($file, 'produtos');
                }
            }
        }
        return $imagensSalvas;
    }

    public function store(): void
    {
        $produtoData = $this->getProdutoDataFromPost();
        if (!$produtoData['nome']) {
            header('Location: /admin/produtos');
            exit();
        }

        $produtoData['slug'] = Slugger::generate($produtoData['nome'], 'produtos');
        $imagensSalvas = $this->handleImageUploads();
        
        (new Produto())->create($produtoData, $imagensSalvas);

        header('Location: /admin/produtos');
        exit();
    }

    public function edit(int $id): void
    {
        $produto = (new Produto())->findById($id);
        if (!$produto) {
            header('Location: /admin/produtos');
            exit();
        }

        $this->render('admin/produtos/edit.twig', [
            'titulo' => 'Editar Produto',
            'produto' => $produto,
            'categorias' => (new Categoria())->findAll(),
            'fornecedores' => (new Fornecedor())->findAll()
        ]);
    }

    public function update(int $id): void
    {
        $produtoData = $this->getProdutoDataFromPost();
        if (!$produtoData['nome']) {
            header('Location: /admin/produtos');
            exit();
        }

        $produtoData['slug'] = Slugger::generate($produtoData['nome'], 'produtos', $id);
        (new Produto())->update($id, $produtoData);

        $novasImagens = $this->handleImageUploads($id);
        if (!empty($novasImagens)) {
            $imagemModel = new ProdutoImagem();
            foreach ($novasImagens as $caminhoImagem) {
                $imagemModel->create($id, $caminhoImagem);
            }
        }

        header('Location: /admin/produtos');
        exit();
    }
    
    public function destroy(int $id): void
    {
        if ($id) {
            (new Produto())->delete($id);
        }
        header('Location: /admin/produtos');
        exit();
    }
}