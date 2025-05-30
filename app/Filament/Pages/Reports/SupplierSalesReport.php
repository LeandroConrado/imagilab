<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use App\Exports\SupplierSalesReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;

class SupplierSalesReport extends Page implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string $view = 'filament.pages.reports.supplier-sales-report';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Relatórios';
    protected static ?string $title = 'Vendas por Fornecedor';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => DB::table('vw_vendas_por_fornecedor'))
            ->columns([
                TextColumn::make('fornecedor')->label('Fornecedor')->sortable()->searchable(),
                TextColumn::make('total_pedidos')->label('Total de Pedidos'),
                TextColumn::make('total_itens')->label('Itens Vendidos'),
                TextColumn::make('total_vendido')->label('Valor Total')->money('BRL'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Exportar para PDF')
                ->color('danger')
                ->action(fn () => response()->streamDownload(function () {
                    echo Pdf::loadView('filament.pages.reports.supplier-sales-report-pdf', [
                        'records' => $this->getFilteredTableQuery()->get(),
                    ])->stream();
                }, 'relatorio-vendas-fornecedor.pdf')),

            Action::make('Exportar para Excel')
                ->color('success')
                ->action(fn () => (new SupplierSalesReportExport(
                    $this->getFilteredTableQuery()->get()
                ))->download('relatorio-vendas-fornecedor.xlsx')),
        ];
    }
}
