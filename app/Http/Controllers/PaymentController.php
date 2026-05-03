<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Services\FonnteService;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function pay(Schedule $schedule)
    {
        if ($schedule->user_id !== auth()->id()) {
            abort(403);
        }

        if ($schedule->payment_status === 'paid') {
            return redirect()->route('jadwal.index')->with('success', 'Jadwal ini sudah lunas dibayar.');
        }

        // Hanya bisa bayar kalau guru sudah confirm (status: waiting_payment)
        if ($schedule->status !== 'waiting_payment') {
            return redirect()->route('jadwal.index')->with('error', 'Menunggu konfirmasi guru terlebih dahulu sebelum bisa melakukan pembayaran.');
        }

        // If not yet has snap token, generate it
        if (!$schedule->snap_token) {
            $params = [
                'transaction_details' => [
                    'order_id' => $schedule->order_id,
                    'gross_amount' => $schedule->total_price,
                ],
                'item_details' => [
                    [
                        'id' => $schedule->order_id,
                        'price' => $schedule->total_price,
                        'quantity' => 1,
                        'name' => 'Bimbel ' . substr($schedule->teacherProfile->subject, 0, 40)
                    ]
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->username ?? 'Student',
                    'email' => auth()->user()->email ?? '',
                    'phone' => auth()->user()->phone ?? '',
                ],
                'bca_va' => [
                    'free_text' => [
                        'inquiry' => [
                            [
                                'en' => 'RekoBimbel Mntring',
                                'id' => 'Pembayaran Bimbel'
                            ]
                        ],
                        'payment' => [
                            [
                                'en' => 'RekoBimbel Mntring',
                                'id' => 'Pembayaran Bimbel'
                            ]
                        ]
                    ]
                ],
                'callbacks' => [
                    'finish' => route('jadwal.index'),
                    'error' => route('jadwal.index'),
                    'unfinish' => route('jadwal.index'),
                ],
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                $schedule->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal memproses ke Midtrans: ' . $e->getMessage());
            }
        }

        return view('payment.pay', compact('schedule'));
    }

    public function resetToken(Schedule $schedule)
    {
        if ($schedule->user_id !== auth()->id() || $schedule->payment_status === 'paid') {
            abort(403);
        }

        // Must change order_id because Midtrans locks the payment method to the old order_id
        $schedule->update([
            'order_id' => 'SCH-' . time() . '-' . rand(1, 100),
            'snap_token' => null
        ]);

        return back()->with('success', 'Silakan pilih metode pembayaran baru.');
    }

    public function webhook(Request $request)
    {
        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $order_id = $notif->order_id;
        $fraud = $notif->fraud_status;

        $schedule = Schedule::where('order_id', $order_id)->first();
        if (!$schedule) {
            return response()->json(['status' => 'error', 'message' => 'Order ID not found'], 404);
        }

        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $schedule->payment_status = 'pending';
                } else {
                    if ($schedule->payment_status !== 'paid') {
                        $schedule->payment_status = 'paid';
                        $schedule->status = 'confirmed';
                    }
                }
            }
        } else if ($transaction == 'settlement') {
            if ($schedule->payment_status !== 'paid') {
                $schedule->payment_status = 'paid';
                $schedule->status = 'confirmed';
            }
        } else if ($transaction == 'pending') {
            $schedule->payment_status = 'unpaid';
        } else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
            $schedule->payment_status = 'failed';
            // Bisa ganti status jadi batal juga
            $schedule->status = 'batal';
        }

        $schedule->save();

        // Kirim WA ke guru jika pembayaran berhasil
        if ($schedule->payment_status === 'paid') {
            $this->notifyGuruPaymentSuccess($schedule);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Kirim notifikasi WA ke guru bahwa siswa sudah bayar.
     */
    private function notifyGuruPaymentSuccess(Schedule $schedule): void
    {
        try {
            $schedule->loadMissing(['user', 'teacherProfile.user']);
            $guru = $schedule->teacherProfile->user ?? null;
            $siswa = $schedule->user ?? null;

            if ($guru && $guru->phone && $siswa) {
                $message = "Halo Kak {$guru->name}! 💰\n\n"
                    . "Siswa *{$siswa->name}* sudah melakukan pembayaran untuk jadwal bimbel berikut:\n"
                    . "📅 Tanggal: {$schedule->tanggal}\n"
                    . "🕐 Jam: {$schedule->jam_mulai} - {$schedule->jam_selesai}\n"
                    . "💰 Total: Rp " . number_format($schedule->total_price, 0, ',', '.') . "\n\n"
                    . "Jadwal sudah dikonfirmasi. Selamat mengajar! 📚\n\n"
                    . "- RekoBimbel";
                app(FonnteService::class)->send($guru->phone, $message);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal kirim WA ke guru: ' . $e->getMessage());
        }
    }
}
