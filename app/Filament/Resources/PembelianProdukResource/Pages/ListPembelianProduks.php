<?php

namespace App\Filament\Resources\PembelianProdukResource\Pages;

use App\Filament\Resources\PembelianProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPembelianProduks extends ListRecords
{
    protected static string $resource = PembelianProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->badge($this->getTabBadgeCount('semua')),
            'pending' => Tab::make('Menunggu')
                ->badge($this->getTabBadgeCount('pending'))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending')),
            'diproses' => Tab::make('Diproses')
                ->badge($this->getTabBadgeCount('diproses'))
                ->badgeColor('primary')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'diproses')),
            'dikirim' => Tab::make('Dikirim')
                ->badge($this->getTabBadgeCount('dikirim'))
                ->badgeColor('info')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'dikirim')),
            'selesai' => Tab::make('Selesai')
                ->badge($this->getTabBadgeCount('selesai'))
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'selesai')),
            'dibatalkan' => Tab::make('Dibatalkan')
                ->badge($this->getTabBadgeCount('dibatalkan'))
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'dibatalkan')),
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
