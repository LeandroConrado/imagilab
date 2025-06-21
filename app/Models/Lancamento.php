<?php
namespace App\Models;
use Core\Database;
use PDO;

class Lancamento
{
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function findAll(array $filtros = []): array
    {
        $sql = "
            SELECT 
                l.*,
                CASE
                    WHEN l.origem_tipo = 'cliente' THEN c.nome
                    WHEN l.origem_tipo = 'fornecedor' THEN f.nome
                    WHEN l.origem_tipo = 'pedido' THEN cliente_do_pedido.nome
                    ELSE NULL
                END as nome_origem
            FROM 
                lancamentos l
            LEFT JOIN 
                clientes c ON l.origem_id = c.id AND l.origem_tipo = 'cliente'
            LEFT JOIN 
                fornecedores f ON l.origem_id = f.id AND l.origem_tipo = 'fornecedor'
            LEFT JOIN 
                pedidos p ON l.origem_id = p.id AND l.origem_tipo = 'pedido'
            LEFT JOIN 
                clientes cliente_do_pedido ON p.cliente_id = cliente_do_pedido.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filtros['tipo'])) { $sql .= " AND l.tipo = :tipo"; $params[':tipo'] = $filtros['tipo']; }
        if (!empty($filtros['status'])) { $sql .= " AND l.status = :status"; $params[':status'] = $filtros['status']; }
        if (!empty($filtros['data_inicio'])) { $sql .= " AND l.data_vencimento >= :data_inicio"; $params[':data_inicio'] = $filtros['data_inicio']; }
        if (!empty($filtros['data_fim'])) { $sql .= " AND l.data_vencimento <= :data_fim"; $params[':data_fim'] = $filtros['data_fim']; }
        
        $sql .= " ORDER BY l.id DESC, l.data_vencimento DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM lancamentos WHERE id = :id"); 
        $stmt->bindValue(':id', $id); 
        $stmt->execute(); 
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }
    
    public function findByOrigem(string $origemTipo, int $origemId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM lancamentos WHERE origem_tipo = :tipo AND origem_id = :id");
        $stmt->bindValue(':tipo', $origemTipo);
        $stmt->bindValue(':id', $origemId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO lancamentos (descricao, valor, tipo, status, data_vencimento, data_pagamento, observacoes, origem_tipo, origem_id, grupo_id) VALUES (:descricao, :valor, :tipo, :status, :data_vencimento, :data_pagamento, :observacoes, :origem_tipo, :origem_id, :grupo_id)");
        $this->bindValues($stmt, $data);
        return $stmt->execute();
    }
    
    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("UPDATE lancamentos SET descricao = :descricao, valor = :valor, tipo = :tipo, status = :status, data_vencimento = :data_vencimento, data_pagamento = :data_pagamento, observacoes = :observacoes, origem_tipo = :origem_tipo, origem_id = :origem_id WHERE id = :id");
        $this->bindValues($stmt, $data);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
    
    public function delete(int $id): bool
    {
        $lancamento = $this->findById($id);

        if ($lancamento && !empty($lancamento['grupo_id'])) {
            $stmt = $this->pdo->prepare("DELETE FROM lancamentos WHERE grupo_id = :grupo_id");
            $stmt->bindValue(':grupo_id', $lancamento['grupo_id']);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM lancamentos WHERE id = :id");
            $stmt->bindValue(':id', $id);
        }
        
        return $stmt->execute();
    }

    private function bindValues(\PDOStatement $stmt, array $data): void
    {
        $stmt->bindValue(':descricao', $data['descricao']);
        $stmt->bindValue(':valor', $data['valor']);
        $stmt->bindValue(':tipo', $data['tipo']);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':data_vencimento', $data['data_vencimento']);
        $stmt->bindValue(':data_pagamento', $data['data_pagamento']);
        $stmt->bindValue(':observacoes', $data['observacoes']);
        $stmt->bindValue(':origem_tipo', $data['origem_tipo']);
        $stmt->bindValue(':origem_id', $data['origem_id'] ? (int)$data['origem_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':grupo_id', $data['grupo_id'] ?? null);
    }

    public function getDashboardMetrics(): array
    {
        $aReceber = $this->pdo->query("SELECT SUM(valor) FROM lancamentos WHERE tipo = 'receber' AND status = 'pendente'")->fetchColumn() ?: 0;
        $aPagar = $this->pdo->query("SELECT SUM(valor) FROM lancamentos WHERE tipo = 'pagar' AND status = 'pendente'")->fetchColumn() ?: 0;
        $pagoMes = $this->pdo->query("SELECT SUM(valor) FROM lancamentos WHERE tipo = 'pagar' AND status = 'pago' AND MONTH(data_pagamento) = MONTH(CURDATE()) AND YEAR(data_pagamento) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $recebidoMes = $this->pdo->query("SELECT SUM(valor) FROM lancamentos WHERE tipo = 'receber' AND status = 'pago' AND MONTH(data_pagamento) = MONTH(CURDATE()) AND YEAR(data_pagamento) = YEAR(CURDATE())")->fetchColumn() ?: 0;

        return [
            'a_receber' => $aReceber,
            'a_pagar' => $aPagar,
            'balanco_pendente' => $aReceber - $aPagar,
            'saldo_mes' => $recebidoMes - $pagoMes
        ];
    }
}
