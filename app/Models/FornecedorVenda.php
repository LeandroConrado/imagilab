<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FornecedorVenda extends Model
{
    protected $table = 'vw_vendas_por_fornecedor';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'fornecedor',
        'total_pedidos',
        'total_itens',
        'total_vendido',
    ];
}
