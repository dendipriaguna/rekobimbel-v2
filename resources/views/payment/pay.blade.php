@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Pembayaran" />

<div class="max-w-2xl mx-auto space-y-6">
    <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Detail Pembayaran</h3>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Selesaikan pembayaran untuk mengkonfirmasi jadwal belajar Anda.</p>

        <div class="space-y-4 mb-8">
            <div class="flex justify-between border-b pb-2 dark:border-gray-800">
                <span class="text-gray-600 dark:text-gray-400">Order ID</span>
                <span class="font-medium dark:text-white">{{ $schedule->order_id }}</span>
            </div>
            <div class="flex justify-between border-b pb-2 dark:border-gray-800">
                <span class="text-gray-600 dark:text-gray-400">Guru</span>
                <span class="font-medium dark:text-white">{{ $schedule->teacherProfile->user->name }}</span>
            </div>
            <div class="flex justify-between border-b pb-2 dark:border-gray-800">
                <span class="text-gray-600 dark:text-gray-400">Tanggal & Jam</span>
                <span class="font-medium dark:text-white">{{ $schedule->tanggal }} ({{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }})</span>
            </div>
            <div class="flex justify-between text-lg font-bold">
                <span class="text-gray-800 dark:text-white">Total</span>
                <span class="text-blue-600 dark:text-blue-400">Rp {{ number_format($schedule->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="space-y-3">
            <button id="pay-button" class="w-full rounded-lg bg-brand-600 px-4 py-3 text-center text-sm font-bold text-white hover:bg-brand-700 transition-colors cursor-pointer shadow-md">
                Lanjutkan Pembayaran
            </button>

            <form action="{{ route('payment.reset', $schedule->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-gray-100 px-4 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    Ganti Metode Pembayaran
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script type="text/javascript">
    document.getElementById('pay-button').onclick = function () {
        var snapToken = '{{ $schedule->snap_token }}';
        var clientKey = '{{ config("services.midtrans.client_key") }}';
        
        if (!clientKey) {
            alert('Error: MIDTRANS_CLIENT_KEY belum diisi di .env!');
            return;
        }
        
        if (!snapToken) {
            alert('Error: Token Snap tidak valid atau kosong dari server!');
            return;
        }

        snap.pay(snapToken, {
            onSuccess: function (result) {
                window.location.href = "{{ route('jadwal.index') }}?order_id=" + result.order_id + "&transaction_status=" + result.transaction_status;
            },
            onPending: function (result) {
                window.location.href = "{{ route('jadwal.index') }}?order_id=" + result.order_id + "&transaction_status=" + result.transaction_status;
            },
            onError: function (result) {
                alert("Pembayaran gagal!");
            },
            onClose: function () {
                // console.log('customer closed the popup without finishing the payment');
            }
        });
    };
</script>
@endsection
