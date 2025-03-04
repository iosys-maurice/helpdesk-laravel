<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers\CommentsRelationManager;
use App\Models\Priority;
use App\Models\ProblemCategory;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\Unit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Forms\Components\Select::make('unit_id')
                        ->label(__('Work Unit'))
                        ->options(Unit::all()
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $unit = Unit::find($state);
                            if ($unit) {
                                $problemCategoryId = (int) $get('problem_category_id');
                                if ($problemCategoryId && $problemCategory = ProblemCategory::find($problemCategoryId)) {
                                    if ($problemCategory->unit_id !== $unit->id) {
                                        $set('problem_category_id', null);
                                    }
                                }
                            }
                        })
                        ->reactive(),

                    Forms\Components\Select::make('problem_category_id')
                        ->label(__('Problem Category'))
                        ->options(function (callable $get, callable $set) {
                            $unit = Unit::find($get('unit_id'));
                            if ($unit) {
                                return $unit->problemCategories->pluck('name', 'id');
                            }
                        })
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label(__('Title'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpan([
                            'sm' => 2,
                        ]),

                    Forms\Components\RichEditor::make('description')
                        ->label(__('Description'))
                        ->required()
                        ->maxLength(65535)
                        ->columnSpan([
                            'sm' => 2,
                        ]),

                    Forms\Components\Placeholder::make('approved_at')
                        ->translateLabel()
                        ->hiddenOn('create')
                        ->content(fn (
                            ?Ticket $record,
                        ): string => $record->approved_at ? $record->approved_at->diffForHumans() : '-'),

                    Forms\Components\Placeholder::make('solved_at')
                        ->translateLabel()
                        ->hiddenOn('create')
                        ->content(fn (
                            ?Ticket $record,
                        ): string => $record->solved_at ? $record->solved_at->diffForHumans() : '-'),
                ])->columns([
                    'sm' => 2,
                ])->columnSpan(2),

                Card::make()->schema([
                    Forms\Components\Select::make('priority_id')
                        ->label(__('Priority'))
                        ->options(Priority::all()
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\Placeholder::make('status')
                        ->translateLabel()
                        ->content(fn (
                            ?Ticket $record,
                        ): string => $record ? $record->ticketStatus->name : '-')
                        ->visible(fn () => ! auth()
                            ->user()
                            ->hasAnyRole(['Super Admin', 'Admin Unit', 'Staff Unit'])),

                    Forms\Components\Select::make('ticket_statuses_id')
                        ->label(__('Status'))
                        ->options(TicketStatus::all()
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->hiddenOn('create')
                        ->hidden(
                            fn () => ! auth()
                                ->user()
                                ->hasAnyRole(['Super Admin', 'Admin Unit', 'Staff Unit']),
                        ),

                    Forms\Components\Select::make('responsible_id')
                        ->label(__('Responsible'))
                        ->options(User::ByRole()
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->hiddenOn('create')
                        ->hidden(
                            fn () => ! auth()
                                ->user()
                                ->hasAnyRole(['Super Admin', 'Admin Unit']),
                        ),

                    Forms\Components\Placeholder::make('created_at')
                        ->translateLabel()
                        ->content(fn (
                            ?Ticket $record,
                        ): string => $record ? $record->created_at->diffForHumans() : '-'),

                    Forms\Components\Placeholder::make('updated_at')
                        ->translateLabel()
                        ->content(fn (
                            ?Ticket $record,
                        ): string => $record ? $record->updated_at->diffForHumans() : '-'),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner.name')
                    ->translateLabel()
                    ->searchable()
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('title')
                    ->translateLabel()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->translateLabel()
                    ->sortable()
                    ->since()
                    ->tooltip(fn (Ticket $record) => $record->created_at)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('problemCategory.name')
                    ->searchable()
                    ->label(__('Problem Category'))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('approved_at')
                    ->default('-')
                    ->label(__('Approved'))
                    ->icon(fn (Ticket $record) => $record->approved_at == null ? 'heroicon-o-x-mark' : 'heroicon-o-check-circle'),
                Tables\Columns\TextColumn::make('ticketStatus.name')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->options(Unit::all()
                        ->pluck('name', 'id'))
                    ->label(__('Work Unit'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('problem_category_id')
                    ->options(ProblemCategory::all()
                        ->pluck('name', 'id'))
                    ->label(__('Problem Category'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('priority_id')
                    ->options(Priority::all()
                        ->pluck('name', 'id'))
                    ->multiple()
                    ->label(__('Priority'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('ticket_statuses_id')
                    ->options(TicketStatus::all()
                        ->pluck('name', 'id'))
                    ->multiple()
                    ->label(__('Status'))
                    ->searchable(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
            ->persistFiltersInSession();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make()
                    ->schema(
                        [
                            Section::make(fn (Ticket $record) => $record->title)
                                ->description(fn (Ticket $record) => __('Created at').' '.$record->created_at->diffForHumans().' oleh '.$record->owner->name)
                                ->schema([
                                    TextEntry::make('unit.name')
                                        ->label(__('Work Unit'))
                                        ->columnSpan(1),
                                    TextEntry::make('problemCategory.name')
                                        ->label(__('Problem Category'))
                                        ->columnSpan(1),
                                    TextEntry::make('description')
                                        ->translateLabel()
                                        ->markdown()
                                        ->prose()
                                        ->columnSpanFull(),
                                    TextEntry::make('responsible.name')
                                        ->label(__('Responsible'))
                                        ->badge()
                                        ->columnSpan(1)
                                        ->visible(fn (Ticket $record) => $record->responsible_id),
                                ])->columnSpan(2)
                                ->columns(2)
                                ->footerActions([
                                    Action::make('determine-pic')
                                        ->label(fn (Ticket $record) => $record->responsible_id ? __('Change the PIC') : __('Set PIC'))
                                        ->icon('heroicon-o-user-plus')
                                        ->form([
                                            Select::make('responsible_id')
                                                ->label(fn (Ticket $record) => $record->responsible_id ? __('Change the PIC') : __('Set PIC'))
                                                ->options(function ($record) {
                                                    return User::query()
                                                        ->whereHas('roles', function ($query) use ($record) {
                                                            $query->where('name', 'Staff Unit')
                                                                ->where('unit_id', $record->unit_id);
                                                        })
                                                        ->pluck('name', 'id');
                                                })
                                                ->preload()
                                                ->searchable()
                                                ->required(),
                                        ])->action(function (array $data, Ticket $record): void {
                                            if ($record->approved_at == null) {
                                                $record->approved_at = now();
                                            }

                                            $record->responsible_id = $data['responsible_id'];
                                            $record->ticket_statuses_id = TicketStatus::ASSIGNED;
                                            $record->save();

                                            Notification::make()
                                                ->title(__('The person in charge has changed'))
                                                ->success()
                                                ->send();

                                            Notification::make()
                                                ->title(__('There is a new ticket that is your responsibility.'))
                                                ->info()
                                                ->actions([
                                                    \Filament\Notifications\Actions\Action::make('view')
                                                        ->url(route('filament.admin.resources.tickets.view', ['record' => $record->id]))
                                                        ->markAsRead(),
                                                ])
                                                ->sendToDatabase(User::find($data['responsible_id']));
                                        })
                                        ->visible(function (Ticket $record) {

                                            if (auth()->user()->hasRole('Super Admin')) {
                                                return true;
                                            }

                                            return auth()->user()->hasRole('Admin Unit') && auth()->user()->unit_id == $record->unit_id;
                                        }),
                                ]),

                            Section::make(__('Additional Information'))
                                ->schema([
                                    TextEntry::make('priority.name')
                                        ->translateLabel()
                                        ->badge(),
                                    TextEntry::make('ticketStatus.name')
                                        ->translateLabel()
                                        ->badge(),
                                    TextEntry::make('updated_at')
                                        ->translateLabel()
                                        ->dateTime(),
                                    TextEntry::make('approved_at')
                                        ->translateLabel()
                                        ->dateTime()
                                        ->columnSpan(1),
                                    TextEntry::make('solved_at')
                                        ->translateLabel()
                                        ->dateTime()
                                        ->columnSpan(1),
                                ])->columnSpan(1),
                        ]
                    )
                    ->columns(3),
            ])
            ->columns(1);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    /**
     * Display tickets based on each role.
     *
     * If it is a Super Admin, then display all tickets.
     * If it is a Admin Unit, then display tickets based on the tickets they have created and their unit id.
     * If it is a Staff Unit, then display tickets based on the tickets they have created and the tickets assigned to them.
     * If it is a Regular User, then display tickets based on the tickets they have created.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                // Display all tickets to Super Admin
                if (auth()->user()->hasRole('Super Admin')) {
                    return;
                }

                if (auth()->user()->hasRole('Admin Unit')) {
                    $query->where('tickets.unit_id', auth()->user()->unit_id)->orWhere('tickets.owner_id', auth()->id());
                } elseif (auth()->user()->hasRole('Staff Unit')) {
                    $query->where('tickets.responsible_id', auth()->id())->orWhere('tickets.owner_id', auth()->id());
                } else {
                    $query->where('tickets.owner_id', auth()->id());
                }
            })
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tickets');
    }
}
