<?php

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruDashboardController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPreferenceController;
use App\Http\Controllers\TeacherProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Route OTP (harus login tapi belum perlu verified)
Route::middleware(['auth'])->group(function () {
    Route::get('/verify-otp', [OtpController::class, 'show'])->name('otp.verify');
    Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify.submit');
    Route::post('/resend-otp', [OtpController::class, 'resend'])->name('otp.resend');
    Route::post('/change-phone-otp', [OtpController::class, 'changePhone'])->name('otp.change.phone');
});

// Route utama (harus login + phone verified)
Route::middleware(['auth', 'phone.verified'])->group(function () {

    // Dashboard admin
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cari guru (siswa)
    Route::get('/cari-guru', [StudentController::class, 'index'])->name('cari.guru');

    // Preferensi siswa
    Route::get('/preferensi', [StudentPreferenceController::class, 'create'])->name('preferensi.create');
    Route::post('/preferensi', [StudentPreferenceController::class, 'store'])->name('preferensi.store');
    Route::get('/preferensi/edit', [StudentPreferenceController::class, 'edit'])->name('preferensi.edit');
    Route::put('/preferensi', [StudentPreferenceController::class, 'update'])->name('preferensi.update');

    // Dashboard guru
    Route::get('/guru/dashboard', [GuruDashboardController::class, 'index'])->name('guru.dashboard');

    // Profil guru
    Route::get('/guru/profil', [TeacherProfileController::class, 'create'])->name('guru.profil.create');
    Route::post('/guru/profil', [TeacherProfileController::class, 'store'])->name('guru.profil.store');
    Route::get('/guru/profil/edit', [TeacherProfileController::class, 'edit'])->name('guru.profil.edit');
    Route::put('/guru/profil', [TeacherProfileController::class, 'update'])->name('guru.profil.update');
    Route::get('/guru/reviews', [TeacherProfileController::class, 'reviews'])->name('guru.reviews');

    // Approve/reject guru & penarikan (admin)
    Route::post('/guru/{id}/approve', [DashboardController::class, 'approve'])->name('guru.approve');
    Route::post('/guru/{id}/reject', [DashboardController::class, 'reject'])->name('guru.reject');
    Route::post('/withdrawals/{id}/approve', [DashboardController::class, 'approveWithdrawal'])->name('admin.withdrawal.approve');
    Route::post('/withdrawals/{id}/reject', [DashboardController::class, 'rejectWithdrawal'])->name('admin.withdrawal.reject');

    // Review guru (siswa)
    Route::post('/review', [ReviewController::class, 'store'])->name('review.store');

    // Jadwal belajar (siswa)
    Route::get('/jadwal', [ScheduleController::class, 'index'])->name('jadwal.index');
    Route::get('/jadwal/booking/{teacherProfileId}', [ScheduleController::class, 'create'])->name('jadwal.create');
    Route::post('/jadwal', [ScheduleController::class, 'store'])->name('jadwal.store');
    Route::post('/jadwal/{id}/cancel', [ScheduleController::class, 'cancel'])->name('jadwal.cancel');
    Route::get('/jadwal/{id}/invoice', [ScheduleController::class, 'invoice'])->name('schedule.invoice');
    
    // Pembayaran
    Route::get('/payment/{schedule}', [PaymentController::class, 'pay'])->name('payment.pay');
    Route::post('/payment/{schedule}/reset', [PaymentController::class, 'resetToken'])->name('payment.reset');

    // Jadwal belajar (guru)
    Route::get('/guru/jadwal', [ScheduleController::class, 'guruIndex'])->name('jadwal.guru');
    Route::post('/jadwal/{id}/confirm', [ScheduleController::class, 'confirm'])->name('jadwal.confirm');
    Route::post('/jadwal/{id}/complete', [ScheduleController::class, 'complete'])->name('jadwal.complete');
    
    // Pendapatan / Saldo Guru
    Route::get('/guru/pendapatan', [\App\Http\Controllers\GuruDashboardController::class, 'pendapatan'])->name('guru.pendapatan');
    Route::post('/guru/withdraw', [\App\Http\Controllers\GuruDashboardController::class, 'withdraw'])->name('guru.withdraw');

    // Manajemen user (admin)
    Route::resource('users', UserController::class);

    // Whatsapp manual send (admin testing)
    Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('wa.index');
    Route::post('/whatsapp/send', [WhatsappController::class, 'send'])->name('wa.send');
});

// Webhook Midtrans (tanpa middleware auth)
Route::post('/midtrans/webhook', [PaymentController::class, 'webhook'])->name('midtrans.webhook');

require __DIR__ . '/auth.php';