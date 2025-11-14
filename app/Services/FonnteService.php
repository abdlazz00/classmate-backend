<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Tambahkan ini

class FonnteService
{
    private static function sendRequest(array $data)
    {
        // Perhatikan ejaannya: 'fonnte' (double n) sesuai config/services.php Anda
        $token = config('services.fonnte.token');

        // --- DEBUGGING: Cek token apa yang terbaca sistem ---
        Log::info('🔍 DEBUG FONNTE TOKEN:', [
            'token_yang_dibaca' => $token ? substr($token, 0, 5) . '...' : 'KOSONG/NULL',
            'panjang_token' => strlen($token ?? ''),
        ]);
        // ----------------------------------------------------

        return Http::withHeaders([
            'Authorization' => $token, // Langsung gunakan variabel $token
        ])->asForm()->post('https://api.fonnte.com/send', $data);
    }

    public static function sendMessage(string $target, string $message)
    {
        return self::sendRequest([
            'target' => $target,
            'message' => $message,
        ]);
    }

    public static function sendFile(string $target, string $fileUrl, string $caption = '')
    {
        return self::sendRequest([
            'target' => $target,
            'url' => $fileUrl, // Fonnte update: gunakan 'url' bukan 'fileUrl' untuk send file
            'message' => $caption, // Fonnte update: caption masuk ke 'message' jika ada file
        ]);
    }
}
