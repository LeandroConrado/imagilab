<?php
namespace App\Models;
use Core\Database;
use PDO;
class TipoFrete
{
    private PDO $pdo;
    public function __construct() { $this->pdo = Database::getInstance(); }
    public function findAll(): array { return $this->pdo->query("SELECT * FROM tipos_frete ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC); }
    public function findById(int $id): array|false { $stmt = $this->pdo->prepare("SELECT * FROM tipos_frete WHERE id = :id"); $stmt->bindValue(':id', $id); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
    public function create(array $data): bool { $stmt = $this->pdo->prepare("INSERT INTO tipos_frete (nome, tipo_valor, valor, ativo) VALUES (:nome, :tipo_valor, :valor, :ativo)"); $this->bindValues($stmt, $data); return $stmt->execute(); }
    public function update(int $id, array $data): bool { $stmt = $this->pdo->prepare("UPDATE tipos_frete SET nome = :nome, tipo_valor = :tipo_valor, valor = :valor, ativo = :ativo WHERE id = :id"); $this->bindValues($stmt, $data); $stmt->bindValue(':id', $id); return $stmt->execute(); }
    public function delete(int $id): bool { $stmt = $this->pdo->prepare("DELETE FROM tipos_frete WHERE id = :id"); $stmt->bindValue(':id', $id); return $stmt->execute(); }
    private function bindValues(\PDOStatement $stmt, array $data): void { $stmt->bindValue(':nome', $data['nome']); $stmt->bindValue(':tipo_valor', $data['tipo_valor']); $stmt->bindValue(':valor', $data['tipo_valor'] == 'fixo' ? str_replace(',', '.', $data['valor']) : null); $stmt->bindValue(':ativo', $data['ativo'], PDO::PARAM_INT); }
}