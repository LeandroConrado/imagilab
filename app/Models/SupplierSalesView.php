<?php

// app/Models/SupplierSalesView.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierSalesView extends Model
{
    protected $table = 'vw_vendas_por_fornecedor';
    public $timestamps = false;
    protected $guarded = [];
}

