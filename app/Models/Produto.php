<?php

namespace App\Models;

use App\Helpers\Uploader;
use Core\Database;
use PDO;

class Produto
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findAll(): array
    {
        $sql = "
            SELECT p.id, p.nome, p.preco, p.estoque, p.anunciar, p.destaque, c.nome as categoria_nome,
                   (SELECT pi.caminho_imagem FROM produto_imagens pi WHERE pi.produto_id = p.id ORDER BY pi.ordem ASC LIMIT 1) as imagem_principal
            FROM produtos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            ORDER BY p.nome ASC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $produto = $this->pdo->prepare("SELECT * FROM produtos WHERE id = :id");
        $produto->bindValue(':id', $id, PDO::PARAM_INT);
        $produto->execute();
        $data = $produto->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $data['imagens'] = (new ProdutoImagem())->findByProdutoId($id);
        }
        return $data;
    }

    public function create(array $produtoData, array $imagens): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO produtos (categoria_id, fornecedor_id, nome, slug, descricao, preco, preco_custo, preco_promocional, estoque, estoque_minimo, peso_kg, altura_cm, largura_cm, profundidade_cm, anunciar, destaque) 
                VALUES (:categoria_id, :fornecedor_id, :nome, :slug, :descricao, :preco, :preco_custo, :preco_promocional, :estoque, :estoque_minimo, :peso_kg, :altura_cm, :largura_cm, :profundidade_cm, :anunciar, :destaque)
            ");
            
            $this->bindProdutoValues($stmt, $produtoData);
            $stmt->execute();

            $produtoId = (int)$this->pdo->lastInsertId();
            $imagemModel = new ProdutoImagem();
            foreach ($imagens as $caminhoImagem) {
                $imagemModel->create($produtoId, $caminhoImagem);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $produtoData): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                UPDATE produtos SET
                    categoria_id = :categoria_id, fornecedor_id = :fornecedor_id, nome = :nome, slug = :slug, descricao = :descricao, 
                    preco = :preco, preco_custo = :preco_custo, preco_promocional = :preco_promocional, estoque = :estoque, estoque_minimo = :estoque_minimo, 
                    peso_kg = :peso_kg, altura_cm = :altura_cm, largura_cm = :largura_cm, profundidade_cm = :profundidade_cm, anunciar = :anunciar, destaque = :destaque
                WHERE id = :id
            ");

            $this->bindProdutoValues($stmt, $produtoData);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $produto = $this->findById($id);
        try {
            $this->pdo->beginTransaction();
            if ($produto && !empty($produto['imagens'])) {
                foreach ($produto['imagens'] as $imagem) {
                    Uploader::delete($imagem['caminho_imagem']);
                }
                $stmtImg = $this->pdo->prepare("DELETE FROM produto_imagens WHERE produto_id = :id");
                $stmtImg->bindValue(':id', $id, PDO::PARAM_INT);
                $stmtImg->execute();
            }
            $stmtProd = $this->pdo->prepare("DELETE FROM produtos WHERE id = :id");
            $stmtProd->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtProd->execute();
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function bindProdutoValues(\PDOStatement $stmt, array $data): void
    {
        $stmt->bindValue(':categoria_id', $data['categoria_id'] ?: null, PDO::PARAM_INT);
        $stmt->bindValue(':fornecedor_id', $data['fornecedor_id'] ?: null, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':slug', $data['slug']);
        $stmt->bindValue(':descricao', $data['descricao']);
        $stmt->bindValue(':preco', $data['preco']);
        $stmt->bindValue(':preco_custo', $data['preco_custo'] ?: null);
        $stmt->bindValue(':preco_promocional', $data['preco_promocional'] ?: null);
        $stmt->bindValue(':estoque', $data['estoque'], PDO::PARAM_INT);
        $stmt->bindValue(':estoque_minimo', $data['estoque_minimo'] ?: null, PDO::PARAM_INT);
        $stmt->bindValue(':peso_kg', $data['peso_kg']);
        $stmt->bindValue(':altura_cm', $data['altura_cm'], PDO::PARAM_INT);
        $stmt->bindValue(':largura_cm', $data['largura_cm'], PDO::PARAM_INT);
        $stmt->bindValue(':profundidade_cm', $data['profundidade_cm'], PDO::PARAM_INT);
        $stmt->bindValue(':anunciar', $data['anunciar'], PDO::PARAM_INT);
        $stmt->bindValue(':destaque', $data['destaque'], PDO::PARAM_INT);
    }
    
    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
    }
    
    public function getMaisVendidos(int $limite = 5): array
    {
        $sql = "SELECT p.nome, SUM(pi.quantidade) as total_vendido FROM pedido_itens pi JOIN produtos p ON pi.produto_id = p.id GROUP BY pi.produto_id, p.nome ORDER BY total_vendido DESC LIMIT :limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
