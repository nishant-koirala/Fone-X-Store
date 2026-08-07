<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TradeInValuationResource\Pages;
use App\Models\TradeInValuation;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TradeInValuationResource extends Resource
{
    protected static ?string $model = TradeInValuation::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Trade-In Valuations';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->nullable(),

                Forms\Components\TextInput::make('device_brand')
                    ->required(),

                Forms\Components\TextInput::make('device_model')
                    ->required(),

                Forms\Components\TextInput::make('estimated_value')
                    ->numeric()
                    ->prefix('Rs ')
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'reviewed' => 'Valuation Reviewed',
                        'completed' => 'Trade-In Completed',
                    ])
                    ->required()
                    ->default('pending'),

                Forms\Components\Textarea::make('condition_description')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->default('Guest')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('device_brand')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('device_model')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->prefix('Rs ')
                    ->numeric()
                    ->sortable()
                    ->placeholder('Unassessed'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reviewed' => 'info',
                        'completed' => 'success',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'reviewed' => 'Valuation Reviewed',
                        'completed' => 'Trade-In Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTradeInValuations::route('/'),
            'create' => Pages\CreateTradeInValuation::route('/create'),
            'edit' => Pages\EditTradeInValuation::route('/{record}/edit'),
        ];
    }
}
