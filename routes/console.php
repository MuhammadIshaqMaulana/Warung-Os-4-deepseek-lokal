<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tandai transaksi QRIS yang kedaluwarsa & kembalikan stoknya.
Schedule::command('qris:expire')->everyMinute();
