<?php

namespace App\Filament\Widgets;

use App\Models\TicketStatus;
use Filament\Widgets\ChartWidget;

class TicketStatuses extends ChartWidget
{
    protected static ?string $heading = 'Ticket Statuses';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    protected static string $color = 'success';

    protected function getData(): array
    {
        $ticketStatuses = TicketStatus::withCount('tickets')->get();

        return [
            'labels' => $ticketStatuses->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Tickets',
                    'data' => $ticketStatuses->pluck('tickets_count')->toArray(),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
