# Rangkuman Implementasi RekoBimbel (Project Log)

Dokumen ini berisi rangkuman seluruh fitur krusial yang telah dirancang, ditulis, dan diimplementasikan dari awal hingga selesai pada proyek RekoBimbel. Aplikasi dibangun menggunakan konsep *Mobile-First Design* dengan stack Laravel + Tailwind CSS + Alpine.js.

---

## 🔒 1. Sistem Otentikasi & Keamanan Level Produksi
Kita tidak menggunakan sistem registrasi/login standar konvensional demi menjaga kualitas dan validitas lalu lintas data pengguna.
*   **OTP WhatsApp (Fonnte API):** Setiap user baru wajib memverifikasi kepemilikan nomor telepon via WhatsApp OTP sebelum dapat mengakses *dashboard*. Ini memblokir kemunculan *fake account* (akun fiktif).
*   **2-Step Verification (2FA):** Menambahkan lapisan keamanan opsional. Jika aktif, user dimintai konfirmasi kode sandi tambahan tiap kali login.
*   **Password Reset via WA:** Link reset sandi dikirim langsung ke WhatsApp alih-alih Email untuk mempertahankan konsistensi ekosistem nomor *handphone*.

---

## 👥 2. Manajemen Peran & Logika Inti
Sistem mendelegasikan 3 peran absolut dengan batasan *middleware*:
*   **Siswa:** Fokus pada mencari profil guru, menetapkan harga/budget, dan *booking*.
*   **Guru:** Wajib mengisi kelengkapan Profil Keahlian (Harga tiket per sesi, mata pelajaran, foto, deskripsi) dan hanya bisa muncul di radar pencarian jika status profilnya sudah **Di-Approve** oleh Admin.
*   **Admin:** Memegang kendali mutlak (CMS) atas Validasi Profil Guru dan Alur Kas Keuangan.

---

## 🧠 3. Scoring Engine (Algoritma Pencocokan Guru & Siswa)
Alih-alih menyuruh siswa *scroll* ratusan guru, sistem dibangun dengan pendekatan **Sistem Rekomendasi Cerdas** bertipe *Hybrid* (Content-based + Rule-based) ketika siswa mengisi "Preferensi Belajar".
**Distribusi Skor Otomatis:**
*   Kesamaan Mata Pelajaran: `+40 Poin`
*   Kesamaan Jenjang Studi: `+25 Poin`
*   Kecocokan Harga Bawah Budget: `+20 Poin` (Ditambah poin proporsional sisa selisih uang).
*   Kecocokan Waktu Lowong & Gender: `+15` dan `+10 Poin`
*   Nilai Pengalaman Mengajar Mengkatrol skor bonus.
Hasil dari kalkulasi akan di-*Sort Descending* sehingga guru yang paling sesuai muncul di puncak rekomendasi siswa!

---

## 📅 4. Sistem Negosiasi & Booking Jadwal
*   Setiap pengajuan belajar di-*track* di tabel `schedules`. Alur pertukaran status: `Pending -> Confirmed -> Selesai / Batal`.
*   Guru bisa menolak siswa jika *slot* dirasa penuh.
*   Sistem validasi ketat: Siswa **HANYA** bisa ngasih *Review* & *Rating* Bintang 1-5 apabila status jadwal belajar benar-benar telah mencapai fase **Selesai**. 

---

## 💸 5. Integrasi Payment Gateway & Sistem 'Escrow'
Transaksi dilakukan secara digital murni (Bebas Cash) dengan keamanan ekstra.
*   **Midtrans Snap API:** Murid membayar lewat jendela *pop-up* keren tanpa harus meninggalkan halaman aplikasi (Mendukung VA, GoPay, QRIS, dsb).
*   **Otomatisasi Webhook:** Saat pembayaran sukses, Midtrans mengabari laravel secara '*background*' (Webhook) dan mengubah status jadwal menjadi `paid`.
*   **Sistem Dana Ditahan (Escrow):** Duit yang ditransfer murid TIDAK LANGSUNG masuk ke kantong guru. Duit ditahan oleh sistem. Guru baru mendapatkan uang di saldonya HANYA ketika kelas sudah diajarkan (tombol **Selesai** dipencet oleh Guru). Murni perlindungan tingkat dewa terhadap skenario Guru mangkir/batal ajar!

---

## 🏦 6. Pembagian Cuan (Revenue Sharing) & Invoicing
Menerapkan *core-value* bisnis yang menyejahterakan perusahaan sekaligus mitra pengajar.
*   **Split Fee 80% / 20%:** Ketika jadwal usai, `80%` dari harga tiket mengalir menjadi tambahan **Saldo Guru**, dan `20%` mengendap otomatis menjadi profit mutlak bagi pemilik RekoBimbel (Platform Fee).
*   **Cetak Invoice Digital:** Murid memiliki hak atas transparansi. Jika status `Lunas`, murid mendapatkan akses mencetak dokumen *Invoice* PDF rapi yang mencantumkan pembagian harga tiket belajar dan biaya aplikasinya.
*   **Riwayat Pendapatan Guru:** Guru disuguhkan tabel *history* transparansi yang mendaftar siswa mana saja yang sudah mereka ajar dan seberapa besar pertambahan Rp saldonya usai kena potongan *platform*.

---

## 🏧 7. Infrastruktur Withdrawal (Tarik Tunai) & Kas Admin
*   **Sistem Tarik Eceran:** Guru bisa menarik sebagian atau seluruh sisa dana (*withdrawals*) yang menumpuk di profil mereka ke Rekening Bank Asli mereka.
*   **Manajemen Kas Admin:** Admin disajikan tabel "Antrean Pencairan" di dalam *Dashboard*. Jika Admin menyetujui penarikan, Admin wajib mementukan **Approve** (sambil mengupload gambar resi M-banking sebagai bukti absolut). 
*   **Refund Otomatis:** Jika pencairan Ditolak, sistem otomatis memuntahkan kembali nominal yang ditarik secara utuh ke saldo *Teacher Profile* terkait. Jadi tidak ada uang (*cash*) yang tersangkut karena *error* administratif.
