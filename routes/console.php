<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Picks up book-export files pushed over FTPS to storage/app/private/book-imports.
//
// NOT ACTUALLY USED on the real hosting account — Schedule::command() spawns
// the command as a separate process via Symfony Process, which needs
// proc_open(), and that's disabled on this shared host (common security
// restriction). Kept here for local dev / documentation purposes only. The
// real cron entry calls the artisan command directly instead of
// `schedule:run`, bypassing proc_open entirely:
//   * * * * * /usr/local/bin/ea-php84 /path/to/app/artisan books:process-imports >> /dev/null 2>&1
Schedule::command('books:process-imports')->everyMinute()->withoutOverlapping();
