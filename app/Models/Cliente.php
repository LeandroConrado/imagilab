<?php

namespace App\Models;

use Core\Database;
use PDO;

class Cliente
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findAll(): array
    {
        $sql = "
            SELECT 
                c.id, c.nome, c.email, c.documento, c.status, 
                e.cidade, e.estado, e.id as endereco_id 
            FROM clientes c
            LEFT JOIN enderecos e ON c.id = e.entidade_id AND e.entidade_tipo = 'cliente'
            ORDER BY c.nome ASC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findById(int $id): array|false
    {
        $sql = "
            SELECT c.*, e.*, c.id as id, e.id as endereco_id
            FROM clientes c
            LEFT JOIN enderecos e ON c.id = e.entidade_id AND e.entidade_tipo = 'cliente'
            WHERE c.id = :id LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create(array $clienteData, array $enderecoData): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO clientes (nome, email, senha, tipo_pessoa, documento, telefone, data_nascimento, status) 
                VALUES (:nome, :email, :senha, :tipo_pessoa, :documento, :telefone, :data_nascimento, :status)
            ");
            $stmt->bindValue(':nome', $clienteData['nome']);
            $stmt->bindValue(':email', $clienteData['email']);
            $stmt->bindValue(':senha', password_hash($clienteData['senha'], PASSWORD_DEFAULT));
            $stmt->bindValue(':tipo_pessoa', $clienteData['tipo_pessoa']);
            $stmt->bindValue(':documento', $clienteData['documento']);
            $stmt->bindValue(':telefone', $clienteData['telefone']);
            $stmt->bindValue(':data_nascimento', $clienteData['data_nascimento'] ?: null);
            $stmt->bindValue(':status', $clienteData['status'], PDO::PARAM_INT);
            $stmt->execute();
            $clienteId = (int)$this->pdo->lastInsertId();

            $enderecoModel = new Endereco();
            $enderecoModel->create('cliente', $clienteId, $enderecoData);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $clienteData, array $enderecoData): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            $enderecoModel = new Endereco();
            if (!empty($enderecoData['id'])) {
                $enderecoModel->update($enderecoData['id'], $enderecoData);
            } else {
                $enderecoModel->create('cliente', $id, $enderecoData);
            }

            $sql = "UPDATE clientes SET nome = :nome, email = :email, tipo_pessoa = :tipo_pessoa, documento = :documento, telefone = :telefone, data_nascimento = :data_nascimento, status = :status";
            if (!empty($clienteData['senha'])) {
                $sql .= ", senha = :senha";
            }
            $sql .= " WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':nome', $clienteData['nome']);
            $stmt->bindValue(':email', $clienteData['email']);
            $stmt->bindValue(':tipo_pessoa', $clienteData['tipo_pessoa']);
            $stmt->bindValue(':documento', $clienteData['documento']);
            $stmt->bindValue(':telefone', $clienteData['telefone']);
            $stmt->bindValue(':data_nascimento', $clienteData['data_nascimento'] ?: null);
            $stmt->bindValue(':status', $clienteData['status'], PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if (!empty($clienteData['senha'])) {
                $stmt->bindValue(':senha', password_hash($clienteData['senha'], PASSWORD_DEFAULT));
            }
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
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->pdo->prepare("DELETE FROM enderecos WHERE entidade_id = :id AND entidade_tipo = 'cliente'");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Conta o total de registros na tabela.
     */
    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    }
}
