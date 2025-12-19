<?php

namespace App\Filament\Resources\ShipmentResource\Pages;

use App\Filament\Resources\ShipmentResource;
use App\Models\Shipment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListShipments extends ListRecords
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة شحنة جديدة'),
        ];
    }

    public function getTabs(): array
    {
        $user = Auth::user();

        $getBadge = function (string $status, ?string $subStatus = null, ?string $location = null) use ($user) {
            $query = Shipment::query()->where('status', $status);
            if ($subStatus) $query->where('sub_status', $subStatus);
            if ($location) $query->where('current_location', $location);
            if ($user && $user->branch_id) $query->where('branch_id', $user->branch_id);
            return $query->count();
        };

        return [
            'all' => Tab::make('الكل')->label('كل الشحنات'),

            'saved' => Tab::make('محفوظة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Shipment::STATUS_SAVED))
                ->icon('heroicon-m-pencil-square')
                ->badge($getBadge(Shipment::STATUS_SAVED)),

            'in_stock' => Tab::make('في المخزن')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', Shipment::STATUS_IN_PROGRESS)
                    ->where('current_location', Shipment::LOCATION_BRANCH)
                    ->whereIn('sub_status', [Shipment::SUB_IN_STOCK, Shipment::SUB_DEFERRED]))
                ->icon('heroicon-m-building-storefront')
                ->badge($getBadge(Shipment::STATUS_IN_PROGRESS, Shipment::SUB_IN_STOCK, Shipment::LOCATION_BRANCH))
                ->badgeColor('info'),

            'assigned' => Tab::make('معينة للمندوب')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', Shipment::STATUS_IN_PROGRESS)
                    ->where('sub_status', Shipment::SUB_ASSIGNED))
                ->icon('heroicon-m-user-plus')
                ->badge($getBadge(Shipment::STATUS_IN_PROGRESS, Shipment::SUB_ASSIGNED)),

            'with_courier' => Tab::make('مع المندوب')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('status', Shipment::STATUS_IN_PROGRESS)
                    ->where('sub_status', Shipment::SUB_WITH_COURIER))
                ->icon('heroicon-m-truck')
                ->badge($getBadge(Shipment::STATUS_IN_PROGRESS, Shipment::SUB_WITH_COURIER))
                ->badgeColor('warning'),

            'delivered' => Tab::make('تم التسليم')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Shipment::STATUS_DELIVERED))
                ->icon('heroicon-m-check-badge')
                ->badge($getBadge(Shipment::STATUS_DELIVERED))
                ->badgeColor('success'),

            'returned' => Tab::make('مرتجعات')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Shipment::STATUS_RETURNED))
                ->icon('heroicon-m-arrow-path')
                ->badge($getBadge(Shipment::STATUS_RETURNED))
                ->badgeColor('danger'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
