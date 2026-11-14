<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('trades:settle-expired --limit=100')
    ->everyMinute()
    ->withoutOverlapping();

// Real pending orders (By Price / By Time): execute due orders as real
// trades. Same pattern as trades:settle-expired; the host still needs a
// cron running `artisan schedule:run` every minute (or run
// `artisan pending:execute` directly from the host cron instead).
Schedule::command('pending:execute --limit=100')
    ->everyMinute()
    ->withoutOverlapping();
