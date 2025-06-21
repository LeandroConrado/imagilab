<?php

namespace App\Models;

use App\Helpers\Uploader;
use Core\Database;
use PDO;

class Banner
{
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function findAll(): array
    {
        return $this->pdo->query("SELECT id, titulo, ativo, data_inicio, data_fim, visualizacoes, cliques FROM banners ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM banners WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO banners (titulo, descricao, imagem_desktop, imagem_mobile, cor_fundo_1, cor_fundo_2, paginas_exibicao, link_botao, texto_botao, data_inicio, data_fim, ativo) VALUES (:titulo, :descricao, :imagem_desktop, :imagem_mobile, :cor_fundo_1, :cor_fundo_2, :paginas_exibicao, :link_botao, :texto_botao, :data_inicio, :data_fim, :ativo)";
        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $data);
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE banners SET titulo = :titulo, descricao = :descricao, imagem_desktop = :imagem_desktop, imagem_mobile = :imagem_mobile, cor_fundo_1 = :cor_fundo_1, cor_fundo_2 = :cor_fundo_2, paginas_exibicao = :paginas_exibicao, link_botao = :link_botao, texto_botao = :texto_botao, data_inicio = :data_inicio, data_fim = :data_fim, ativo = :ativo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $this->bindValues($stmt, $data);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $banner = $this->findById($id);
        if ($banner) {
            Uploader::delete($banner['imagem_desktop']);
            Uploader::delete($banner['imagem_mobile']);
        }

        $stmt = $this->pdo->prepare("DELETE FROM banners WHERE id = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    private function bindValues(\PDOStatement $stmt, array $data): void
    {
        $stmt->bindValue(':titulo', $data['titulo']);
        $stmt->bindValue(':descricao', $data['descricao']);
        $stmt->bindValue(':imagem_desktop', $data['imagem_desktop']);
        $stmt->bindValue(':imagem_mobile', $data['imagem_mobile']);
        $stmt->bindValue(':cor_fundo_1', $data['cor_fundo_1']);
        $stmt->bindValue(':cor_fundo_2', $data['cor_fundo_2']);
        $stmt->bindValue(':paginas_exibicao', $data['paginas_exibicao']);
        $stmt->bindValue(':link_botao', $data['link_botao']);
        $stmt->bindValue(':texto_botao', $data['texto_botao']);
        $stmt->bindValue(':data_inicio', $data['data_inicio'] ?: null);
        $stmt->bindValue(':data_fim', $data['data_fim'] ?: null);
        $stmt->bindValue(':ativo', $data['ativo'], PDO::PARAM_INT);
    }
}
