<?php
namespace App\Models;
use Core\Database;
use PDO;
class PedidoStatus
{
    private PDO $pdo;
    public function __construct() { $this->pdo = Database::getInstance(); }
    public function findAll(): array { return $this->pdo->query("SELECT * FROM pedido_status ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC); }
    public function findById(int $id): array|false { $stmt = $this->pdo->prepare("SELECT * FROM pedido_status WHERE id = :id"); $stmt->bindValue(':id', $id); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
    public function create(string $nome, string $cor): bool { $stmt = $this->pdo->prepare("INSERT INTO pedido_status (nome, cor_badge) VALUES (:nome, :cor)"); $stmt->bindValue(':nome', $nome); $stmt->bindValue(':cor', $cor); return $stmt->execute(); }
    public function update(int $id, string $nome, string $cor): bool { $stmt = $this->pdo->prepare("UPDATE pedido_status SET nome = :nome, cor_badge = :cor WHERE id = :id"); $stmt->bindValue(':id', $id); $stmt->bindValue(':nome', $nome); $stmt->bindValue(':cor', $cor); return $stmt->execute(); }
    public function delete(int $id): bool { $stmt = $this->pdo->prepare("DELETE FROM pedido_status WHERE id = :id"); $stmt->bindValue(':id', $id); return $stmt->execute(); }
}