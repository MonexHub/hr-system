<?php

namespace App\Filament\Admin\Resources\PerformanceAppraisalResource\Pages;

use App\Filament\Admin\Resources\PerformanceAppraisalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerformanceAppraisals extends ListRecords
{
    protected static string $resource = PerformanceAppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
