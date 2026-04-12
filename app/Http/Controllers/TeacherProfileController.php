<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use Illuminate\Support\Facades\Auth;

class TeacherProfileController extends Controller
{
    // Form isi profil baru
    public function create()
    {
        $locations = $this->getLocations();
        return view('guru.profile', compact('locations'));
    }

    // Simpan profil baru
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:100',
            'jenjang' => 'required|string|max:50',
            'price' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_account_name' => 'required|string'
        ]);

        $years = $request->input('experience_years', 0);
        $months = $request->input('experience_months', 0);

        TeacherProfile::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'experience' => $years . ' tahun ' . $months . ' bulan',
            'education' => $request->education,
            'price' => (int) round($request->price),
            'availability' => implode(', ', $request->availability ?? []),
            'gender' => $request->gender,
            'jenjang' => $request->jenjang,
            'detail' => $request->detail,
            'location' => $request->location,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'status' => 'pending'
        ]);

        return redirect()->route('dashboard')->with('success', 'Profil berhasil disimpan, menunggu persetujuan admin.');
    }

    // Form edit profil
    public function edit()
    {
        $profile = TeacherProfile::where('user_id', Auth::id())->first();
        $locations = $this->getLocations();
        return view('guru.profile', compact('profile', 'locations'));
    }

    // Update profil
    public function update(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:100',
            'jenjang' => 'required|string|max:50',
            'price' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_account_name' => 'required|string'
        ]);

        $years = $request->input('experience_years', 0);
        $months = $request->input('experience_months', 0);

        $profile = TeacherProfile::where('user_id', Auth::id())->first();

        $profile->update([
            'subject' => $request->subject,
            'experience' => $years . ' tahun ' . $months . ' bulan',
            'education' => $request->education,
            'price' => (int) round($request->price),
            'availability' => implode(', ', $request->availability ?? []),
            'gender' => $request->gender,
            'jenjang' => $request->jenjang,
            'detail' => $request->detail,
            'location' => $request->location,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'status' => 'pending' // reset ke pending setelah edit
        ]);

        return redirect()->route('dashboard')->with('success', 'Profil diperbarui, menunggu persetujuan ulang admin.');
    }

    // Guru lihat review yang masuk
    public function reviews()
    {
        $profile = TeacherProfile::where('user_id', Auth::id())->first();

        $reviews = collect();
        if ($profile) {
            $reviews = $profile->reviews()->with('user')->latest()->get();
        }

        return view('guru.reviews', compact('reviews'));
    }

    // 🔥 Helper lokasi kecamatan Karawang
    private function getLocations()
    {
        return [
            'Banyusari','Batujaya','Ciampel','Cibuaya','Cikampek',
            'Cilamaya Kulon','Cilamaya Wetan','Cilebar','Jatisari','Jayakerta',
            'Karawang Barat','Karawang Timur','Klari','Kotabaru','Kutawaluya',
            'Lemahabang','Majalaya','Pakisjaya','Pangkalan','Pedes',
            'Purwasari','Rawamerta','Rengasdengklok','Tegalwaru','Telagasari',
            'Telukjambe Barat','Telukjambe Timur','Tempuran','Tirtajaya','Tirtamulya'
        ];
    }
}