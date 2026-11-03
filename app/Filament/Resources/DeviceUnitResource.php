<?php

namespace App\Filament\Resources;

use App\Enums\DeviceUnitStatusEnum;
use App\Filament\Resources\DeviceUnitResource\Pages;
use App\Models\DeviceUnit;
use App\Models\ProductCondition;
use App\Rules\Imei;
use App\Services\Inventory\UnitInventoryService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
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
                    ->label('Primary IMEI')
                    ->maxLength(25)
                    ->placeholder('e.g. 356938035643809')
                    ->helperText('15-digit unique device identifier with Luhn check digit')
                    ->rule(new Imei)
                    ->rules([
                        fn (?DeviceUnit $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                            if (empty($value)) {
                                return;
                            }
                            $val = trim((string) $value);
                            $query = DeviceUnit::where(function ($q) use ($val) {
                                $q->where('imei', $val)->orWhere('imei2', $val);
                            });
                            if ($record) {
                                $query->where('id', '!=', $record->id);
                            }
                            if ($query->exists()) {
                                $fail('This IMEI is already registered to another device unit.');
                            }
                        },
                    ])
                    ->nullable(),

                Forms\Components\TextInput::make('imei2')
                    ->label('Secondary IMEI (Dual-SIM)')
                    ->maxLength(25)
                    ->placeholder('e.g. 356938035643817')
                    ->helperText('15-digit secondary IMEI (must differ from primary IMEI)')
                    ->different('imei')
                    ->rule(new Imei)
                    ->rules([
                        fn (?DeviceUnit $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                            if (empty($value)) {
                                return;
                            }
                            $val = trim((string) $value);
                            $query = DeviceUnit::where(function ($q) use ($val) {
                                $q->where('imei', $val)->orWhere('imei2', $val);
                            });
                            if ($record) {
                                $query->where('id', '!=', $record->id);
                            }
                            if ($query->exists()) {
                                $fail('This secondary IMEI is already registered to another device unit.');
                            }
                        },
                    ])
                    ->nullable(),

                Forms\Components\TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->maxLength(50)
                    ->nullable(),

                Forms\Components\TextInput::make('battery_health')
                    ->label('Battery Health')
                    ->numeric()
                    ->minValue(0)
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
                Tables\Columns\TextColumn::make('imei2')
                    ->label('IMEI 2')
                    ->searchable()
                    ->copyable()
                    ->default('—')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Action::make('markReturned')
                    ->label('Mark Returned')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (DeviceUnit $record): bool => $record->status === DeviceUnitStatusEnum::SOLD)
                    ->form([
                        Forms\Components\Textarea::make('return_reason')
                            ->label('Return Reason')
                            ->placeholder('e.g. Returned under checking guarantee: battery drain issue')
                            ->required(),
                    ])
                    ->action(function (DeviceUnit $record, array $data): void {
                        app(UnitInventoryService::class)->markReturned($record, $data['return_reason'] ?? null, auth()->id());
                        Notification::make()
                            ->title('Unit Marked as Returned')
                            ->body('Unit has been marked returned. It is not available for sale.')
                            ->warning()
                            ->send();
                    }),

                Action::make('returnToStock')
                    ->label('Return to Stock')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (DeviceUnit $record): bool => $record->status === DeviceUnitStatusEnum::RETURNED)
                    ->requiresConfirmation()
                    ->modalHeading('Return Unit to Sellable Stock')
                    ->modalDescription('This will mark the unit as Available and record +1 stock movement in inventory.')
                    ->action(function (DeviceUnit $record): void {
                        app(UnitInventoryService::class)->returnToStock($record, auth()->id());
                        Notification::make()
                            ->title('Unit Restored to Stock')
                            ->body('Unit is now Available and stock has been incremented by 1.')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make()
                    ->using(fn (DeviceUnit $record) => app(UnitInventoryService::class)->deleteUnit($record, auth()->id())),
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
