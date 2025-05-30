<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW vw_vendas_por_fornecedor AS
            SELECT
                suppliers.id,
                suppliers.name AS fornecedor,
                COUNT(DISTINCT orders.id) AS total_pedidos,
                SUM(order_items.quantity) AS total_itens,
                SUM(order_items.quantity * order_items.price) AS total_vendido
            FROM suppliers
            LEFT JOIN products ON products.supplier_id = suppliers.id
            LEFT JOIN order_items ON order_items.product_id = products.id
            LEFT JOIN orders ON orders.id = order_items.order_id
            GROUP BY suppliers.id, suppliers.name;

        SQL);
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vw_vendas_por_fornecedor");
    }
};
