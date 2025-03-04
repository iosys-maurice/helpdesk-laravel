<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\TicketStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatusOverviewStaffUnit extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tickets = Ticket::where('responsible_id', auth()->id())->get();

        return [
            Stat::make('Total Tickets', $tickets->count())
                ->icon('heroicon-o-ticket')
                ->color('default')
                ->description('Total number of tickets your responsibility'),
            Stat::make('Total Tickets Assigned', $tickets->whereNotIn('ticket_statuses_id', [TicketStatus::OPEN, TicketStatus::CLOSED])->count())
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->description('Tickets that are assigned to You'),
            Stat::make('Total Tickets Closed', $tickets->where('ticket_statuses_id', TicketStatus::CLOSED)->count())
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description('Tickets that are closed'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['Staff Unit']);
    }
}
