<?php

namespace App\Filament\Resources;

use App\Enums\DeviceUnitStatusEnum;
use App\Filament\Resources\DeviceUnitResource\Pages;
use App\Models\DeviceUnit;
use App\Models\ProductCondition;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DeviceUnitResource extends Resource
{
    protected static ?string $model = DeviceUnit::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-device-phone-mobile';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory & Catalog';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    protected static ?string $navigationLabel = 'Device Units (IMEI)';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('product_condition_id')
                    ->label('Product & Grade')
                    ->options(function () {
                        return ProductCondition::with('product')
                            ->get()
                            ->mapWithKeys(function (ProductCondition $cond) {
                                $name = $cond->product ? "{$cond->product->brand} {$cond->product->name}" : "Condition #{$cond->id}";
                                $grade = $cond->grade->value;

                                return [$cond->id => "{$name} (Grade: {$grade}) - Rs {$cond->price}"];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('imei')
                    ->label('IMEI Number')
                    ->maxLength(25)
                    ->placeholder('e.g. 356938035643809')
                    ->helperText('15-digit unique device identifier for NTA / MDMS compliance and warranty verification')
                    ->nullable(),

                Forms\Components\TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->maxLength(50)
                    ->nullable(),

                Forms\Components\TextInput::make('battery_health')
                    ->label('Battery Health')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->suffix('%')
                    ->helperText('Verified battery health percentage')
                    ->nullable(),

                Forms\Components\TextInput::make('cost_price')
                    ->label('Acquisition / Cost Price')
                    ->numeric()
                    ->prefix('Rs ')
                    ->nullable(),

                Forms\Components\Select::make('status')
                    ->options(DeviceUnitStatusEnum::class)
                    ->required()
                    ->default(DeviceUnitStatusEnum::AVAILABLE),

                Forms\Components\Textarea::make('notes')
                    ->label('Cosmetic & Diagnostic Notes')
                    ->placeholder('e.g. Minor bezel hairline scuff, original screen verified')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('imei')
                    ->label('IMEI')
                    ->searchable()
                    ->copyable()
                    ->default('—')
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('productCondition.product.name')
                    ->label('Phone Model')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('productCondition.grade')
                    ->label('Grade')
                    ->badge(),
                Tables\Columns\TextColumn::make('battery_health')
                    ->label('Battery')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}%" : '—')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 85 => 'success',
                        $state >= 80 => 'warning',
                        $state !== null => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('cost_price')
                    ->money('NPR')
                    ->label('Cost')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orderItem.order_id')
                    ->label('Sold On Order')
                    ->formatStateUsing(fn ($state) => $state ? "#{$state}" : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(DeviceUnitStatusEnum::class),
                Tables\Filters\SelectFilter::make('grade')
                    ->options([
                        'new' => 'Brand New',
                        'A' => 'Grade A (Like New)',
                        'B' => 'Grade B (Good)',
                        'C' => 'Grade C (Fair)',
                    ])
                    ->query(fn ($query, array $data) => ! empty($data['value'])
                        ? $query->whereHas('productCondition', fn ($q) => $q->where('grade', $data['value']))
                        : $query),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeviceUnits::route('/'),
            'create' => Pages\CreateDeviceUnit::route('/create'),
            'edit' => Pages\EditDeviceUnit::route('/{record}/edit'),
        ];
    }
}
