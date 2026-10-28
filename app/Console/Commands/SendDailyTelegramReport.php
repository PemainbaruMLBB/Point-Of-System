<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendDailyTelegramReport extends Command
{
    protected $signature = 'reports:telegram-daily';

    protected $description = 'Send the daily sales summary to the configured Telegram chat';

    public function handle(TelegramService $telegram): int
    {
        $sales = Sale::whereDate('created_at', today());
        $count = (clone $sales)->count();
        $total = (clone $sales)->sum('total');
        $telegram->admin("📊 <b>Ringkasan penjualan harian</b>\nTanggal: ".today()->format('d-m-Y')."\nTransaksi: {$count}\nOmzet: Rp ".number_format($total, 0, ',', '.'));

        $this->info('Daily Telegram report sent.');

        return self::SUCCESS;
    }
}
