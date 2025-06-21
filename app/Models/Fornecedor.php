<?php

namespace App\Models;

use Core\Database;
use PDO;

class Fornecedor
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findAll(): array
    {
        $sql = "
            SELECT f.id, f.nome, f.documento, f.status, e.cidade, e.estado 
            FROM fornecedores f
            LEFT JOIN enderecos e ON f.id = e.entidade_id AND e.entidade_tipo = 'fornecedor'
            ORDER BY f.nome ASC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $sql = "
            SELECT f.*, e.*, f.id as id, e.id as endereco_id
            FROM fornecedores f
            LEFT JOIN enderecos e ON f.id = e.entidade_id AND e.entidade_tipo = 'fornecedor'
            WHERE f.id = :id LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $fornecedorData, array $enderecoData): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO fornecedores (nome, tipo_pessoa, documento, contato_email, contato_telefone, status) 
                VALUES (:nome, :tipo_pessoa, :documento, :contato_email, :contato_telefone, :status)
            ");
            $stmt->bindValue(':nome', $fornecedorData['nome']);
            $stmt->bindValue(':tipo_pessoa', $fornecedorData['tipo_pessoa']);
            $stmt->bindValue(':documento', $fornecedorData['documento']);
            $stmt->bindValue(':contato_email', $fornecedorData['contato_email']);
            $stmt->bindValue(':contato_telefone', $fornecedorData['contato_telefone']);
            $stmt->bindValue(':status', $fornecedorData['status'], PDO::PARAM_INT);
            $stmt->execute();
            $fornecedorId = (int)$this->pdo->lastInsertId();

            $enderecoModel = new Endereco();
            $enderecoModel->create('fornecedor', $fornecedorId, $enderecoData);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $fornecedorData, array $enderecoData): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            $enderecoModel = new Endereco();
            // Se o fornecedor já tem um endereço, atualiza. Se não, cria um.
            if (!empty($enderecoData['id'])) {
                $enderecoModel->update($enderecoData['id'], $enderecoData);
            } else {
                $enderecoModel->create('fornecedor', $id, $enderecoData);
            }
            
            $stmt = $this->pdo->prepare("
                UPDATE fornecedores 
                SET nome = :nome, tipo_pessoa = :tipo_pessoa, documento = :documento, contato_email = :contato_email, contato_telefone = :contato_telefone, status = :status
                WHERE id = :id
            ");
            $stmt->bindValue(':nome', $fornecedorData['nome']);
            $stmt->bindValue(':tipo_pessoa', $fornecedorData['tipo_pessoa']);
            $stmt->bindValue(':documento', $fornecedorData['documento']);
            $stmt->bindValue(':contato_email', $fornecedorData['contato_email']);
            $stmt->bindValue(':contato_telefone', $fornecedorData['contato_telefone']);
            $stmt->bindValue(':status', $fornecedorData['status'], PDO::PARAM_INT);
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
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("DELETE FROM fornecedores WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->pdo->prepare("DELETE FROM enderecos WHERE entidade_id = :id AND entidade_tipo = 'fornecedor'");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}