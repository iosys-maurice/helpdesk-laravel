<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\TicketStatus;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('approve')
                ->label('Approve')
                ->requiresConfirmation()
                ->visible(fn ($record) => ($record->ticket_statuses_id == TicketStatus::OPEN)
                    && (auth()->user()->can('approve', $record))
                    && ($record->approved_at == null))
                ->action(fn ($record) => $record->update(['approved_at' => now()])),
            \Filament\Actions\EditAction::make()
                ->visible(fn ($record) => ($record->ticket_statuses_id == TicketStatus::OPEN) && ($record->owner_id == auth()->id())),
        ];
    }
}
