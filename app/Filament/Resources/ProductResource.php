<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use BackedEnum;
use Illuminate\Support\Facades\Storage;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): ?string { return 'heroicon-o-device-phone-mobile'; }

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory Management';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true),

                        Forms\Components\TextInput::make('brand')
                            ->required(),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('base_price')
                            ->numeric()
                            ->prefix('Rs ')
                            ->required(),

                        Forms\Components\Toggle::make('is_new')
                            ->label('New Condition Available')
                            ->default(true),

                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('current_image_preview')
                            ->label('Current Image')
                            ->content(function ($record) {
                                if ($record && $record->image) {
                                    $url = Storage::disk('public')->url($record->image);
                                    return new \Illuminate\Support\HtmlString(
                                        '<img src="' . $url . '" style="max-height:150px;border-radius:8px;" />'
                                    );
                                }
                                return 'No image uploaded yet.';
                            })
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Upload New Image')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->formatStateUsing(fn () => null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Sellable Product Conditions & Stock')
                    ->description('Specify price & inventory per condition grade (New, Grade A, Grade B, Grade C).')
                    ->schema([
                        Forms\Components\Repeater::make('conditions')
                            ->relationship('conditions')
                            ->schema([
                                Forms\Components\Select::make('grade')
                                    ->options([
                                        'new' => 'New (Brand New)',
                                        'A' => 'Grade A (Excellent Used)',
                                        'B' => 'Grade B (Good Used)',
                                        'C' => 'Grade C (Fair Used)',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('original_price')
                                    ->label('Old Price (Crossed Out)')
                                    ->numeric()
                                    ->prefix('Rs ')
                                    ->nullable(),

                                Forms\Components\TextInput::make('price')
                                    ->label('New/Current Price')
                                    ->numeric()
                                    ->prefix('Rs ')
                                    ->required(),

                                Forms\Components\TextInput::make('quantity_in_stock')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ])
                            ->columns(4)
                            ->defaultItems(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\ImageColumn::make('image')->disk('public'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('brand')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->sortable(),
                Tables\Columns\TextColumn::make('base_price')->money('USD')->sortable(),
                Tables\Columns\IconColumn::make('is_new')->boolean(),
                Tables\Columns\TextColumn::make('conditions_count')
                    ->counts('conditions')
                    ->label('Condition Listings'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_new'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
