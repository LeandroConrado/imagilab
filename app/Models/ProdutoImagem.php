<?php

namespace App\Models;

use Core\Database;
use PDO;

class ProdutoImagem
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }
    
    public function create(int $produtoId, string $caminhoImagem): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO produto_imagens (produto_id, caminho_imagem) VALUES (:produto_id, :caminho_imagem)");
        $stmt->bindValue(':produto_id', $produtoId, PDO::PARAM_INT);
        $stmt->bindValue(':caminho_imagem', $caminhoImagem);
        return $stmt->execute();
    }

    public function findByProdutoId(int $produtoId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produto_imagens WHERE produto_id = :produto_id ORDER BY ordem ASC");
        $stmt->bindValue(':produto_id', $produtoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}