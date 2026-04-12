<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\TeacherProfile;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    // Form booking jadwal
    public function create($teacherProfileId)
    {
        $teacher = TeacherProfile::with('user')->findOrFail($teacherProfileId);
        return view('schedule.create', compact('teacher'));
    }

    // Simpan jadwal baru
    public function store(Request $request)
    {
        $request->validate([
            'teacher_profile_id' => ['required', 'exists:teacher_profiles,id'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required'],
            'jam_selesai' => ['required'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $teacher = TeacherProfile::findOrFail($request->teacher_profile_id);

        Schedule::create([
            'user_id' => auth()->id(),
            'teacher_profile_id' => $request->teacher_profile_id,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'catatan' => $request->catatan,
            'status' => 'pending',
            'order_id' => 'SCH-' . time() . '-' . auth()->id(),
            'total_price' => $teacher->price ?? 0,
            'payment_status' => 'unpaid',
        ]);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dibuat, menunggu konfirmasi guru.');
    }

    // Daftar jadwal siswa
    public function index(Request $request)
    {
        // Fallback untuk localhost: cek status langsung ke Midtrans jika ada redirect query params
        if ($request->has('order_id') && $request->has('transaction_status')) {
            try {
                \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
                \Midtrans\Config::$isProduction = config('services.midtrans.is_production', false);
                
                $statusResponse = \Midtrans\Transaction::status($request->order_id);
                $schedule = Schedule::where('order_id', $request->order_id)->first();
                
                $transactionStatus = is_array($statusResponse) ? ($statusResponse['transaction_status'] ?? '') : ($statusResponse->transaction_status ?? '');
                
                if ($schedule && in_array($transactionStatus, ['settlement', 'capture'])) {
                    if ($schedule->payment_status !== 'paid') {
                        $schedule->update([
                            'payment_status' => 'paid',
                            'status' => 'confirmed'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Abaikan error midtrans
            }
            return redirect()->route('jadwal.index')->with('success', 'Status tagihan telah disinkronisasi.');
        }

        $schedules = Schedule::where('user_id', auth()->id())
            ->with('teacherProfile.user')
            ->latest()
            ->get();

        return view('schedule.index', compact('schedules'));
    }

    // Daftar jadwal guru (yang masuk ke dia)
    public function guruIndex()
    {
        $profile = TeacherProfile::where('user_id', auth()->id())->first();

        $schedules = [];
        if ($profile) {
            $schedules = Schedule::where('teacher_profile_id', $profile->id)
                ->with('user')
                ->latest()
                ->get();
        }

        return view('schedule.guru', compact('schedules'));
    }

    // Guru konfirmasi jadwal
    public function confirm($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update(['status' => 'confirmed']);

        return back()->with('success', 'Jadwal dikonfirmasi.');
    }

    // Guru atau siswa batalkan jadwal
    public function cancel($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update(['status' => 'batal']);

        return back()->with('success', 'Jadwal dibatalkan.');
    }

    // Guru tandai selesai
    public function complete($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule = Schedule::where('id', $id)
            ->where('teacher_profile_id', Auth::user()->teacherProfile->id)
            ->where('status', 'confirmed')
            ->firstOrFail();

        $schedule->update([
            'status' => 'selesai'
        ]);

        // Release money to teacher ONLY when marked as selesai (escrow released)
        if ($schedule->payment_status === 'paid' && $schedule->teacherProfile) {
            $teacherCut = (int) ($schedule->total_price * 0.80); // 80% rule
            $schedule->teacherProfile->increment('balance', $teacherCut);
        }

        return back()->with('success', 'Jadwal ditandai selesai. Saldo pendapatan telah masuk ke rekening RekoBimbel Anda.');
    }

    // Invoice untuk siswa
    public function invoice($id)
    {
        $schedule = Schedule::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('payment_status', 'paid')
            ->firstOrFail();

        return view('schedule.invoice', compact('schedule'));
    }
}
