<?php

namespace App\Filament\Resources;

use App\Enums\UserRoleEnums;
use App\Filament\Resources\FilamentDepartmentResource\Pages;
use App\Filament\Resources\FilamentDepartmentResource\RelationManagers;
use App\Models\Department;
use App\Models\FilamentDepartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Illuminate\Support\Str;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class FilamentDepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->live(onBlur: true)->required()
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required(),
                Checkbox::make('is_active')->default(false),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
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
           RelationManagers\CategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFilamentDepartments::route('/'),
            'create' => Pages\CreateFilamentDepartment::route('/create'),
            'edit' => Pages\EditFilamentDepartment::route('/{record}/edit'),

        ];
    }

    public static function canViewAny(): bool
    {
        $user = filament()->auth()->user();

        return $user && $user->hasRole(UserRoleEnums::Admin);
    }
}
