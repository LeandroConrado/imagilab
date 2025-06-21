<?php

namespace App\Controllers\Admin;

use App\Helpers\Slugger;
use App\Helpers\Uploader;
use App\Models\Categoria;
use Core\Controller;

class CategoriaController extends Controller
{
    public function index(): void
    {
        $this->render('admin/categorias/index.twig', [
            'titulo' => 'Listagem de Categorias',
            'categorias' => (new Categoria())->findAll()
        ]);
    }

    public function create(): void
    {
        $this->render('admin/categorias/create.twig', [
            'titulo' => 'Nova Categoria'
        ]);
    }

    public function store(): void
    {
        $nome = $_POST['nome'] ?? null;
        if (!$nome) {
            header('Location: /admin/categorias');
            exit();
        }

        $imagePath = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
            $imagePath = Uploader::upload($_FILES['imagem'], 'categorias');
        }

        $data = [
            'nome' => $nome,
            'slug' => Slugger::generate($nome, 'categorias'),
            'imagem' => $imagePath,
            'status' => isset($_POST['status']) ? 1 : 0,
            'destaque' => isset($_POST['destaque']) ? 1 : 0,
        ];
        
        (new Categoria())->create($data);

        header('Location: /admin/categorias');
        exit();
    }

    public function edit(int $id): void
    {
        $categoria = (new Categoria())->findById($id);
        if (!$categoria) {
            header('Location: /admin/categorias');
            exit();
        }
        $this->render('admin/categorias/edit.twig', [
            'titulo' => 'Editar Categoria',
            'categoria' => $categoria
        ]);
    }

    public function update(int $id): void
    {
        $categoriaModel = new Categoria();
        $categoria = $categoriaModel->findById($id);
        if (!$categoria) {
            header('Location: /admin/categorias');
            exit();
        }

        $nome = $_POST['nome'] ?? null;
        if (!$nome) {
            header('Location: /admin/categorias');
            exit();
        }

        $imagePath = $categoria['imagem'];
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
            Uploader::delete($imagePath);
            $imagePath = Uploader::upload($_FILES['imagem'], 'categorias');
        }
        
        $data = [
            'nome' => $nome,
            'slug' => Slugger::generate($nome, 'categorias', $id),
            'imagem' => $imagePath,
            'status' => isset($_POST['status']) ? 1 : 0,
            'destaque' => isset($_POST['destaque']) ? 1 : 0,
        ];

        $categoriaModel->update($id, $data);

        header('Location: /admin/categorias');
        exit();
    }
    
    public function destroy(int $id): void
    {
        if ($id) {
            (new Categoria())->delete($id);
        }
        header('Location: /admin/categorias');
        exit();
    }
}