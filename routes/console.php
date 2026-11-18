<?php

use Illuminate\Support\Facades\Schedule;

/*
| Run the scheduler in production with a cron entry:
|   * * * * * cd /path/to/bloodlink && php artisan schedule:run >> /dev/null 2>&1
*/

Schedule::command('bloodlink:expire')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('bloodlink:restore-availability')->dailyAt('06:00')->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::command('model:prune')->daily();
