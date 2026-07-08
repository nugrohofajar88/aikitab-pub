<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Picks up book-export files pushed over FTPS to storage/app/private/book-imports.
// Requires the actual cron entry `* * * * * php artisan schedule:run` to be set up
// in cPanel — this registration alone does nothing without that.
Schedule::command('books:process-imports')->everyMinute()->withoutOverlapping();
