<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\InfoArsipSkot;
use App\Models\JenisDokumen;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Operator;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InfoArsipSkotController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api',['except'=>['login', 'register']]);
    }


    public function simpanSkot(Request $request)
    {
        // Validasi input
        $validator = app('validator')->make($request->all(), [
            'JUMLAH_BERKAS' => 'nullable|integer',
            'NO_BUKU' => 'nullable|integer',
            'NO_RAK' => 'nullable|integer',
            'NO_BARIS' => 'nullable|integer',
            'NO_BOKS' => 'nullable|integer',
            'LOK_SIMPAN' => 'nullable|string|max:25',
            'KETERANGAN'=>'nullable|string|max:15',
            'NO_DOK_SKOT' => 'required|string|max:25|unique:info_arsip_skot',
            'NAMA' => 'required|string|max:50',
            'NAMA_PANGGIL' => 'required|string|max:25',
            'NIK' => 'required|integer',
            'JENIS_KELAMIN' => 'required|string|max:15',
            'TEMPAT_LAHIR' => 'required|string|max:25',
            'TANGGAL_LAHIR' => 'required|date',
            'AGAMA' => 'required|string|max:15',
            'STATUS_KAWIN' => 'required|string|max:15',
            'PEKERJAAN' => 'required|string|max:25',
            'ALAMAT_ASAL' => 'required|string|max:50',
            'PROV_ASAL' => 'required|string|max:25',
            'KOTA_ASAL' => 'required|string|max:25',
            'KEC_ASAL' => 'required|string|max:25',
            'KEL_ASAL' => 'required|string|max:25',
            'ALAMAT' => 'required|string|max:50',
            'PROV' => 'required|string|max:50',
            'KOTA' => 'required|string|max:50',
            'ID_KELURAHAN' =>'required|integer',
            'ID_KECAMATAN' => 'required|integer',
            'TAHUN_PEMBUATAN_DOK_SKOT' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SKOT' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'SKOT')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_SKOT = $request->input('NO_DOK_SKOT');
        $arsip->JUMLAH_BERKAS = $request->input('JUMLAH_BERKAS');
        $arsip->NO_BUKU = $request->input('NO_BUKU');
        $arsip->NO_RAK = $request->input('NO_RAK');
        $arsip->NO_BARIS = $request->input('NO_BARIS');
        $arsip->NO_BOKS = $request->input('NO_BOKS');
        $arsip->LOK_SIMPAN = $request->input('LOK_SIMPAN');
        $arsip->KETERANGAN = $request->input('KETERANGAN');
        $arsip->ID_DOKUMEN = $idDokumen;
        $arsip->TANGGAL_PINDAI = Carbon::now();
        $arsip->save();

        $idArsip = $arsip->ID_ARSIP;
        $infoArsipSkot = new InfoArsipSkot();
        $infoArsipSkot->ID_ARSIP = $idArsip;
        $infoArsipSkot->NO_DOK_SKOT = $arsip->NO_DOK_SKOT;
        $infoArsipSkot->NAMA = $request->input('NAMA');
        $infoArsipSkot->NAMA_PANGGIL = $request->input('NAMA_PANGGIL');
        $infoArsipSkot->NIK = $request->input('NIK');
        $infoArsipSkot->JENIS_KELAMIN = $request->input('JENIS_KELAMIN');
        $infoArsipSkot->TEMPAT_LAHIR = $request->input('TEMPAT_LAHIR');
        $infoArsipSkot->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
        $infoArsipSkot->AGAMA = $request->input('AGAMA');
        $infoArsipSkot->STATUS_KAWIN = $request->input('STATUS_KAWIN');
        $infoArsipSkot->PEKERJAAN = $request->input('PEKERJAAN');
        $infoArsipSkot->ALAMAT_ASAL = $request->input('ALAMAT_ASAL');
        $infoArsipSkot->PROV_ASAL = $request->input('PROV_ASAL');
        $infoArsipSkot->KOTA_ASAL = $request->input('KOTA_ASAL');
        $infoArsipSkot->KEC_ASAL = $request->input('KEC_ASAL');
        $infoArsipSkot->KEL_ASAL = $request->input('KEL_ASAL');
        $infoArsipSkot->ALAMAT = $request->input('ALAMAT');
        $infoArsipSkot->PROV = $request->input('PROV');
        $infoArsipSkot->KOTA = $request->input('KOTA');
        $infoArsipSkot->TAHUN_PEMBUATAN_DOK_SKOT = $request->input('TAHUN_PEMBUATAN_DOK_SKOT');

        $kecamatan = Kecamatan::find($request->input('ID_KECAMATAN'));
        // Jika kecamatan tidak ditemukan
        if (!$kecamatan) {
            return response()->json(['error' => 'Kecamatan tidak valid'], 400);
        }
        $infoArsipSkot->ID_KECAMATAN = $kecamatan->ID_KECAMATAN;

        $id_kelurahan = $request->input('ID_KELURAHAN');
        $kelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)
                    ->where('ID_KECAMATAN', $kecamatan->ID_KECAMATAN)
                    ->first();
        // Jika kelurahan tidak ditemukan
        if (!$kelurahan) {
            return response()->json(['error' => 'Kelurahan tidak ditemukan sesuai kecamatan yang dipilih'], 400);
        }
        $infoArsipSkot->ID_KELURAHAN = $kelurahan->ID_KELURAHAN;

        $tahunPembuatanDokSkot = $infoArsipSkot->TAHUN_PEMBUATAN_DOK_SKOT;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_SKOT'
        ];

        // Loop melalui setiap field file untuk menyimpannya
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $allowedExtensions = ['pdf'];
                $file = $request->file($field);
                $extension = $file->getClientOriginalExtension();

                // Periksa apakah ekstensi file diizinkan
                if (in_array($extension, $allowedExtensions)) {
                    // Periksa ukuran file
                    if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                        $fileName = $file->getClientOriginalName();
                        $folderPath = $tahunPembuatanDokSkot . '/Arsip Skot';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipSkot->$field = $fileName;
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                            'field' => $field
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                        'field' => $field
                    ], 400);
                }
            }
        }
        $infoArsipSkot->save();

        if ($infoArsipSkot) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Skot berhasil ditambahkan',
                'data' => $infoArsipSkot,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Skot gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    public function updateSkot(Request $request, $ID_ARSIP)
    {
        // Validasi input
        $validator = app('validator')->make($request->all(), [
            'JUMLAH_BERKAS' => 'nullable|integer',
            'NO_BUKU' => 'nullable|integer',
            'NO_RAK' => 'nullable|integer',
            'NO_BARIS' => 'nullable|integer',
            'NO_BOKS' => 'nullable|integer',
            'LOK_SIMPAN' => 'nullable|string|max:25',
            'KETERANGAN'=>'nullable|string|max:15',
            'NO_DOK_SKOT' => 'required|string|max:25',
            'NAMA' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SKOT' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'SKOT')->value('ID_DOKUMEN');
        // Temukan arsip berdasarkan ID_ARSIP
        $arsip = Arsip::find($ID_ARSIP);
        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Arsip tidak ditemukan',
            ], 404);
        }

        // Simpan data arsip sebelum diupdate untuk memeriksa apakah ada perubahan
        $arsipBeforeUpdate = clone $arsip;

        // update data ke dalam tabel "arsip"
        $arsip->NO_DOK_SKOT = $request->input('NO_DOK_SKOT');
        $arsip->JUMLAH_BERKAS = $request->input('JUMLAH_BERKAS');
        $arsip->NO_BUKU = $request->input('NO_BUKU');
        $arsip->NO_RAK = $request->input('NO_RAK');
        $arsip->NO_BARIS = $request->input('NO_BARIS');
        $arsip->NO_BOKS = $request->input('NO_BOKS');
        $arsip->LOK_SIMPAN = $request->input('LOK_SIMPAN');
        $arsip->KETERANGAN = $request->input('KETERANGAN');
        $arsip->ID_DOKUMEN = $idDokumen;
        $arsip->TANGGAL_PINDAI = Carbon::now();

        // Periksa apakah ada perubahan pada data arsip
        if (!$arsip->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada perubahan pada Arsip',
                'data' => $arsipBeforeUpdate,
            ], 200);
        }
        $arsip->save();

        $infoArsipSkot = InfoArsipSkot::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipSkot) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip SKOT tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip SKOT sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipSkotBeforeUpdate = clone $infoArsipSkot;

        $infoArsipSkot->NO_DOK_SKOT = $arsip->NO_DOK_SKOT;
        $infoArsipSkot->NAMA = $request->input('NAMA');

        $tahunPembuatanDokSkot = $infoArsipSkot->TAHUN_PEMBUATAN_DOK_SKOT;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_SKOT'
        ];

        // Loop melalui setiap field file untuk menyimpannya
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $allowedExtensions = ['pdf'];
                $file = $request->file($field);
                $extension = $file->getClientOriginalExtension();

                // Periksa apakah ekstensi file diizinkan
                if (in_array($extension, $allowedExtensions)) {
                    // Periksa ukuran file
                    if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                        $fileName = $file->getClientOriginalName();
                        $folderPath = $tahunPembuatanDokSkot . '/Arsip Skot';
                        $oldFileName = $infoArsipSkot->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipSkot->$field = $fileName;
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                            'field' => $field
                        ], 400);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                        'field' => $field
                    ], 400);
                }
            }
        }

        // Periksa apakah ada perubahan pada data info arsip pengesahan
        if (!$infoArsipSkot->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Skot tidak ada perubahan',
                'data' => $infoArsipSkotBeforeUpdate,
            ], 200);
        }
        $infoArsipSkot ->save();

        unset($infoArsipSkot->NAMA_PANGGIL);
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'arsip' => [
                'JUMLAH_BERKAS' => $arsip->JUMLAH_BERKAS,
                'NO_BUKU' => $arsip->NO_BUKU,
                'NO_RAK' => $arsip->NO_RAK,
                'NO_BARIS' => $arsip->NO_BARIS,
                'NO_BOKS' => $arsip->NO_BOKS,
                'LOK_SIMPAN' => $arsip->LOK_SIMPAN,
                'KETERANGAN' => $arsip->KETERANGAN,
                'ID_DOKUMEN' => $arsip->ID_DOKUMEN,
            ],
            'info_arsip_Skot' => $infoArsipSkot,
        ], 200);
    }
}
