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
        if (auth()->user()->hasRole('Admin Unit')) {
            $unitId = auth()->user()->unit_id;
            $ticketStatuses = TicketStatus::whereHas('tickets', function ($query) use ($unitId) {
                $query->where('unit_id', $unitId);
            })
                ->withCount(['tickets as tickets_count' => function ($query) use ($unitId) {
                    $query->where('unit_id', $unitId);
                }])
                ->get();
        } else {
            $ticketStatuses = TicketStatus::withCount('tickets')->get();
        }

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

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['Super Admin', 'Admin Unit']);
    }
}
