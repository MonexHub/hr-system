<?php

namespace App\Filament\Admin\Resources\BenefitResource\Pages;

use App\Filament\Admin\Resources\BenefitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBenefits extends ListRecords
{
    protected static string $resource = BenefitResource::class;

//    protected function getHeaderActions(): array
//    {
//        return [
//            Actions\CreateAction::make(),
//        ];
//    }
}
