<?php

namespace App\Models;

use Core\Database;
use PDO;

class Pedido
{
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }
    
    public function findAll(): array
    {
        $sql = "
            SELECT 
                p.id, p.codigo_pedido, p.valor_total, p.created_at,
                c.nome as cliente_nome,
                ps.nome as status_nome, ps.cor_badge as status_cor
            FROM pedidos p
            JOIN clientes c ON p.cliente_id = c.id
            JOIN pedido_status ps ON p.pedido_status_id = ps.id
            ORDER BY p.id DESC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $sql = "
            SELECT p.*, c.nome as cliente_nome, c.email as cliente_email, c.documento as cliente_documento, c.telefone as cliente_telefone,
                   fp.nome as forma_pagamento_nome, ps.nome as status_nome, ps.cor_badge as status_cor
            FROM pedidos p
            LEFT JOIN clientes c ON p.cliente_id = c.id
            LEFT JOIN formas_pagamento fp ON p.forma_pagamento_id = fp.id
            LEFT JOIN pedido_status ps ON p.pedido_status_id = ps.id
            WHERE p.id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) return false;

        if ($pedido['endereco_entrega_id']) {
            $stmtEnd = $this->pdo->prepare("SELECT * FROM enderecos WHERE id = :id");
            $stmtEnd->bindValue(':id', $pedido['endereco_entrega_id'], PDO::PARAM_INT);
            $stmtEnd->execute();
            $pedido['endereco'] = $stmtEnd->fetch(PDO::FETCH_ASSOC);
        } else {
            $pedido['endereco'] = null;
        }

        $sqlItens = "
            SELECT pi.*, p.nome as produto_nome 
            FROM pedido_itens pi
            JOIN produtos p ON pi.produto_id = p.id
            WHERE pi.pedido_id = :id
        ";
        $stmtItens = $this->pdo->prepare($sqlItens);
        $stmtItens->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtItens->execute();
        $pedido['itens'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

        return $pedido;
    }

    private function generateOrderCode(): string
    {
        return 'PED-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }
    
    public function create(array $pedidoData, array $itensData): int|false
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO pedidos (cliente_id, codigo_pedido, forma_pagamento_id, condicao_pagamento_id, pedido_status_id, subtotal, valor_frete, desconto, valor_total, endereco_entrega_id)
                VALUES (:cliente_id, :codigo_pedido, :forma_pagamento_id, :condicao_pagamento_id, :pedido_status_id, :subtotal, :valor_frete, :desconto, :valor_total, :endereco_entrega_id)
            ");
            $stmt->bindValue(':cliente_id', $pedidoData['cliente_id'], PDO::PARAM_INT);
            $stmt->bindValue(':codigo_pedido', $this->generateOrderCode());
            $stmt->bindValue(':forma_pagamento_id', $pedidoData['forma_pagamento_id'], PDO::PARAM_INT);
            $stmt->bindValue(':condicao_pagamento_id', $pedidoData['condicao_pagamento_id'], PDO::PARAM_INT);
            $stmt->bindValue(':pedido_status_id', $pedidoData['pedido_status_id'], PDO::PARAM_INT);
            $stmt->bindValue(':subtotal', $pedidoData['subtotal']);
            $stmt->bindValue(':valor_frete', $pedidoData['valor_frete']);
            $stmt->bindValue(':desconto', $pedidoData['desconto']);
            $stmt->bindValue(':valor_total', $pedidoData['valor_total']);
            $stmt->bindValue(':endereco_entrega_id', $pedidoData['endereco_entrega_id'], PDO::PARAM_INT);
            $stmt->execute();
            $pedidoId = (int)$this->pdo->lastInsertId();

            if ($pedidoId > 0) {
                $itemModel = new PedidoItem();
                $stmtUpdateEstoque = $this->pdo->prepare("UPDATE produtos SET estoque = estoque - :quantidade WHERE id = :id");
                
                foreach ($itensData as $item) {
                    $itemModel->create($pedidoId, $item);
                    $stmtUpdateEstoque->bindValue(':quantidade', $item['quantidade'], PDO::PARAM_INT);
                    $stmtUpdateEstoque->bindValue(':id', $item['id'], PDO::PARAM_INT);
                    $stmtUpdateEstoque->execute();
                }
            }

            $this->pdo->commit();
            return $pedidoId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    public function updateStatus(int $id, int $statusId, ?string $codigoRastreio): bool
    {
        $stmt = $this->pdo->prepare("UPDATE pedidos SET pedido_status_id = :status_id, codigo_rastreio = :codigo_rastreio, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindValue(':status_id', $statusId, PDO::PARAM_INT);
        $stmt->bindValue(':codigo_rastreio', $codigoRastreio);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function gerarRelatorioVendas(string $dataInicio, string $dataFim): array
    {
        $dataFimCompleta = $dataFim . ' 23:59:59';

        $sql = "
            SELECT 
                p.id, p.codigo_pedido, p.valor_total, p.created_at,
                c.nome as cliente_nome,
                ps.nome as status_nome, ps.cor_badge as status_cor
            FROM pedidos p
            JOIN clientes c ON p.cliente_id = c.id
            JOIN pedido_status ps ON p.pedido_status_id = ps.id
            WHERE p.created_at BETWEEN :data_inicio AND :data_fim
            ORDER BY p.id DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':data_inicio', $dataInicio);
        $stmt->bindValue(':data_fim', $dataFimCompleta);
        $stmt->execute();

        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $faturamentoTotal = array_sum(array_column($pedidos, 'valor_total'));
        $totalPedidos = count($pedidos);
        $ticketMedio = $totalPedidos > 0 ? $faturamentoTotal / $totalPedidos : 0;

        return [
            'pedidos' => $pedidos,
            'metricas' => [
                'faturamento_total' => $faturamentoTotal,
                'total_pedidos' => $totalPedidos,
                'ticket_medio' => $ticketMedio
            ]
        ];
    }
    
    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
    }

    public function getFaturamentoTotal(): float
    {
        return (float)$this->pdo->query("SELECT SUM(valor_total) FROM pedidos")->fetchColumn();
    }
    
    public function getVendasDiarias(int $dias = 30): array
    {
        $sql = "SELECT DATE(created_at) as dia, SUM(valor_total) as total FROM pedidos WHERE created_at >= :data_inicio GROUP BY DATE(created_at) ORDER BY dia ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':data_inicio', date('Y-m-d H:i:s', strtotime("-{$dias} days")));
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
