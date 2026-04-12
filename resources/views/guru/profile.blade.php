@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="{{ isset($profile) ? 'Edit Profil Guru' : 'Isi Profil Guru' }}" />

<div class="space-y-6">

    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                {{ isset($profile) ? 'Edit Profil' : 'Form Profil Guru' }}
            </h3>
        </div>

        <form action="{{ isset($profile) ? route('guru.profil.update') : route('guru.profil.store') }}" method="POST" class="p-6">
            @csrf
            @if(isset($profile))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                {{-- Mata Pelajaran --}}
                  <div x-data="{
                    open: false,
                    selected: '{{ old('subject', $profile->subject ?? '') }}',
                    subjects: ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi']
                }">
                    <label class="block mb-1 text-sm font-medium">Mata Pelajaran <span class="text-error-500">*</span></label>

                    <div class="relative">
                        <!-- Input -->
                        <input 
                            type="text"
                            name="subject"
                            x-model="selected"
                            @focus="open = true"
                            @click.away="open = false"
                            placeholder="Pilih atau ketik mata pelajaran"
                            required
                            class="w-full border rounded-lg px-4 py-2 border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >

                        <!-- Dropdown -->
                        <div x-show="open" class="absolute w-full bg-white border border-gray-200 mt-1 rounded-lg shadow-lg z-10 max-h-40 overflow-y-auto dark:bg-gray-800 dark:border-gray-700">
                            <template x-for="item in subjects" :key="item">
                                <div 
                                    @click="selected = item; open = false"
                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm dark:hover:bg-gray-700 dark:text-gray-200"
                                    x-text="item"
                                ></div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Jenjang --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Jenjang <span class="text-error-500">*</span>
                    </label>
                    <select name="jenjang" required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Pilih Jenjang</option>
                        <option value="SD" {{ old('jenjang', $profile->jenjang ?? '') === 'SD' ? 'selected' : '' }}>SD</option>
                        <option value="SMP" {{ old('jenjang', $profile->jenjang ?? '') === 'SMP' ? 'selected' : '' }}>SMP</option>
                        <option value="SMA" {{ old('jenjang', $profile->jenjang ?? '') === 'SMA' ? 'selected' : '' }}>SMA</option>
                    </select>
                </div>

                {{-- Pengalaman --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pengalaman</label>
                    @php
                        $expYears = 0; $expMonths = 0;
                        if(isset($profile->experience) && preg_match('/(\d+)\s*tahun\s*(\d+)\s*bulan/i', $profile->experience, $matches)) {
                            $expYears = $matches[1];
                            $expMonths = $matches[2];
                        }
                    @endphp
                    <div class="flex items-center gap-3">
                        <!-- Tahun -->
                        <div class="flex items-center">
                            <input type="number" name="experience_years" min="0" max="50" value="{{ old('experience_years', $expYears) }}"
                                class="h-11 w-20 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">tahun</span>
                        </div>
                        <!-- Bulan -->
                        <div class="flex items-center">
                            <input type="number" name="experience_months" min="0" max="12" value="{{ old('experience_months', $expMonths) }}"
                                class="h-11 w-20 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">bulan</span>
                        </div>
                    </div>
                </div>

                {{-- Pendidikan --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pendidikan</label>
                    <input type="text" name="education" value="{{ old('education', $profile->education ?? '') }}" placeholder="Contoh: S1 Pendidikan Matematika"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>

                {{-- Harga --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Harga per Sesi (Rp) <span class="text-error-500">*</span>
                    </label>
                    <input type="number" name="price" value="{{ old('price', $profile->price ?? '') }}" placeholder="75000" required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>

                {{-- Lokasi --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lokasi Kecamatan</label>
                    <select name="location" class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Pilih Kecamatan</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc }}" {{ old('location', $profile->location ?? '') == $loc ? 'selected' : '' }}>
                                {{ $loc }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Ketersediaan --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ketersediaan Hari</label>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        @php
                            $days = ['Senin','Selasa','Rabu','Kamis',"Jumat",'Sabtu','Minggu'];
                            $selectedDays = old('availability', isset($profile->availability) ? explode(', ', $profile->availability) : []);
                        @endphp
                        @foreach($days as $day)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="availability[]" value="{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $day }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Gender --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Gender</label>
                    <select name="gender"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Pilih Gender</option>
                        <option value="laki-laki" {{ old('gender', $profile->gender ?? '') === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan" {{ old('gender', $profile->gender ?? '') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            {{-- Detail --}}
            <div class="mt-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Detail Tambahan</label>
                <textarea name="detail" rows="3" placeholder="Ceritakan tentang gaya mengajar kamu"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('detail', $profile->detail ?? '') }}</textarea>
            </div>

            {{-- Rekening --}}
            <div class="mt-8 border-t border-gray-200 pt-5 dark:border-gray-800">
                <h4 class="mb-4 text-base font-medium text-gray-800 dark:text-white/90">Informasi Pencairan (Rekening/e-Wallet)</h4>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Nama Bank --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Bank / e-Wallet <span class="text-error-500">*</span>
                        </label>
                        <select name="bank_name" required
                            class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">Pilih Bank / e-Wallet</option>
                            <option value="BCA" {{ old('bank_name', $profile->bank_name ?? '') === 'BCA' ? 'selected' : '' }}>BCA</option>
                            <option value="Mandiri" {{ old('bank_name', $profile->bank_name ?? '') === 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                            <option value="BNI" {{ old('bank_name', $profile->bank_name ?? '') === 'BNI' ? 'selected' : '' }}>BNI</option>
                            <option value="BRI" {{ old('bank_name', $profile->bank_name ?? '') === 'BRI' ? 'selected' : '' }}>BRI</option>
                            <option value="BSI" {{ old('bank_name', $profile->bank_name ?? '') === 'BSI' ? 'selected' : '' }}>BSI</option>
                            <option value="GoPay" {{ old('bank_name', $profile->bank_name ?? '') === 'GoPay' ? 'selected' : '' }}>GoPay</option>
                            <option value="OVO" {{ old('bank_name', $profile->bank_name ?? '') === 'OVO' ? 'selected' : '' }}>OVO</option>
                            <option value="DANA" {{ old('bank_name', $profile->bank_name ?? '') === 'DANA' ? 'selected' : '' }}>DANA</option>
                            <option value="ShopeePay" {{ old('bank_name', $profile->bank_name ?? '') === 'ShopeePay' ? 'selected' : '' }}>ShopeePay</option>
                            <option value="LinkAja" {{ old('bank_name', $profile->bank_name ?? '') === 'LinkAja' ? 'selected' : '' }}>LinkAja</option>
                        </select>
                    </div>

                    {{-- No Rekening --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nomor Rekening / HP <span class="text-error-500">*</span>
                        </label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $profile->bank_account_number ?? '') }}" required placeholder="Contoh: 1234567890"
                            class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    {{-- Nama Pemilik --}}
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nama Pemilik Rekening <span class="text-error-500">*</span>
                        </label>
                        <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $profile->bank_account_name ?? '') }}" required placeholder="Sesuai buku tabungan / aplikasi"
                            class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                    class="bg-brand-500 hover:bg-brand-600 rounded-lg px-5 py-3 text-sm font-medium text-white">
                    {{ isset($profile) ? 'Update Profil' : 'Simpan Profil' }}
                </button>
                <a href="{{ route('dashboard') }}"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
