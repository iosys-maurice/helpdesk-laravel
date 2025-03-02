<?php

namespace App\Filament\Widgets;

use App\Models\TicketStatus;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TicketOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $ticketStatuses = TicketStatus::select('id', 'name')
            ->whereNotIn('id', [TicketStatus::ON_HOLD, TicketStatus::ESCALATED])
            ->withCount(['tickets'])
            ->get();

        return $ticketStatuses->map(function ($status) {
            return Stat::make($status->name, $status->tickets_count)
                ->icon('heroicon-o-ticket');
        })->toArray();
    }
}
