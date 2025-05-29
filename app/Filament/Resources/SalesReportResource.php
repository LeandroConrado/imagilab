<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesReportResource\Pages;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalesReportResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Relatórios de Vendas';

    protected static ?string $modelLabel = 'Relatório de Vendas';

    protected static ?string $pluralModelLabel = 'Relatórios de Vendas';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationGroup = 'Relatórios';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Não precisamos de formulário para relatórios
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Número do Pedido')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'shipped' => 'Enviado',
                        'delivered' => 'Entregue',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data do Pedido')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'shipped' => 'Enviado',
                        'delivered' => 'Entregue',
                        'cancelled' => 'Cancelado',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->label('Período')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date_from')
                            ->label('Data Inicial'),
                        \Filament\Forms\Components\DatePicker::make('date_until')
                            ->label('Data Final'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getNavigationBadge(): ?string
    {
        $today = static::getModel()::whereDate('created_at', today())->count();
        return $today > 0 ? (string) $today : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesReports::route('/'),
            'general' => Pages\GeneralSalesReport::route('/geral'),
            'customer' => Pages\CustomerSalesReport::route('/cliente'),
            'product' => Pages\ProductSalesReport::route('/produto'),
            'category' => Pages\CategorySalesReport::route('/categoria'),
            'supplier' => Pages\SupplierSalesReport::route('/fornecedor'),
            'region' => Pages\RegionSalesReport::route('/regiao'),
        ];
    }
}
