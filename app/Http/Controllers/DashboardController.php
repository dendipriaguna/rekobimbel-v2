<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\StudentPreference;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Redirect ke dashboard sesuai role user
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        if ($user->role === 'guru') {
            return $this->guruDashboard();
        }

        return $this->siswaDashboard();
    }

    // Dashboard admin: statistik + daftar guru
    private function adminDashboard()
    {
        $totalUsers = User::count();
        $totalGuru = User::where('role', 'guru')->count();
        $totalSiswa = User::where('role', 'siswa')->count();
        $pendingGuru = TeacherProfile::where('status', 'pending')->count();
        $guruProfiles = TeacherProfile::with('user')->latest()->get();
        $withdrawals = \App\Models\Withdrawal::with('teacherProfile.user')->latest()->get();

        return view('dashboard', compact(
            'totalUsers',
            'totalGuru',
            'totalSiswa',
            'pendingGuru',
            'guruProfiles',
            'withdrawals'
        ));
    }

    // Dashboard guru: tampilkan profil sendiri
    private function guruDashboard()
    {
        $profile = TeacherProfile::where('user_id', auth()->id())->first();

        return view('dashboard', compact('profile'));
    }

    // Dashboard siswa: auto scoring dari preferensi yang tersimpan
    private function siswaDashboard()
    {
        $preference = StudentPreference::where('user_id', auth()->id())->first();
        $teachers = TeacherProfile::where('status', 'approved')->with(['user', 'reviews', 'schedules'])->get();

        // Jika sudah memiliki preferensi, langsung jalankan perhitungan scoring
        if ($preference) {
            $teachers = $teachers->map(function ($teacher) use ($preference) {
                $score = 0;

                // Content-based: subject match
                if ($preference->subject && str_contains(strtolower($teacher->subject), strtolower($preference->subject))) {
                    $score += 40;
                }

                // Content-based: jenjang match
                if ($preference->jenjang && strtolower($teacher->jenjang) === strtolower($preference->jenjang)) {
                    $score += 25;
                }

                // Content-based: harga dalam budget
                if ($preference->max_price && $teacher->price <= $preference->max_price) {
                    $score += 20;
                    $score += round((($preference->max_price - $teacher->price) / $preference->max_price) * 10);
                }

                // Rule-based: gender match
                if ($preference->gender && $teacher->gender === $preference->gender) {
                    $score += 10;
                }

                // Rule-based: availability match
                if ($preference->availability && str_contains(strtolower($teacher->availability ?? ''), strtolower($preference->availability))) {
                    $score += 15;
                }

                 // 🔥 Location (EXACT MATCH)
                if ($preference->location && $teacher->location === $preference->location) {
                    $score += 40; // bobot tinggi karena penting
                }

                // Rule-based: subject + jenjang
                if (
                    $preference->subject && $preference->jenjang &&
                    str_contains(strtolower($teacher->subject), strtolower($preference->subject)) &&
                    strtolower($teacher->jenjang) === strtolower($preference->jenjang)
                ) {
                    $score += 20;
                }

                // Rule-based: experience (max 10)
                if ($teacher->experience) {
                    $expYears = (int) filter_var($teacher->experience, FILTER_SANITIZE_NUMBER_INT);
                    $score += min($expYears * 2, 10);
                }

                $teacher->score = $score;
                return $teacher;
            });

            // Urutkan by skor tertinggi
            $teachers = $teachers->sortByDesc('score')->values();
        }

        return view('dashboard', compact('preference', 'teachers'));
    }

    // Admin approve guru
    public function approve($id)
    {
        $profile = TeacherProfile::findOrFail($id);
        $profile->update(['status' => 'approved']);

        return back()->with('success', 'Guru berhasil di-approve.');
    }

    // Admin reject guru
    public function reject($id)
    {
        $profile = TeacherProfile::findOrFail($id);
        $profile->update(['status' => 'rejected']);

        return back()->with('success', 'Guru berhasil di-reject.');
    }

    // Admin approve pencairan dana
    public function approveWithdrawal(Request $request, $id)
    {
        $withdrawal = \App\Models\Withdrawal::findOrFail($id);

        $request->validate([
            'proof_image' => 'nullable|image|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('proof_image')) {
            $imagePath = $request->file('proof_image')->store('withdrawals', 'public');
        }

        $withdrawal->update([
            'status' => 'approved',
            'proof_image' => $imagePath
        ]);

        return back()->with('success', 'Penarikan dana disetujui. Status diperbarui.');
    }

    // Admin reject/batal pencairan dana
    public function rejectWithdrawal($id)
    {
        $withdrawal = \App\Models\Withdrawal::findOrFail($id);
        
        // Refund saldo yang tadinya tertahan
        if ($withdrawal->teacherProfile && $withdrawal->status === 'pending') {
            $withdrawal->teacherProfile->increment('balance', $withdrawal->amount);
        }

        $withdrawal->update([
            'status' => 'rejected'
        ]);

        return back()->with('success', 'Penarikan dana dibatalkan dan saldo telah dikembalikan ke Guru.');
    }
}
