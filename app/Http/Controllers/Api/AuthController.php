<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('nim', $request->login_id)
            ->orWhere('email', $request->login_id)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'NIM/Email atau Password Salah'
            ], 401);
        }
        $role = $user->getRoleNames()->first();
        $token = $user->createToken('portal')->plainTextToken;
        return response()->json([
            'message' => 'Login Berhasil',
            'token' => $token,
            'user' => $user,
            'role' => $role,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20', // Pastikan ada kolom phone di tabel users
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'user' => $user,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed', // Butuh input new_password_confirmation di frontend
        ]);

        $user = auth()->user();

        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password lama salah'], 422);
        }

        // Update password baru
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password berhasil diubah']);
    }
    public function sendOtp(Request $request)
    {
        $request->validate(['nim' => 'required']);

        $user = User::where('nim', $request->nim)->first();

        if (!$user) {
            return response()->json(['message' => 'NIM tidak ditemukan'], 404);
        }

        if (!$user->phone) {
            return response()->json(['message' => 'No. HP belum terdaftar. Hubungi Admin.'], 400);
        }

        // Generate OTP 6 Digit Angka
        $otp = rand(100000, 999999);

        // Simpan ke Database (Berlaku 5 Menit)
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(5)
        ]);

        // Kirim WA
        $message = "*KODE OTP RESET PASSWORD*\n\n"
            . "Halo {$user->name},\n"
            . "Gunakan kode berikut untuk mereset password Anda:\n\n"
            . "*$otp*\n\n"
            . "Kode ini hanya berlaku selama 5 menit. JANGAN BERIKAN KODE INI KE SIAPAPUN.";

        try {
            $response = FonnteService::sendMessage($user->phone, $message);
            return response()->json(['message' => 'Kode OTP telah dikirim ke WhatsApp Anda.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengirim OTP'], 500);
        }
    }

    // 2. FUNGSI RESET PASSWORD DENGAN OTP
    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'nim' => 'required',
            'otp' => 'required|numeric',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('nim', $request->nim)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // 1. CEK APAKAH DATA OTP ADA? (PERBAIKAN UTAMA)
        // Jika kolom kosong, tolak permintaan agar tidak error "isPast on null"
        if (is_null($user->otp_expires_at)) {
            return response()->json([
                'message' => 'Data OTP tidak ditemukan atau belum dibuat. Silakan minta kode OTP ulang.'
            ], 400);
        }

        // 2. Cek Kesesuaian Kode OTP
        if (strval($user->otp_code) !== strval($request->otp)) {
            return response()->json(['message' => 'Kode OTP Salah!'], 400);
        }

        // 3. Cek Waktu Kadaluarsa
        if ($user->otp_expires_at->isPast()) {
            return response()->json([
                'message' => 'Kode OTP sudah kadaluarsa. Silakan minta ulang.',
            ], 400);
        }

        // 4. Update Password & Bersihkan OTP
        $user->update([
            'password' => Hash::make($request->password),
            'otp_code' => null,
            'otp_expires_at' => null
        ]);

        return response()->json(['message' => 'Password berhasil diubah. Silakan Login.']);
    }
}
