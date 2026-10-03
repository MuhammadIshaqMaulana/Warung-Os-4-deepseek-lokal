<?php

namespace App\Console\Commands;

use App\Services\TransactionService;
use Illuminate\Console\Command;

class ExpireQrisPayments extends Command
{
    protected $signature = 'qris:expire';

    protected $description = 'Batalkan transaksi QRIS yang kedaluwarsa dan kembalikan stoknya';

    public function handle(TransactionService $transactions): int
    {
        $count = $transactions->expireOverdue();

        $this->info("{$count} transaksi QRIS kedaluwarsa diproses.");

        return self::SUCCESS;
    }
}
