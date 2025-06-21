<?php

namespace App\Models;

use Core\Database;
use PDO;

class PedidoItem
{
    private PDO $pdo;

    public function __construct() { $this->pdo = Database::getInstance(); }
    
    public function create(int $pedidoId, array $itemData): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) 
            VALUES (:pedido_id, :produto_id, :quantidade, :preco_unitario)
        ");
        $stmt->bindValue(':pedido_id', $pedidoId, PDO::PARAM_INT);
        $stmt->bindValue(':produto_id', $itemData['id'], PDO::PARAM_INT);
        $stmt->bindValue(':quantidade', $itemData['quantidade'], PDO::PARAM_INT);
        $stmt->bindValue(':preco_unitario', $itemData['preco']);
        return $stmt->execute();
    }
}