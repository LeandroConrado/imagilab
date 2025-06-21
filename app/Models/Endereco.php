<?php

namespace App\Models;

use Core\Database;
use PDO;

class Endereco
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function create(string $entidadeTipo, int $entidadeId, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO enderecos (entidade_tipo, entidade_id, cep, logradouro, numero, complemento, bairro, cidade, estado) 
            VALUES (:entidade_tipo, :entidade_id, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado)
        ");
        $stmt->bindValue(':entidade_tipo', $entidadeTipo);
        $stmt->bindValue(':entidade_id', $entidadeId, PDO::PARAM_INT);
        $this->bindAll($stmt, $data);
        return $stmt->execute();
    }
    
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE enderecos 
            SET cep = :cep, logradouro = :logradouro, numero = :numero, complemento = :complemento, bairro = :bairro, cidade = :cidade, estado = :estado 
            WHERE id = :id
        ");
        $this->bindAll($stmt, $data);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function bindAll(\PDOStatement $stmt, array $data): void
    {
        $stmt->bindValue(':cep', $data['cep'] ?? null);
        $stmt->bindValue(':logradouro', $data['logradouro'] ?? null);
        $stmt->bindValue(':numero', $data['numero'] ?? null);
        $stmt->bindValue(':complemento', $data['complemento'] ?? null);
        $stmt->bindValue(':bairro', $data['bairro'] ?? null);
        $stmt->bindValue(':cidade', $data['cidade'] ?? null);
        $stmt->bindValue(':estado', $data['estado'] ?? null);
    }
}