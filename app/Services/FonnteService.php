<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    private static function sendRequest(array $data)
    {
        $token = config('services.fonte.token');
        return Http::withHeaders([
            'Authorization' => "$token",
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
            'fileUrl' => $fileUrl,
            'caption' => $caption,
        ]);
    }
}
