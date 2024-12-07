<?php

namespace App\Filament\Clusters\Products\Resources;

use App\Filament\Clusters\Products;
use App\Filament\Clusters\Products\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $cluster = Products::class;

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make("Details")->schema([
                        Grid::make(2)->schema([
                            Select::make("outlet_id")->label("Outlet")->relationship("outlet", "name")->required()->preload()->searchable()->live()->columnSpan(1),
                            Select::make("category")->multiple()->relationship("categories", "name")->required()->preload()->searchable()->live()->columnSpan(1),
                            TextInput::make("name")->required()->maxLength(150)->columnSpan(1),
                            TextInput::make("price")->prefix("Rp")->label("Price")->required()->numeric()->minValue(0)->columnSpan(1),
                            Textarea::make("description")->required()->maxLength(300)->columnSpan(2)
                        ])
                    ]),
                    Step::make("Thumbnails")->schema([
                        FileUpload::make('images')
                            ->directory('products')
                            ->multiple()
                            ->image()
                            ->maxSize(2048)
                            ->minFiles(1)
                            ->maxFiles(5)
                            ->label('Upload Image')
                            ->panelLayout("grid")
                            ->required(fn($record) => $record === null)
                    ]),
                    Step::make("Variants")->schema([
                        ToggleButtons::make("enable_variant")->boolean()->grouped()->icons([
                            true => "heroicon-o-check",
                            false => "heroicon-o-x-mark",
                        ])->required()->default(true)->live(),
                        Repeater::make("variants")->schema([
                            Grid::make(3)->schema([
                                TextInput::make("name")->label("Variant Name")->required()->maxLength(100),
                                TextInput::make("price")->label("Variant Price")->required()->numeric()->minValue(0),
                                ToggleButtons::make("status")->boolean()->options([
                                    true => "Active",
                                    false => "Non Active",
                                ])->grouped()->default(true),
                            ])
                        ])->columns(1)->minItems(1)->label("Variants")->visible(fn(callable $get) => $get('enable_variant'))
                    ])
                ])
                    ->columnSpanFull()
                    ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')->label('Images')->stacked()->getStateUsing(fn($record) => json_decode($record->images, true)),
                TextColumn::make("name")->description(function (Product $product) {
                    return new HtmlString("
                        Outlet: {$product->outlet->name} <br>
                        Price: " . Number::currency($product->price, 'IDR', 'id') . "
                    ");
                })->html(),
                ToggleColumn::make("status")->label("Status")->afterStateUpdated(function ($state, $record) {
                    Notification::make()
                        ->title('Update status successfully')
                        ->success()
                        ->send();
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
