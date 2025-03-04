<?php

namespace App\Filament\Exports;

use App\Models\Ticket;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TicketExporter extends Exporter
{
    protected static ?string $model = Ticket::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('owner.name'),
            ExportColumn::make('title'),
            ExportColumn::make('description'),
            ExportColumn::make('priority.name'),
            ExportColumn::make('unit.name'),
            ExportColumn::make('problemCategory.name'),
            ExportColumn::make('ticketStatus.name'),
            ExportColumn::make('responsible.name'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('approved_at'),
            ExportColumn::make('solved_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your ticket export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
