<?php

namespace App\Filament\Resources\Reports;

use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\UserStatus;
use App\Filament\Resources\Reports\Pages\ManageReports;
use App\Filament\Resources\Users\UserResource;
use App\Models\EmergencyRequest;
use App\Models\Report;
use App\Models\User;
use App\Services\EmergencyRequestService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/** Abuse reports against users (donors/requesters) and emergency requests. */
class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static string|UnitEnum|null $navigationGroup = 'Trust & Safety';

    protected static ?string $navigationLabel = 'Abuse reports';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $n = Report::whereIn('status', [ReportStatus::Open->value, ReportStatus::Reviewing->value])->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('reporter', 'reportable', 'handler'))
            ->columns([
                TextColumn::make('reportable_type')->label('Target')->badge()
                    ->formatStateUsing(fn ($state) => $state === 'user' ? 'User' : 'Emergency request'),
                TextColumn::make('target_name')->label('Reported')
                    ->state(fn (Report $r) => match (true) {
                        $r->reportable instanceof User => $r->reportable->name.' ('.$r->reportable->phone.')',
                        $r->reportable instanceof EmergencyRequest => $r->reportable->hospital_name,
                        default => '(deleted)',
                    }),
                TextColumn::make('reason')->badge()->color('warning'),
                TextColumn::make('details')->limit(50)->tooltip(fn (Report $r) => $r->details)->wrap(),
                TextColumn::make('reporter.name')->label('Reporter')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since()->sortable(),
                TextColumn::make('handler.name')->label('Handled by')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ReportStatus::class),
                SelectFilter::make('reason')->options(ReportReason::class),
                SelectFilter::make('reportable_type')->label('Target')->options(['user' => 'User', 'emergency_request' => 'Emergency request']),
            ])
            ->recordActions([
                Action::make('resolve')->icon(Heroicon::OutlinedCheckCircle)->color('success')
                    ->visible(fn (Report $r) => in_array($r->status, [ReportStatus::Open, ReportStatus::Reviewing], true))
                    ->schema([
                        Select::make('outcome')->required()->options([
                            'resolved' => 'Resolve (no further action)',
                            'suspend' => 'Resolve and suspend the reported user',
                            'cancel_request' => 'Resolve and cancel the emergency request',
                            'dismissed' => 'Dismiss (report not valid)',
                        ]),
                        Textarea::make('resolution_note')->maxLength(1000),
                    ])
                    ->action(function (Report $r, array $data) {
                        DB::transaction(function () use ($r, $data) {
                            $target = $r->reportable;
                            if ($data['outcome'] === 'suspend') {
                                $user = $target instanceof EmergencyRequest ? $target->user : $target;
                                if ($user instanceof User && ! $user->isAdmin()) {
                                    $user->forceFill(['status' => UserStatus::Suspended, 'status_reason' => 'Report #'.$r->id.': '.$r->reason->getLabel()])->save();
                                    DB::table('sessions')->where('user_id', $user->id)->delete();
                                }
                            }
                            if ($data['outcome'] === 'cancel_request' && $target instanceof EmergencyRequest) {
                                app(EmergencyRequestService::class)->cancel($target);
                            }
                            $r->forceFill([
                                'status' => $data['outcome'] === 'dismissed' ? ReportStatus::Dismissed : ReportStatus::Resolved,
                                'resolution_note' => $data['resolution_note'] ?? null,
                                'handled_by' => auth()->id(),
                                'resolved_at' => now(),
                            ])->save();
                        });
                        Notification::make()->title('Report closed')->success()->send();
                    }),
                ActionGroup::make([
                    Action::make('reviewing')->label('Mark reviewing')->icon(Heroicon::OutlinedEye)
                        ->visible(fn (Report $r) => $r->status === ReportStatus::Open)
                        ->action(fn (Report $r) => $r->forceFill(['status' => ReportStatus::Reviewing, 'handled_by' => auth()->id()])->save()),
                    Action::make('open_target')->label('Open target')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->url(fn (Report $r) => match (true) {
                            $r->reportable instanceof EmergencyRequest => route('emergency.show', $r->reportable),
                            $r->reportable instanceof User => UserResource::getUrl('edit', ['record' => $r->reportable]),
                            default => null,
                        }, shouldOpenInNewTab: true),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageReports::route('/')];
    }
}
