<?php

namespace App\Filament\Anggota\Resources\TagihanSayaResource\Pages;

use App\Filament\Anggota\Resources\TagihanSayaResource;
use App\Models\TagihanAnggota;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ListTagihanSayas extends ListRecords
{
    protected static string $resource = TagihanSayaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableFiltersLayout(): FiltersLayout
    {
        return FiltersLayout::AboveContent;
    }

    protected function getTableEmptyStateHeading(): string
    {
        return 'Tidak ada tagihan';
    }

    protected function getTableEmptyStateDescription(): string
    {
        return 'Anda tidak memiliki tagihan yang perlu dibayarkan saat ini.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-receipt-percent';
    }

    protected function getTableQuery(): Builder
    {

        return TagihanAnggota::query()
            ->where('user_id', Auth::id());
    }


    protected function getTableFiltersFormData(): array
    {
        return [
            'periode' => now()->format('Y-m'),
        ];
    }


    protected function getTableDescription(): ?string
    {
        $periode = request()->get('tableFilters.periode');

        if ($periode) {
            $date = Carbon::createFromFormat('Y-m', $periode)->format('F Y');
            return 'Tagihan untuk periode: ' . $date;
        }

        return 'Semua tagihan Anda';
    }
}
