```blade
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
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                Jadwal Belajar Kamu
            </h3>
        </div>

        <div class="max-w-full overflow-x-auto">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            Guru
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            Tanggal
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            Jam
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            Catatan
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            Status
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            Pembayaran
                        </th>
                        <th class="px-5 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($schedules as $jadwal)

                        @php
                            $teacherId = $jadwal->teacherProfile->id;

                            // cek apakah siswa sudah pernah review guru ini
                            $sudahReview = \App\Models\Review::where('student_id', auth()->id())
                                ->where('teacher_profile_id', $teacherId)
                                ->exists();
                        @endphp

                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            {{-- Guru --}}
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $jadwal->teacherProfile->user->name }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $jadwal->tanggal }}
                            </td>

                            {{-- Jam --}}
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                            </td>

                            {{-- Catatan --}}
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $jadwal->catatan ?? '-' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @if($jadwal->status === 'confirmed')
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Confirmed
                                    </span>

                                @elseif($jadwal->status === 'pending')
                                    <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                        Pending
                                    </span>

                                @elseif($jadwal->status === 'selesai')
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                        Selesai
                                    </span>

                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                        Batal
                                    </span>
                                @endif
                            </td>

                            {{-- Payment --}}
                            <td class="px-5 py-4">
                                @if($jadwal->payment_status === 'paid')
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Lunas
                                    </span>

                                @elseif($jadwal->payment_status === 'unpaid')
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                        Belum Bayar
                                    </span>

                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                        Gagal
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-2">

                                    {{-- jika sudah bayar --}}
                                    @if($jadwal->payment_status === 'paid')
                                        <a
                                            href="{{ route('schedule.invoice', $jadwal->id) }}"
                                            target="_blank"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors bg-blue-50 px-3 py-1 rounded-md w-fit"
                                        >
                                            Cetak Bukti Pembayaran
                                        </a>
                                    @endif

                                    {{-- jika belum bayar --}}
                                    @if($jadwal->payment_status === 'unpaid' && ($jadwal->status === 'pending' || $jadwal->status === 'confirmed'))
                                        <a
                                            href="{{ route('payment.pay', $jadwal->id) }}"
                                            class="text-sm font-medium text-brand-600 hover:text-brand-800 transition-colors bg-brand-50 px-3 py-1 rounded-md w-fit"
                                        >
                                            Bayar
                                        </a>
                                    @endif

                                    {{-- tombol batal jika pending / confirmed --}}
                                    @if($jadwal->status === 'pending' || $jadwal->status === 'confirmed')
                                        <form
                                            action="{{ route('jadwal.cancel', $jadwal->id) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            <button
                                                type="submit"
                                                class="text-sm text-red-500 hover:underline"
                                            >
                                                Batalkan
                                            </button>
                                        </form>
                                    @endif

                                    {{-- jika jadwal selesai dan belum review -> tampil form review --}}
                                    @if($jadwal->status === 'selesai' && !$sudahReview)

                                        <button
                                            type="button"
                                            onclick="toggleReview({{ $jadwal->id }})"
                                            class="text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 px-3 py-1 rounded-md w-fit"
                                        >
                                            Tulis Review
                                        </button>

                                        <div
                                            id="review-{{ $jadwal->id }}"
                                            class="hidden mt-2 border border-gray-200 rounded-lg p-3 bg-gray-50 dark:border-gray-700 dark:bg-gray-900"
                                        >
                                            <form action="{{ route('review.store') }}" method="POST">
                                                @csrf

                                                <input
                                                    type="hidden"
                                                    name="teacher_profile_id"
                                                    value="{{ $teacherId }}"
                                                >

                                                <div class="mb-3">
                                                    <label class="text-xs text-gray-500 dark:text-gray-400">
                                                        Rating
                                                    </label>

                                                    <select
                                                        name="rating"
                                                        required
                                                        class="mt-1 h-9 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                    >
                                                        <option value="5">5</option>
                                                        <option value="4">4</option>
                                                        <option value="3">3</option>
                                                        <option value="2">2</option>
                                                        <option value="1">1</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <textarea
                                                        name="ulasan"
                                                        rows="3"
                                                        required
                                                        placeholder="Tulis pengalaman belajar dengan guru ini..."
                                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                    ></textarea>
                                                </div>

                                                <button
                                                    type="submit"
                                                    class="mt-3 text-sm bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2 text-white"
                                                >
                                                    Kirim Review
                                                </button>
                                            </form>
                                        </div>
                                    @endif

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                            >
                                Belum ada jadwal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleReview(id) {
        const form = document.getElementById('review-' + id);

        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
        } else {
            form.classList.add('hidden');
        }
    }
</script>

@endsection
```
