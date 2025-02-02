<?php

namespace App\Filament\Resources;

use App\Enums\ProductStatusEnums;
use App\Enums\SubNavigationPositionEnum;
use App\Enums\UserRoleEnums;
use App\Filament\Resources\FilamentProductResource\Pages;
use App\Filament\Resources\FilamentProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Department;
use App\Models\FilamentProduct;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;

class FilamentProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->forVendor();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()->schema([
                    TextInput::make('name')
                        ->live(onBlur: true)
                        ->required()
                        ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')->required(),
                    Select::make('department_id')
                        ->relationship('department', 'name', fn ($query) => $query->where('is_active', true))
                        ->preload()
                        ->label(__('Department'))
                        ->required()
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('category_id', null)),
                    Select::make('category_id')
                        ->relationship(
                            name:'category',
                            titleAttribute: 'name',
                            modifyQueryUsing: function (Builder $query, callable $get){
                                $department_id = $get('department_id');
                                if($department_id){
                                    $query->where('department_id', $department_id)->where('is_active', true);
                                } else {
                                    $query->whereNull('department_id');
                                }
                            })
                        ->preload()
                        ->label(__('Categories'))
                        ->required()
                        ->searchable(),
                    TextInput::make('price')->numeric()->required(),
                    TextInput::make('quantity')->integer()->required(),
                    Select::make('status')->options(ProductStatusEnums::labels())->default(ProductStatusEnums::Draft->value)->required(),
                    TextInput::make('discount')->numeric()->nullable(),
                ])->columns(2),
                Forms\Components\RichEditor::make('description')
                ->required()
                ->toolbarButtons([
                    'blockquote',
                    'bold',
                    'bulletList',
                    'h2',
                    'h3',
                    'italic',
                    'link',
                    'orderList',
                    'redo',
                    'strike',
                    'underline',
                    'undo',
                    'table',
                    'blockquote',
                    'code',
                    'subscript',
                    'superscript',
                    'alignment',
                    'fontSize',
                    'color',
                ])
                ->extraAttributes([
                    'x-data' => '{ config: {
                     plugins: "table link image code textcolor",
                     toolbar: "bold italic underline | table link image | fontSize forecolor backcolor | undo redo"
                 }}',
                ]),
                Forms\Components\Section::make('SEO')
                    ->collapsible()
                ->schema([
                    Forms\Components\TextInput::make('meta_title'),
                    Forms\Components\TextInput::make('meta_description'),
                ])->columns(2),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('status')->badge()->colors(ProductStatusEnums::colors()),
                TextColumn::make('department.name')->sortable()->searchable(),
                TextColumn::make('category.name')->sortable()->searchable(),
                TextColumn::make('created_at')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(ProductStatusEnums::labels()),
                Tables\Filters\SelectFilter::make('department_id')->relationship('department', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFilamentProducts::route('/'),
            'create' => Pages\CreateFilamentProduct::route('/create'),
            'edit' => Pages\EditFilamentProduct::route('/{record}/edit'),
            'images'=> Pages\FilamentProductImages::route('/{record}/images'),
            'variation-types'=>Pages\FilamentProductVariationTypes::route('/{record}/variation-types'),
            'variation-type-options'=>Pages\FilamentProductVariationTypeOptions::route('/{record}/variation-type-options'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
           Pages\EditFilamentProduct::class,
            Pages\FilamentProductImages::class,
            Pages\FilamentProductVariationTypes::class,
            Pages\FilamentProductVariationTypeOptions::class
        ]);
    }

    public static function canViewAny(): bool
    {
        $user = filament()->auth()->user();
        return $user && $user->hasRole(UserRoleEnums::Vendor);
    }
}
