<?php

namespace App\Models;

use Core\Database;
use PDO;

class FormaPagamento
{
    private PDO $pdo;

    public function __construct() { $this->pdo = Database::getInstance(); }
    public function findAll(): array { return $this->pdo->query("SELECT * FROM formas_pagamento ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC); }
    public function findById(int $id): array|false { $stmt = $this->pdo->prepare("SELECT * FROM formas_pagamento WHERE id = :id"); $stmt->bindValue(':id', $id); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
    public function create(string $nome, bool $ativo): bool { $stmt = $this->pdo->prepare("INSERT INTO formas_pagamento (nome, ativo) VALUES (:nome, :ativo)"); $stmt->bindValue(':nome', $nome); $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT); return $stmt->execute(); }
    public function update(int $id, string $nome, bool $ativo): bool { $stmt = $this->pdo->prepare("UPDATE formas_pagamento SET nome = :nome, ativo = :ativo WHERE id = :id"); $stmt->bindValue(':id', $id); $stmt->bindValue(':nome', $nome); $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT); return $stmt->execute(); }
    public function delete(int $id): bool { $stmt = $this->pdo->prepare("DELETE FROM formas_pagamento WHERE id = :id"); $stmt->bindValue(':id', $id); return $stmt->execute(); }
}