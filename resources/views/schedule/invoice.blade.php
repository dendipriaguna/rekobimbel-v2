<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $schedule->order_id }}</title>
    <!-- Gunakan Tailwind script dari cdn untuk kemudahan cetak di luar kerangka app.blade.php utama -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Agar saat di print ukurannya pas A4/receipt */
        @media print {
            body { background: white; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased py-10 print:py-0 print:bg-white flex justify-center">

    <div class="max-w-2xl w-full bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200 print:shadow-none print:border-none">
        <!-- Header -->
        <div class="bg-brand-600 px-8 py-6 flex justify-between items-center text-white" style="background-color: #551A8B;"> <!-- Solid Purple fallback -->
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">RekoBimbel</h1>
                <p class="text-sm opacity-80 mt-1">Sistem Penempatan Guru Privat</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold">INVOICE</p>
                <p class="text-sm opacity-80 font-mono mt-1">{{ Str::upper($schedule->order_id) }}</p>
            </div>
        </div>

        <!-- Info -->
        <div class="px-8 py-6 mb-4 flex justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Ditagihkan Kepada</p>
                <p class="font-bold text-lg text-gray-900">{{ $schedule->user->name }}</p>
                <p class="text-sm text-gray-600">{{ $schedule->user->email }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Tanggal Pesanan</p>
                <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($schedule->created_at)->format('d F Y') }}</p>
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mt-3 mb-1">Status Pembayaran</p>
                <p class="font-bold text-green-600 bg-green-50 px-2 py-1 inline-block rounded border border-green-200">LUNAS</p>
            </div>
        </div>

        <!-- Rincian -->
        <div class="px-8 pb-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-y border-gray-200 bg-gray-50 text-gray-600 text-sm">
                        <th class="py-3 px-4 font-semibold uppercase tracking-wider">Deskripsi Layanan</th>
                        <th class="py-3 px-4 font-semibold uppercase tracking-wider text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <!-- Base Service -->
                    <tr class="border-b border-gray-100">
                        <td class="py-4 px-4 align-top">
                            <p class="font-bold text-gray-900">Les Privat - {{ $schedule->teacherProfile->subject }}</p>
                            <p class="text-gray-500 mt-1">Guru: {{ $schedule->teacherProfile->user->name }}</p>
                            <p class="text-gray-500">Jadwal: {{ \Carbon\Carbon::parse($schedule->tanggal)->format('d/m/Y') }} ({{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }})</p>
                        </td>
                        <td class="py-4 px-4 text-right align-top text-gray-900">
                            Rp {{ number_format($schedule->total_price * 0.8, 0, ',', '.') }}
                        </td>
                    </tr>
                    
                    <!-- Admin Fee -->
                    <tr class="border-b border-gray-200">
                        <td class="py-4 px-4 align-top">
                            <p class="font-semibold text-gray-700">Biaya Layanan Platform / Admin</p>
                            <p class="text-xs text-gray-500 mt-1">20% dari total pesanan untuk perawatan operasional RekoBimbel.</p>
                        </td>
                        <td class="py-4 px-4 text-right align-top text-gray-900">
                            Rp {{ number_format($schedule->total_price * 0.2, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
                <!-- Total -->
                <tfoot>
                    <tr>
                        <td class="py-6 px-4 text-right font-bold text-gray-700 uppercase tracking-wider">Total Pembayaran</td>
                        <td class="py-6 px-4 text-right text-2xl font-black text-brand-600" style="color: #551A8B">
                            Rp {{ number_format($schedule->total_price, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Footnote -->
            <div class="mt-8 text-center text-sm text-gray-500 space-y-2 border-t border-gray-200 pt-6">
                <p>Terima kasih telah menggunakan layanan RekoBimbel!</p>
                <p>Dokumen ini adalah bukti pembayaran digital yang sah. Jika Anda memiliki pertanyaan seputar tagihan, silakan hubungi tim dukungan kami.</p>
            </div>
            
            <!-- Print Button (No Print) -->
            <div class="mt-8 text-center no-print">
                <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-2 rounded-lg shadow font-medium hover:bg-gray-700 transition">
                    Print Invoice / Simpan PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Auto trigger print dialog on specific platforms if needed -->
    <script>
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
