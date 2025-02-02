<?php

namespace App\Filament\Resources\FilamentProductResource\Pages;

use App\Filament\Resources\FilamentProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFilamentProduct extends CreateRecord
{
    protected static string $resource = FilamentProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data):array
    {
       $data['created_by'] = auth()->id();
       $data['updated_by'] = auth()->id();

       return parent::mutateFormDataBeforeCreate($data);
    }
}
