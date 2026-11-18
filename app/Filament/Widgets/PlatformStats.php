<?php

namespace App\Filament\Widgets;

use App\Enums\EmergencyStatus;
use App\Enums\ReportStatus;
use App\Models\DonorProfile;
use App\Models\DonorProfileView;
use App\Models\EmergencyRequest;
use App\Models\Report;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $period = now()->format('Y-m');
        $newThisWeek = User::where('created_at', '>=', now()->subWeek())->count();

        return [
            Stat::make('Registered users', number_format(User::count()))
                ->description("+{$newThisWeek} this week")->descriptionIcon('heroicon-m-arrow-trending-up')->color('success'),
            Stat::make('Listed donors', number_format(DonorProfile::where('is_listed', true)->count()))
                ->description(number_format(DonorProfile::where('is_listed', true)->where('is_available', true)->count()).' available now'),
            Stat::make('Verified donors', number_format(User::whereNotNull('verified_at')->count()))
                ->description(number_format(User::whereNull('verified_at')->whereNotNull('phone_verified_at')->count()).' awaiting verification')
                ->color('info'),
            Stat::make('Active emergency requests', EmergencyRequest::active()->count())
                ->description(EmergencyRequest::where('status', EmergencyStatus::Pending->value)->count().' pending review')
                ->color('danger'),
            Stat::make('Open abuse reports', Report::whereIn('status', [ReportStatus::Open->value, ReportStatus::Reviewing->value])->count())
                ->color('warning'),
            Stat::make('Contact profiles unlocked', number_format(DonorProfileView::where('period', $period)->count()))
                ->description('This month ('.now()->format('F').')'),
        ];
    }
}
