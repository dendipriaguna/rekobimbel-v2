@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Pendapatan Saya" />

<div class="space-y-6">

    @session('success')
        <x-ui.alert variant="success">
            {{ $value }}
        </x-ui.alert>
    @endsession

    @session('error')
        <x-ui.alert variant="error" class="bg-red-50 text-red-600 border border-red-200 p-4 rounded-md">
            {{ $value }}
        </x-ui.alert>
    @endsession
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">Kesalahan pengisian:</span>
            <ul class="mt-1.5 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Kartu Saldo --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Saldo Tersedia</p>
                    <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($profile->balance ?? 0, 0, ',', '.') }}
                    </h3>
                </div>
            </div>
            
            <div class="mt-4 sm:mt-0">
                <form action="{{ route('guru.withdraw') }}" method="POST" class="flex items-center gap-2" onsubmit="return confirm('Apakah Anda yakin ingin menarik dana sejumlah yang Anda masukkan? Saldo Anda akan segera dipotong.')">
                    @csrf
                    <div class="relative w-48">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                        <input type="number" name="amount" min="1000" max="{{ $profile->balance ?? 0 }}" value="{{ $profile->balance ?? 0 }}" required class="w-full rounded-lg border border-gray-300 pl-9 pr-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Nominal" {{ ($profile->balance ?? 0) <= 0 ? 'disabled' : '' }}>
                    </div>
                <button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 focus:outline-none focus:ring focus:ring-brand-500/20 disabled:scale-100 disabled:opacity-50" {{ ($profile->balance ?? 0) <= 0 ? 'disabled' : '' }}>
                        Tarik Saldo
                    </button>
                </form>
            </div>
        </div>
        <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Pencairan dana akan ditransfer oleh Admin ke rekening <strong>{{ $profile->bank_name ?? '-' }} ({{ $profile->bank_account_number ?? '-' }})</strong>.
            </p>
        </div>
    </div>

    {{-- Tabel Riwayat Pendapatan --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Riwayat Penghasilan dari Siswa</h3>
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Booking</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Siswa</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Order ID</th>
                        <th class="px-5 py-3 text-right text-sm font-medium text-gray-500 dark:text-gray-400">Penghasilan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $jadwal)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ \Carbon\Carbon::parse($jadwal->created_at)->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-800 dark:text-white">
                                {{ $jadwal->user->name }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $jadwal->order_id }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                    + Rp {{ number_format($jadwal->total_price * 0.8, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    (Setelah dipangkas admin 20% dari tiket Rp{{ number_format($jadwal->total_price, 0, ',', '.') }})
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada penghasilan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel Histori Penarikan (Withdrawals) --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Riwayat Penarikan Dana</h3>
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Pengajuan</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tujuan Bank</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-right text-sm font-medium text-gray-500 dark:text-gray-400">Nominal Tarik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $wd)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ \Carbon\Carbon::parse($wd->created_at)->format('d M Y, H:i') }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-800 dark:text-white">
                                <div><span class="font-bold">{{ $wd->bank_name }}</span> ({{ $wd->bank_account_number }})</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">a.n. {{ $wd->bank_account_name }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($wd->status === 'pending')
                                    <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">Sedang Diproses</span>
                                @elseif($wd->status === 'approved')
                                    <div class="flex flex-col items-start">
                                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">Sukses Ditransfer</span>
                                        @if($wd->proof_image)
                                            <a href="{{ asset('storage/' . $wd->proof_image) }}" target="_blank" class="mt-1 text-xs text-blue-600 hover:underline">Lihat Bukti</a>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right text-sm font-bold text-gray-900 dark:text-white">
                                Rp {{ number_format($wd->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat penarikan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
