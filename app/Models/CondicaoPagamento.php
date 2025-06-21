<?php
namespace App\Models;
use Core\Database;
use PDO;
class CondicaoPagamento
{
    private PDO $pdo;
    public function __construct() { $this->pdo = Database::getInstance(); }
    public function findAll(): array { return $this->pdo->query("SELECT * FROM condicoes_pagamento ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC); }
    public function findById(int $id): array|false { $stmt = $this->pdo->prepare("SELECT * FROM condicoes_pagamento WHERE id = :id"); $stmt->bindValue(':id', $id); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
    public function create(array $data): bool { $stmt = $this->pdo->prepare("INSERT INTO condicoes_pagamento (nome, numero_parcelas, intervalo_dias, ativo) VALUES (:nome, :numero_parcelas, :intervalo_dias, :ativo)"); $this->bindValues($stmt, $data); return $stmt->execute(); }
    public function update(int $id, array $data): bool { $stmt = $this->pdo->prepare("UPDATE condicoes_pagamento SET nome = :nome, numero_parcelas = :numero_parcelas, intervalo_dias = :intervalo_dias, ativo = :ativo WHERE id = :id"); $this->bindValues($stmt, $data); $stmt->bindValue(':id', $id); return $stmt->execute(); }
    public function delete(int $id): bool { $stmt = $this->pdo->prepare("DELETE FROM condicoes_pagamento WHERE id = :id"); $stmt->bindValue(':id', $id); return $stmt->execute(); }
    private function bindValues(\PDOStatement $stmt, array $data): void { $stmt->bindValue(':nome', $data['nome']); $stmt->bindValue(':numero_parcelas', $data['numero_parcelas'], PDO::PARAM_INT); $stmt->bindValue(':intervalo_dias', $data['intervalo_dias']); $stmt->bindValue(':ativo', $data['ativo'], PDO::PARAM_INT); }
}