<?php

namespace App\Filament\Resources\EmergencyRequests\Tables;

use App\Enums\EmergencyStatus;
use App\Models\EmergencyRequest;
use App\Services\EmergencyRequestService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmergencyRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('bloodGroup.code')->label('Blood')->badge()->color('danger'),
                TextColumn::make('hospital_name')->searchable()->limit(30)
                    ->description(fn (EmergencyRequest $r) => $r->generalLocation()),
                TextColumn::make('units_needed')->label('Units'),
                TextColumn::make('needed_at')->dateTime('d M, h:i A')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('user.name')->label('Requester')->searchable(),
                TextColumn::make('responses_count')->label('Responses'),
                TextColumn::make('reports_count')->label('Reports')->badge()->color(fn ($state) => $state ? 'danger' : 'gray'),
                TextColumn::make('notified_count')->label('Notified')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->since()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(EmergencyStatus::class),
                SelectFilter::make('blood_group_id')->label('Blood group')->relationship('bloodGroup', 'code'),
                SelectFilter::make('district_id')->label('District')->relationship('district', 'name')->searchable()->preload(),
            ])
            ->recordActions([
                Action::make('approve')->label('Approve & notify')->icon(Heroicon::OutlinedCheckCircle)->color('success')
                    ->visible(fn (EmergencyRequest $r) => $r->status === EmergencyStatus::Pending)
                    ->requiresConfirmation()
                    ->modalDescription('The request becomes public and compatible nearby donors are notified.')
                    ->action(function (EmergencyRequest $r) {
                        app(EmergencyRequestService::class)->activate($r, auth()->user());
                        Notification::make()->title('Request approved')->success()->send();
                    }),
                ActionGroup::make([
                    Action::make('view')->label('Open public page')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->url(fn (EmergencyRequest $r) => route('emergency.show', $r), shouldOpenInNewTab: true),
                    Action::make('fulfil')->label('Mark fulfilled')->icon(Heroicon::OutlinedHeart)
                        ->visible(fn (EmergencyRequest $r) => $r->status->isOpen())
                        ->requiresConfirmation()
                        ->action(fn (EmergencyRequest $r) => app(EmergencyRequestService::class)->fulfil($r)),
                    Action::make('reject')->label('Reject / cancel')->icon(Heroicon::OutlinedXCircle)->color('danger')
                        ->visible(fn (EmergencyRequest $r) => $r->status->isOpen())
                        ->requiresConfirmation()
                        ->action(fn (EmergencyRequest $r) => app(EmergencyRequestService::class)->cancel($r)),
                    EditAction::make(),
                ]),
            ]);
    }
}
