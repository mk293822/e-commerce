<?php

namespace App\Filament\Resources\FilamentProductResource\Pages;



use App\Filament\Resources\FilamentProductResource;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Resource;
use function Pest\Laravel\delete;

class FilamentProductImages extends EditRecord
{
    protected static string $resource = FilamentProductResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make('delete'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('images')
                    ->image()
                    ->label(false)
                    ->multiple()
                    ->openable()
                    ->panelLayout('grid')
                    ->collection('images')
                    ->reorderable()
                    ->appendFiles()
                    ->preserveFilenames()
                    ->columnSpan(2)
            ])->columns(1);
    }
}
