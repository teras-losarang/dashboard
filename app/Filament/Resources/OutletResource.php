<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutletResource\Pages;
use App\Filament\Resources\OutletResource\RelationManagers;
use App\Models\Outlet;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;

class OutletResource extends Resource
{
    protected static ?string $model = Outlet::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Shop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make("Details")->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name', function ($query) {
                                $query->where('hidden', 0);
                                $query->whereDoesntHave('outlet');
                            })
                            ->label('Customer')
                            ->required()
                            ->visibleOn('create'),
                        TextInput::make('name')
                            ->label('Outlet Name')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('latitude')
                            ->required()
                            ->maxLength(15),
                        TextInput::make('longitude')
                            ->required()
                            ->maxLength(15),
                        Textarea::make('address')
                            ->label('Address')
                            ->required()
                            ->maxLength(200),
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->maxLength(200),
                    ]),
                    Step::make("Thumbnail")->schema([
                        FileUpload::make('images')
                            ->directory('outlets')
                            ->multiple()
                            ->image()
                            ->maxSize(2048)
                            ->minFiles(1)
                            ->maxFiles(3)
                            ->label('Upload Image')
                            ->panelLayout("grid")
                            ->required(fn($record) => $record === null)
                    ]),
                    Step::make("Operational Hour")->schema([
                        Repeater::make('operational_hour')
                            ->label('')
                            ->schema([
                                TextInput::make('day')
                                    ->label('Day')
                                    ->required()
                                    ->readOnly(),
                                TimePicker::make('open_time')
                                    ->label('Open Time')
                                    ->required(),
                                TimePicker::make('close_time')
                                    ->label('Close Time')
                                    ->required(),
                            ])
                            ->minItems(7)
                            ->maxItems(7)
                            ->columns(3)
                            ->default([
                                ['day' => 'Senin', 'open_time' => '09:00', 'close_time' => '17:00'],
                                ['day' => 'Selasa', 'open_time' => '09:00', 'close_time' => '17:00'],
                                ['day' => 'Rabu', 'open_time' => '09:00', 'close_time' => '17:00'],
                                ['day' => 'Kamis', 'open_time' => '09:00', 'close_time' => '17:00'],
                                ['day' => 'Jumat', 'open_time' => '09:00', 'close_time' => '17:00'],
                                ['day' => 'Sabtu', 'open_time' => '09:00', 'close_time' => '17:00'],
                                ['day' => 'Minggu', 'open_time' => '09:00', 'close_time' => '17:00'],
                            ])
                            ->deletable(false),
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
                ImageColumn::make('images')->label('Thumbnail')->stacked()->getStateUsing(fn($record) => json_decode($record->images, true)),
                TextColumn::make('name'),
                TextColumn::make('user.name')->label('Customer'),
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
            'index' => Pages\ListOutlets::route('/'),
            'create' => Pages\CreateOutlet::route('/create'),
            'edit' => Pages\EditOutlet::route('/{record}/edit'),
        ];
    }
}
