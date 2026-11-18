<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\BloodGroup;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()
                    ->description(fn (User $r) => $r->email),
                TextColumn::make('phone')->searchable()->toggleable(),
                TextColumn::make('donorProfile.bloodGroup.code')->label('Blood')->badge()->color('danger'),
                TextColumn::make('donorProfile.district.name')->label('District')->toggleable(),
                TextColumn::make('role')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                IconColumn::make('verified_at')->label('Verified')->boolean()
                    ->getStateUsing(fn (User $r) => $r->verified_at !== null),
                IconColumn::make('phone_verified_at')->label('Mobile')->boolean()
                    ->getStateUsing(fn (User $r) => $r->phone_verified_at !== null)->toggleable(),
                // How many different donor phone numbers this member opened (no limit; monitored).
                TextColumn::make('numbers_viewed_this_month')->label('Numbers viewed (month)')->sortable()->badge()
                    ->color(fn ($state) => $state > config('bloodlink.view_alert_threshold') ? 'danger' : ($state ? 'info' : 'gray')),
                TextColumn::make('numbers_viewed_total')->label('Numbers viewed (total)')->sortable()->toggleable(),
                TextColumn::make('reports_against_count')->counts('reportsAgainst')->label('Reports')
                    ->badge()->color(fn ($state) => $state ? 'danger' : 'gray')->sortable(),
                TextColumn::make('created_at')->label('Joined')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->options(UserRole::class),
                SelectFilter::make('status')->options(UserStatus::class),
                TernaryFilter::make('verified_at')->label('Admin verified')->nullable(),
                TernaryFilter::make('phone_verified_at')->label('Mobile verified')->nullable(),
                SelectFilter::make('blood_group')->label('Blood group')
                    ->options(fn () => BloodGroup::orderBy('sort_order')->pluck('code', 'id'))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn ($q, $v) => $q->whereHas('donorProfile', fn ($p) => $p->where('blood_group_id', $v)))),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('verify')->label('Verify donor')->icon(Heroicon::OutlinedCheckBadge)->color('success')
                        ->visible(fn (User $r) => ! $r->verified_at)
                        ->requiresConfirmation()
                        ->action(function (User $r) {
                            $r->forceFill(['verified_at' => now(), 'verified_by' => auth()->id()])->save();
                            Notification::make()->title("{$r->name} verified")->success()->send();
                        }),
                    Action::make('unverify')->label('Remove verification')->icon(Heroicon::OutlinedXCircle)
                        ->visible(fn (User $r) => (bool) $r->verified_at)
                        ->requiresConfirmation()
                        ->action(fn (User $r) => $r->forceFill(['verified_at' => null, 'verified_by' => null])->save()),
                    Action::make('suspend')->icon(Heroicon::OutlinedNoSymbol)->color('danger')
                        ->visible(fn (User $r) => $r->status !== UserStatus::Suspended && ! $r->is(auth()->user()))
                        ->schema([Textarea::make('reason')->required()->maxLength(255)])
                        ->action(function (User $r, array $data) {
                            $r->forceFill(['status' => UserStatus::Suspended, 'status_reason' => $data['reason']])->save();
                            // Kill any active sessions immediately.
                            DB::table('sessions')->where('user_id', $r->id)->delete();
                            Notification::make()->title("{$r->name} suspended")->warning()->send();
                        }),
                    Action::make('activate')->label('Reactivate')->icon(Heroicon::OutlinedArrowPath)->color('success')
                        ->visible(fn (User $r) => $r->status !== UserStatus::Active)
                        ->requiresConfirmation()
                        ->action(fn (User $r) => $r->forceFill(['status' => UserStatus::Active, 'status_reason' => null])->save()),
                    EditAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('verify')->label('Verify selected donors')->icon(Heroicon::OutlinedCheckBadge)
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(fn (User $u) => $u->forceFill(['verified_at' => $u->verified_at ?? now(), 'verified_by' => $u->verified_by ?? auth()->id()])->save())),
                ]),
            ]);
    }
}
