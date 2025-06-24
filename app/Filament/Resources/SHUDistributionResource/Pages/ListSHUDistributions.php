<?php

namespace App\Filament\Resources\SHUDistributionResource\Pages;

use App\Filament\Resources\SHUDistributionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSHUDistributions extends ListRecords
{
    protected static string $resource = SHUDistributionResource::class;


    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->badge($this->getTabBadgeCount('semua')),
            'pending' => Tab::make('Pending')
                ->badge($this->getTabBadgeCount('pending'))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending')),
            'dibagikan' => Tab::make('Dibagikan')
                ->badge($this->getTabBadgeCount('dibagikan'))
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'dibagikan')),
            'ditolak' => Tab::make('Ditolak')
                ->badge($this->getTabBadgeCount('ditolak'))
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'ditolak')),
        ];
    }

    private function getTabBadgeCount(string $tab): int
    {
        $query = $this->getTableQuery();

        if ($tab === 'semua') {
            return $query->count();
        }

        return $query->where('status', $tab)->count();
    }
}
