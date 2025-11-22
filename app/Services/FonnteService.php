<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    private static function sendRequest(array $data)
    {
        // PERBAIKAN: Ambil langsung dari .env agar lebih aman dan pasti ada
        $token = env('FONNTE_TOKEN');

        // Debugging: Cek di Laravel.log apakah token terbaca
        Log::info('🔍 DEBUG FONNTE TOKEN:', [
            'token_preview' => $token ? substr($token, 0, 5) . '...' : 'KOSONG/NULL',
        ]);

        return Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post('https://api.fonnte.com/send', $data);
    }

    public static function sendMessage(string $target, string $message)
    {
        return self::sendRequest([
            'target' => $target,
            'message' => $message,
            'countryCode' => '62', // Tambahan: Otomatis ubah 08xx jadi 62xx
        ]);
    }

    public static function sendFile(string $target, string $fileUrl, string $caption = '')
    {
        return self::sendRequest([
            'target' => $target,
            'url' => $fileUrl,
            'message' => $caption,
        ]);
    }
}
