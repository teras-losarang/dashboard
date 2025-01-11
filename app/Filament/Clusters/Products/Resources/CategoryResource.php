<?php

namespace App\Filament\Clusters\Products\Resources;

use App\Enums\CategoryDirectionEnum;
use App\Enums\CategoryTypeEnum;
use App\Filament\Clusters\Products;
use App\Filament\Clusters\Products\Resources\CategoryResource\Pages;
use App\Filament\Clusters\Products\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $cluster = Products::class;

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(true)->maxLength(50),
                TextInput::make('description')->required(true)->maxLength(150),
                Select::make('type')->options(CategoryTypeEnum::all())->required()->live(),
                FileUpload::make('image')->required(fn($record) => $record === null)
                    ->directory('categories')
                    ->image()
                    ->maxSize(2048),
                ToggleButtons::make("enable_home")->label("Appears on the Home Page?")->boolean()->grouped()->icons([
                    true => "heroicon-o-check",
                    false => "heroicon-o-x-mark",
                ])->required()->default(false)->live()->columnSpanFull()->visible(fn(callable $get) => in_array($get('type'), [CategoryTypeEnum::MENU, CategoryTypeEnum::DEFAULT])),
                Select::make('direction')->label('Format Layout')->options(CategoryDirectionEnum::all())->required()->visible(fn(callable $get) => $get('enable_home')),
                TextInput::make('per_page')->label('Total Product')->required(true)->minLength(0)->numeric()->maxLength(10)->default(0)->visible(fn(callable $get) => $get('enable_home')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")->description(fn($record): String => Str::limit($record->description, 50, '...'))->searchable(),
                ImageColumn::make("image")->circular(),
                ToggleColumn::make("status")->label("Status")->afterStateUpdated(function ($state, $record) {
                    Notification::make()
                        ->title('Update status successfully')
                        ->success()
                        ->send();
                }),
            ])
            ->filters([
                SelectFilter::make("type")->options(CategoryTypeEnum::all())
            ])
            ->actions([
                Tables\Actions\Action::make('sort-up')->label('Up')->icon('heroicon-o-chevron-up')->color('info')->disabled(function ($record) {
                    return $record->sort == 1;
                })->action(function ($record) {
                    DB::transaction(function () use ($record) {
                        $previous = Category::query()
                            ->where('sort', $record->sort - 1)
                            ->first();

                        if ($previous) {
                            $previous->update(['sort' => $record->sort]);
                            $record->update(['sort' => $record->sort - 1]);
                        }

                        Notification::make()
                            ->title('Update sort change up successfully')
                            ->success()
                            ->send();
                    });
                }),
                Tables\Actions\Action::make('sort-down')->label('Down')->icon('heroicon-o-chevron-down')->color('info')->disabled(function ($record) {
                    $lastSort = Category::query()->max('sort');
                    return $record->sort == $lastSort;
                })->action(function ($record) {
                    DB::transaction(function () use ($record) {
                        $next = Category::query()
                            ->where('sort', $record->sort + 1)
                            ->first();

                        if ($next) {
                            $next->update(['sort' => $record->sort]);
                            $record->update(['sort' => $record->sort + 1]);
                        }

                        Notification::make()
                            ->title('Update sort change down successfully')
                            ->success()
                            ->send();
                    });
                }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort', 'asc')
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
