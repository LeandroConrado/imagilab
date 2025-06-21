<?php

namespace App\Models;

use Core\Database;
use PDO;

class User
{
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function findByEmail(string $email): array|false
    {
        // Usa a coluna 'password' para bater com o seu banco de dados
        $stmt = $this->pdo->prepare("SELECT id, nome, email, password, role_id, ativo FROM usuarios WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll(): array
    {
        return $this->pdo->query("SELECT id, nome, email, role_id, ativo FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT id, nome, email, role_id, ativo FROM usuarios WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, password, role_id, ativo) VALUES (:nome, :email, :password, :role_id, :ativo)");
        
        $senhaHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':password', $senhaHash);
        $stmt->bindValue(':role_id', $data['role_id'], PDO::PARAM_INT);
        $stmt->bindValue(':ativo', $data['ativo'], PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE usuarios SET nome = :nome, email = :email, role_id = :role_id, ativo = :ativo";
        
        if (!empty($data['password'])) {
            $sql .= ", password = :password";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':role_id', $data['role_id'], PDO::PARAM_INT);
        $stmt->bindValue(':ativo', $data['ativo'], PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if (!empty($data['password'])) {
            $senhaHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindValue(':password', $senhaHash);
        }

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
