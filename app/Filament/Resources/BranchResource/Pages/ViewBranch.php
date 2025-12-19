<?php

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\BranchResource\Widgets\BranchFinancialStats;

class ViewBranch extends ViewRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            BranchFinancialStats::class,
        ];
    }
}
