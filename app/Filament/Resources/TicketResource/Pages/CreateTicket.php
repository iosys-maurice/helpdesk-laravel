<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    /**
     * Lengkapi data sebelum disimpan ke database.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] = auth()->id();
        $data['ticket_statuses_id'] = 1;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->record;
        $adminUnits = User::query()
            ->whereHas('roles', function ($query) use ($record) {
                $query->where('name', 'Admin Unit')
                    ->where('unit_id', $record->unit_id);
            })
            ->get();
        Notification::make()
            ->info()
            ->title(__('There is a new ticket that needs to be handled'))
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->url(route('filament.admin.resources.tickets.view', ['record' => $record->id]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($adminUnits);

        return Notification::make()
            ->success()
            ->title(__('Ticket has been created'));
    }
}
