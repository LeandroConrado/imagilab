<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementResource\Pages;
use App\Models\Advertisement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Advertisement Content')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link'
                            ]),

                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->directory('advertisements')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('450'),

                        Forms\Components\TextInput::make('link_url')
                            ->url()
                            ->placeholder('https://example.com'),
                    ]),

                Forms\Components\Section::make('Display Configuration')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'banner' => 'Banner',
                                'popup' => 'Popup',
                                'sidebar' => 'Sidebar',
                                'footer' => 'Footer',
                            ])
                            ->required(),

                        Forms\Components\Select::make('target_audience')
                            ->options([
                                'all' => 'All Visitors',
                                'customers' => 'Customers Only',
                                'visitors' => 'Visitors Only',
                            ])
                            ->default('all')
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Schedule & Analytics')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->helperText('Leave empty for immediate activation'),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->helperText('Leave empty for no expiration'),

                        Forms\Components\TextInput::make('click_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->helperText('Automatically tracked'),

                        Forms\Components\TextInput::make('view_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->helperText('Automatically tracked'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->height(50)
                    ->width(80)
                    ->defaultImageUrl('/images/no-image.png'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'banner' => 'success',
                        'popup' => 'warning',
                        'sidebar' => 'info',
                        'footer' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('target_audience')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all' => 'All Visitors',
                        'customers' => 'Customers Only',
                        'visitors' => 'Visitors Only',
                    }),

                Tables\Columns\TextColumn::make('click_count')
                    ->numeric()
                    ->sortable()
                    ->label('Clicks'),

                Tables\Columns\TextColumn::make('view_count')
                    ->numeric()
                    ->sortable()
                    ->label('Views'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->placeholder('Immediate'),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->placeholder('No expiration'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'banner' => 'Banner',
                        'popup' => 'Popup',
                        'sidebar' => 'Sidebar',
                        'footer' => 'Footer',
                    ]),

                Tables\Filters\SelectFilter::make('target_audience')
                    ->options([
                        'all' => 'All Visitors',
                        'customers' => 'Customers Only',
                        'visitors' => 'Visitors Only',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\Filter::make('active_period')
                    ->label('Currently Active')
                    ->query(fn ($query) => $query
                        ->where('is_active', true)
                        ->where(function($q) {
                            $q->whereNull('start_date')
                                ->orWhere('start_date', '<=', now());
                        })
                        ->where(function($q) {
                            $q->whereNull('end_date')
                                ->orWhere('end_date', '>=', now());
                        })
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('view_stats')
                    ->label('Stats')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading('Advertisement Statistics')
                    ->modalContent(fn (Advertisement $record): string =>
                        "Clicks: {$record->click_count}<br>Views: {$record->view_count}<br>CTR: " .
                        ($record->view_count > 0 ? round(($record->click_count / $record->view_count) * 100, 2) . '%' : '0%')
                    ),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }

    // Badge com contagem de anúncios ativos
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    // Cor do badge baseada em anúncios com performance
    public static function getNavigationBadgeColor(): ?string
    {
        $activeCount = static::getModel()::where('is_active', true)->count();
        $performingCount = static::getModel()::where('is_active', true)
            ->where('click_count', '>', 0)->count();

        if ($performingCount > 0) {
            return 'success'; // Verde se há anúncios com cliques
        } elseif ($activeCount > 0) {
            return 'warning'; // Amarelo se há anúncios ativos sem cliques
        } else {
            return 'gray'; // Cinza se não há anúncios ativos
        }
    }
}
