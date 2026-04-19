@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Jadwal Belajar" />

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

    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Jadwal Belajar Kamu</h3>
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Guru</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Jam</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Catatan</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Pembayaran</th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $jadwal)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $jadwal->teacherProfile->user->name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $jadwal->tanggal }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $jadwal->catatan ?? '-' }}</td>
                            <td class="px-5 py-4">
                                @if($jadwal->status === 'confirmed')
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">Confirmed</span>
                                @elseif($jadwal->status === 'pending')
                                    <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">Pending</span>
                                @elseif($jadwal->status === 'selesai')
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Selesai</span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">Batal</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($jadwal->payment_status === 'paid')
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">Lunas</span>
                                @elseif($jadwal->payment_status === 'unpaid')
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">Belum Bayar</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">Gagal</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex gap-3 items-center">
                                    @if($jadwal->payment_status === 'paid')
                                        <a href="{{ route('schedule.invoice', $jadwal->id) }}" target="_blank" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors bg-blue-50 px-3 py-1 rounded-md">Cetak Invoice</a>
                                    @elseif($jadwal->payment_status === 'unpaid' && ($jadwal->status === 'pending' || $jadwal->status === 'confirmed'))
                                        <a href="{{ route('payment.pay', $jadwal->id) }}" class="text-sm font-medium text-brand-600 hover:text-brand-800 transition-colors bg-brand-50 px-3 py-1 rounded-md">Bayar</a>
                                    @endif
----------------------------------------------
                                      {{-- Form Tulis review (hidden by default) --}}
                        @if($punyaJadwalSelesai && !$sudahReview)
                            <div id="review-{{ $teacher->id }}" class="hidden mt-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                                <form action="{{ route('review.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="teacher_profile_id" value="{{ $teacher->id }}">
                                    <div class="flex items-center gap-2 mb-2">
                                        <label class="text-xs text-gray-500 dark:text-gray-400">Rating:</label>
                                        <select name="rating" required class="h-8 rounded-lg border border-gray-300 px-2 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                            <option value="5">5</option>
                                            <option value="4">4</option>
                                            <option value="3">3</option>
                                            <option value="2">2</option>
                                            <option value="1">1</option>
                                        </select>
                                    </div>
                                    <textarea name="ulasan" rows="2" placeholder="Tulis ulasan pengalaman belajar di sini..." required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                                    <button type="submit" class="mt-2 text-xs bg-brand-500 hover:bg-brand-600 rounded-lg px-3 py-1.5 text-white">Kirim Review</button>
                                </form>
                            </div>
                        @endif
--------------------------------------
                                    @if($jadwal->status === 'pending' || $jadwal->status === 'confirmed')
                                        <form action="{{ route('jadwal.cancel', $jadwal->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-sm text-red-500 hover:underline">Batalkan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada jadwal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
