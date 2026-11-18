<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Who opened the most donor phone numbers this month (no limit; red above the alert threshold). */
class TopProfileViewers extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $period = now()->format('Y-m');
        $threshold = (int) config('bloodlink.view_alert_threshold');

        return $table
            ->heading('Donor numbers opened — top members ('.now()->format('F Y').')')
            ->description("Highlighted in red above {$threshold} different donors in a month.")
            ->query(fn () => User::query()
                ->withCount(['profileViews as views_this_month' => fn ($q) => $q->where('period', $period)])
                ->whereHas('profileViews', fn ($q) => $q->where('period', $period))
                ->orderByDesc('views_this_month'))
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('name')->description(fn (User $r) => $r->phone),
                TextColumn::make('views_this_month')->label('Numbers opened this month')->badge()
                    ->color(fn ($state) => $state > $threshold ? 'danger' : ($state >= $threshold * 0.7 ? 'warning' : 'success')),
                TextColumn::make('created_at')->label('Account age')->since(),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                Action::make('open')->label('Review user')->url(fn (User $r) => UserResource::getUrl('edit', ['record' => $r])),
            ]);
    }
}
