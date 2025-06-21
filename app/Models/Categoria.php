<?php

namespace App\Models;

use App\Helpers\Uploader;
use Core\Database;
use PDO;

class Categoria
{
    public function findAll(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO categorias (nome, slug, imagem, status, destaque) VALUES (:nome, :slug, :imagem, :status, :destaque)");
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':slug', $data['slug']);
        $stmt->bindValue(':imagem', $data['imagem']);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindValue(':destaque', $data['destaque'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findById(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE categorias SET nome = :nome, slug = :slug, imagem = :imagem, status = :status, destaque = :destaque WHERE id = :id");
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':slug', $data['slug']);
        $stmt->bindValue(':imagem', $data['imagem']);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindValue(':destaque', $data['destaque'], PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $categoria = $this->findById($id);
        if ($categoria) {
            Uploader::delete($categoria['imagem']);
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}