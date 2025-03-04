<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTicketTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ticket::query()
                    ->where('owner_id', auth()->id())
                    ->latest()
                    ->limit(5)
            )
            ->recordUrl(fn (Ticket $record) => route('filament.admin.resources.tickets.view', $record))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->description(fn (Ticket $record) => $record->created_at->diffForHumans())
                    ->translateLabel()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\IconColumn::make('approved_at')
                    ->default('-')
                    ->label(__('Approved'))
                    ->icon(fn (Ticket $record) => $record->approved_at == null ? 'heroicon-o-x-mark' : 'heroicon-o-check-circle'),
                Tables\Columns\TextColumn::make('ticketStatus.name')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make(__('Create Ticket'))
                    ->url(route('filament.admin.resources.tickets.create'))
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->emptyStateActions([
                Action::make('create')
                    ->label(__('Create Ticket'))
                    ->url(route('filament.admin.resources.tickets.create'))
                    ->icon('heroicon-o-plus-circle')
                    ->button(),
            ])
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return ! auth()->user()->hasAnyRole(['Super Admin', 'Admin Unit', 'Staff Unit']);
    }
}
