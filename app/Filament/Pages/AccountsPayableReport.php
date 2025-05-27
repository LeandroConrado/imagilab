<?php

namespace App\Filament\Pages;

use App\Models\AccountsPayable;
use App\Models\Supplier;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class AccountsPayableReport extends Page implements HasTable, HasForms, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.accounts-payable-report';

    protected static ?string $navigationLabel = 'Relatório Contas a Pagar';

    protected static ?string $title = 'Relatório de Contas a Pagar';

    protected static ?string $navigationGroup = 'Relatórios';

    protected static ?int $navigationSort = 1;

    // Propriedades para filtros
    public $date_from;
    public $date_to;
    public $supplier_id;
    public $status_filter;

    public function mount(): void
    {
        // Filtros padrão - último mês
        $this->date_from = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->endOfMonth()->format('Y-m-d');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    // Implementar exportação PDF
                    $this->exportToPDF();
                }),

            Action::make('export_excel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    // Implementar exportação Excel
                    $this->exportToExcel();
                }),

            Action::make('filters')
                ->label('Filtros')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->form([
                    DatePicker::make('date_from')
                        ->label('Data Inicial')
                        ->default($this->date_from),
                    
                    DatePicker::make('date_to')
                        ->label('Data Final')
                        ->default($this->date_to),
                    
                    Select::make('supplier_id')
                        ->label('Fornecedor')
                        ->options(Supplier::pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Todos os fornecedores'),
                    
                    Select::make('status_filter')
                        ->label('Status')
                        ->options([
                            'pending' => 'Pendente',
                            'overdue' => 'Em Atraso',
                            'partial' => 'Parcial',
                            'paid' => 'Pago',
                            'cancelled' => 'Cancelado',
                        ])
                        ->placeholder('Todos os status'),
                ])
                ->action(function (array $data) {
                    $this->date_from = $data['date_from'];
                    $this->date_to = $data['date_to'];
                    $this->supplier_id = $data['supplier_id'];
                    $this->status_filter = $data['status_filter'];
                    
                    // Atualizar a tabela
                    $this->resetTable();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('N° Fatura')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Fornecedor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('BRL')
                            ->label('Total Geral:'),
                    ]),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Valor Pago')
                    ->money('BRL')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('BRL')
                            ->label('Total Pago:'),
                    ]),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Saldo Restante')
                    ->money('BRL')
                    ->getStateUsing(fn ($record) => $record->amount - $record->paid_amount)
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->using(fn ($query) => $query->sum('amount') - $query->sum('paid_amount'))
                            ->money('BRL')
                            ->label('Total Restante:'),
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'danger' => 'overdue',
                        'info' => 'partial',
                        'success' => 'paid',
                        'secondary' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'overdue' => 'Em Atraso',
                        'partial' => 'Parcial',
                        'paid' => 'Pago',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->status === 'paid') return 'success';
                        return $record->due_date < now() ? 'danger' : 'primary';
                    }),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Data Pagamento')
                    ->date('d/m/Y')
                    ->placeholder('Não pago')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expenseCategory.name')
                    ->label('Categoria')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendente',
                        'overdue' => 'Em Atraso',
                        'partial' => 'Parcial',
                        'paid' => 'Pago',
                        'cancelled' => 'Cancelado',
                    ]),

                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('due_from')
                            ->label('Vencimento de'),
                        DatePicker::make('due_until')
                            ->label('Vencimento até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('due_date', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        $query = AccountsPayable::query()->with(['supplier', 'expenseCategory']);

        // Aplicar filtros
        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        if ($this->supplier_id) {
            $query->where('supplier_id', $this->supplier_id);
        }

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }

        return $query;
    }

    protected function getViewData(): array
    {
        $query = $this->getTableQuery();

        return [
            'total_accounts' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'total_paid' => $query->sum('paid_amount'),
            'total_remaining' => $query->sum('amount') - $query->sum('paid_amount'),
            'overdue_count' => $query->where('status', 'overdue')->count(),
            'pending_count' => $query->where('status', 'pending')->count(),
        ];
    }

    protected function exportToPDF()
    {
        // Implementar exportação PDF
        // Pode usar DomPDF ou similar
        session()->flash('success', 'Funcionalidade de exportação PDF em desenvolvimento');
    }

    protected function exportToExcel()
    {
        // Implementar exportação Excel
        // Pode usar Laravel Excel
        session()->flash('success', 'Funcionalidade de exportação Excel em desenvolvimento');
    }
}