<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use App\Models\TicketStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatusOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tickets = Ticket::all();
        return [
            Stat::make('Total Tickets', $tickets->count())
                ->icon('heroicon-o-ticket')
                ->color('default')
                ->description('Total number of tickets'),
            Stat::make('Total Tickets Open ', $tickets->where('ticket_statuses_id', TicketStatus::OPEN)->count())
                ->icon('heroicon-o-folder-open')
                ->color('danger')
                ->description('Tickets that are open'),
            Stat::make('Total Tickets In Progress', $tickets->whereNotIn('ticket_statuses_id', [TicketStatus::OPEN, TicketStatus::CLOSED])->count())
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->description('Tickets that are in progress'),
            Stat::make('Total Tickets Closed', $tickets->where('ticket_statuses_id', TicketStatus::CLOSED)->count())
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description('Tickets that are closed'),
        ];
    }
}
