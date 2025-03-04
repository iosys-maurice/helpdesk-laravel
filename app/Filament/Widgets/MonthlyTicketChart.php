<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class MonthlyTicketChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Ticket Chart';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $unitId = auth()->user()->unit_id;

        $tickets = Ticket::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->when(auth()->user()->hasRole('Admin Unit'), function ($query) use ($unitId) {
                $query->where('unit_id', $unitId);
            })
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $tickets->pluck('month')->toArray(),
            'datasets' => [
                [
                    'label' => 'Tickets',
                    'data' => $tickets->pluck('count')->toArray(),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['Super Admin', 'Admin Unit']);
    }
}
