<?php

namespace App\Filament\Resources\DonorProfiles\Tables;

use App\Models\DonorProfile;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DonorProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('Donor')->searchable()->sortable()
                    ->description(fn (DonorProfile $r) => $r->user->phone),
                TextColumn::make('bloodGroup.code')->label('Blood')->badge()->color('danger')->sortable(),
                TextColumn::make('upazila.name')->label('Upazila')->searchable(),
                TextColumn::make('district.name')->label('District')->searchable()->sortable(),
                TextColumn::make('division.name')->label('Division')->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_available')->label('Available'),
                IconColumn::make('is_listed')->boolean()->label('Listed'),
                IconColumn::make('user.verified_at')->label('Verified')->boolean()
                    ->getStateUsing(fn (DonorProfile $r) => $r->user->verified_at !== null),
                TextColumn::make('last_donation_date')->date()->sortable()->placeholder('—'),
                TextColumn::make('age')->label('Age')->state(fn (DonorProfile $r) => $r->age())->placeholder('—')->toggleable(),
                TextColumn::make('weight_kg')->label('Weight (kg)')->sortable()->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('blood_group_id')->label('Blood group')->relationship('bloodGroup', 'code'),
                SelectFilter::make('division_id')->label('Division')->relationship('division', 'name'),
                SelectFilter::make('district_id')->label('District')->relationship('district', 'name')->searchable()->preload(),
                SelectFilter::make('upazila_id')->label('Upazila')->relationship('upazila', 'name')->searchable(),
                TernaryFilter::make('is_available')->label('Available'),
                TernaryFilter::make('is_listed')->label('Listed'),
                Filter::make('verified')->label('Verified donors only')
                    ->query(fn (Builder $query) => $query->whereHas('user', fn ($u) => $u->whereNotNull('verified_at'))),
            ])
            ->recordActions([
                Action::make('public')->label('View public')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (DonorProfile $r) => route('donors.show', $r), shouldOpenInNewTab: true),
                Action::make('verify')->icon(Heroicon::OutlinedCheckBadge)->color('success')
                    ->visible(fn (DonorProfile $r) => ! $r->user->verified_at)
                    ->requiresConfirmation()
                    ->action(fn (DonorProfile $r) => $r->user->forceFill(['verified_at' => now(), 'verified_by' => auth()->id()])->save()),
                EditAction::make(),
            ]);
    }
}
