<?php

namespace App\Filament\Resources\SalesReportResource\Pages;

use App\Filament\Resources\SalesReportResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListSalesReports extends ListRecords
{
    protected static string $resource = SalesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('general_report')
                ->label('📊 Relatório Geral')
                ->icon('heroicon-o-chart-bar')
                ->color('primary')
                ->url(fn () => static::getResource()::getUrl('general')),

            Actions\Action::make('customer_report')
                ->label('👥 Por Cliente')
                ->icon('heroicon-o-users')
                ->color('success')
                ->url(fn () => static::getResource()::getUrl('customer')),
        ];
    }

    public function getTitle(): string
    {
        return 'Relatórios de Vendas';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\Reports\SalesOverviewWidget::class,
        ];
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.pages.sales-reports-header');
    }
}
