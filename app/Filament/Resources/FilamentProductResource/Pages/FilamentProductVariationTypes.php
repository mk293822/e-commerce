<?php

namespace App\Filament\Resources\FilamentProductResource\Pages;

use App\Enums\ProductVariationTypeEnum;
use App\Filament\Resources\FilamentProductResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class FilamentProductVariationTypes extends EditRecord
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
                Repeater::make('variationType')
                ->label(false)
                ->relationship()->collapsible()
                ->defaultItems(1)
                ->addActionLabel('Add New Variation Type')
                ->columns(2)
                ->columnSpan(2)
                ->schema([
                    TextInput::make('name')->required(),
                    Select::make('type')
                        ->options(ProductVariationTypeEnum::labels())
                        ->required(),
                    Repeater::make('variationTypeOption')
                    ->relationship()
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')->columnSpan(2)->required(),
                        SpatieMediaLibraryFileUpload::make('images')
                        ->image()
                        ->multiple()
                        ->openable()
                        ->reorderable()
                        ->panelLayout('grid')
                        ->collection('images')
                        ->appendFiles()
                        ->appendFiles()
                        ->preserveFilenames()
                        ->columnSpan(2)
                    ])->columnSpan(2)
                ])->collapsible()->collapsed()
            ]);
    }

}
