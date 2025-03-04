<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Exports\TicketExporter;
use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle'),
            \Filament\Actions\ExportAction::make()
                ->exporter(TicketExporter::class)
                ->label(__('Export'))
                ->icon('heroicon-o-arrow-down-circle'),
        ];
    }
}
