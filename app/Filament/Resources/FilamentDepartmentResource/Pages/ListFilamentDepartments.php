<?php

namespace App\Filament\Resources\FilamentDepartmentResource\Pages;

use App\Filament\Resources\FilamentDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilamentDepartments extends ListRecords
{
    protected static string $resource = FilamentDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
