<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function send(?string $chatId, string $message): void
    {
        $token = config('services.telegram.bot_token');
        if (! $token || ! $chatId) {
            return;
        }

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ])->throw();
        } catch (\Throwable $exception) {
            Log::warning('Telegram notification failed', ['chat_id' => $chatId, 'error' => $exception->getMessage()]);
        }
    }

    public function admin(string $message): void
    {
        $this->send(config('services.telegram.admin_chat_id'), $message);
    }
}
