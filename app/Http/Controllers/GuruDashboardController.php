<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class GuruDashboardController extends Controller
{
    public function index()
    {
        return view('guru.dashboard');
    }

    public function pendapatan()
    {
        $profile = TeacherProfile::where('user_id', Auth::id())->first();

        // Ambil jadwal dan riwayat penarikan
        $schedules = collect();
        $withdrawals = collect();
        if ($profile) {
            $schedules = Schedule::where('teacher_profile_id', $profile->id)
                ->where('payment_status', 'paid')
                ->where('status', 'selesai')
                ->with('user')
                ->latest()
                ->get();
                
            $withdrawals = \App\Models\Withdrawal::where('teacher_profile_id', $profile->id)
                ->latest()
                ->get();
        }

        return view('guru.pendapatan', compact('profile', 'schedules', 'withdrawals'));
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1000'
        ]);

        $profile = TeacherProfile::where('user_id', Auth::id())->first();

        if (!$profile || !$profile->bank_name || !$profile->bank_account_number || !$profile->bank_account_name) {
            return back()->with('error', 'Gagal! Silakan lengkapi data rekening pencairan di halaman Profil terlebih dahulu.');
        }

        if ($request->amount > $profile->balance) {
            return back()->with('error', 'Gagal! Saldo yang ditarik melebihi total saldo tersedia.');
        }

        // Potong saldo
        $profile->decrement('balance', $request->amount);

        // Catat pengajuan
        \App\Models\Withdrawal::create([
            'teacher_profile_id' => $profile->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'bank_name' => $profile->bank_name,
            'bank_account_number' => $profile->bank_account_number,
            'bank_account_name' => $profile->bank_account_name,
        ]);

        return back()->with('success', 'Berhasil! Permintaan pencairan dana telah dikirim, saldo Anda telah terpotong sementara proses transfer.');
    }
}