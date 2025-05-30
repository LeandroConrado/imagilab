<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SupplierSalesReportExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('vw_vendas_por_fornecedor')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fornecedor',
            'Total de Pedidos',
            'Total de Itens',
            'Total Vendido',
        ];
    }
}
