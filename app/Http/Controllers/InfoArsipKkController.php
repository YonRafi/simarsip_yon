<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Arsip;
use App\Models\JenisDokumen;
use App\Models\InfoArsipKk;
use App\Models\HakAkses;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Operator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InfoArsipKkController extends Controller
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


    public function simpanKk(Request $request)
    {
        // Validasi input
        $validator = app('validator')->make($request->all(),[
            'JUMLAH_BERKAS' => 'nullable|integer',
            'NO_BUKU' => 'nullable|integer',
            'NO_RAK' => 'nullable|integer',
            'NO_BARIS' => 'nullable|integer',
            'NO_BOKS' => 'nullable|integer',
            'LOK_SIMPAN' => 'nullable|string|max:25',
            'KETERANGAN'=>'nullable|string|max:15',
            'NO_DOK_KK' => 'required|integer|unique:info_arsip_kk',
            'NAMA_KEPALA' => 'nullable|string|max:50',
            'ALAMAT' => 'nullable|string|max:50',
            'RT' => 'nullable',
            'RW' => 'nullable',
            'KODEPOS' => 'nullable|integer|',
            'PROV' => 'nullable|string|max:50',
            'KOTA' => 'nullable|string|max:50',
            'ID_KECAMATAN' => 'nullable|integer',
            'ID_KELURAHAN' => 'nullable|integer',
            'TAHUN_PEMBUATAN_DOK_KK' => 'nullable|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F101' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_NIKAH_CERAI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_PINDAH' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_PINDAH_LUAR' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_PENGGANTI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PUTUSAN_PRESIDEN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_PERISTIWA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_HILANG' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Kartu Keluarga')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_KK = $request->input('NO_DOK_KK');
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
        $idDokKk = $arsip->NO_DOK_KK;
        $infoArsipKk = new InfoArsipKk();
        $infoArsipKk ->ID_ARSIP = $idArsip;
        $infoArsipKk ->NO_DOK_KK = $idDokKk;
        $infoArsipKk->NAMA_KEPALA = $request->input('NAMA_KEPALA');
        $infoArsipKk->ALAMAT = $request->input('ALAMAT');
        $infoArsipKk->RT = $request->input('RT');
        $infoArsipKk->RW = $request->input('RW');
        $infoArsipKk->KODEPOS = $request->input('KODEPOS');
        $infoArsipKk->PROV = $request->input('PROV');
        $infoArsipKk->KOTA = $request->input('KOTA');
        $infoArsipKk->TAHUN_PEMBUATAN_DOK_KK = $request->input('TAHUN_PEMBUATAN_DOK_KK');

        $kecamatan = Kecamatan::find($request->input('ID_KECAMATAN'));
        // Jika kecamatan tidak ditemukan
        if (!$kecamatan) {
            return response()->json(['error' => 'Kecamatan tidak valid'], 400);
        }
        $infoArsipKk ->ID_KECAMATAN = $kecamatan->ID_KECAMATAN;

        $id_kelurahan = $request->input('ID_KELURAHAN');
        $kelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)
                    ->where('ID_KECAMATAN', $kecamatan->ID_KECAMATAN)
                    ->first();
        // Jika kelurahan tidak ditemukan
        if (!$kelurahan) {
            return response()->json(['error' => 'Kelurahan tidak ditemukan sesuai kecamatan yang dipilih'], 400);
        }
        $infoArsipKk ->ID_KELURAHAN = $kelurahan->ID_KELURAHAN;
        $tahunPembuatanDokKk = $infoArsipKk->TAHUN_PEMBUATAN_DOK_KK;
        $fileFields = [
            'FILE_LAMA',
            'FILE_F101',
            'FILE_NIKAH_CERAI',
            'FILE_SK_PINDAH',
            'FILE_SK_PINDAH_LUAR',
            'FILE_SK_PENGGANTI',
            'FILE_PUTUSAN_PRESIDEN',
            'FILE_KK_LAMA',
            'FILE_SK_PERISTIWA',
            'FILE_SK_HILANG',
            'FILE_KTP',
            'FILE_LAINNYA',
            'FILE_KK'
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
                        $folderPath = $tahunPembuatanDokKk . '/Arsip Kk';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipKk ->$field = $fileName;
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

        $infoArsipKk ->save();

        if ($infoArsipKk ) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Kartu Keluarga berhasil ditambahkan',
                'data' => $infoArsipKk ,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Kartu Keluarga gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }


    public function updateKk(Request $request, $ID_ARSIP)
    {
        // Validasi input
        $validator = app('validator')->make($request->all(),[
            'JUMLAH_BERKAS' => 'nullable|integer',
            'NO_BUKU' => 'nullable|integer',
            'NO_RAK' => 'nullable|integer',
            'NO_BARIS' => 'nullable|integer',
            'NO_BOKS' => 'nullable|integer',
            'LOK_SIMPAN' => 'nullable|string|max:25',
            'KETERANGAN'=>'nullable|string|max:15',
            'NO_DOK_KK' => 'required|integer',
            'NAMA_KEPALA' => 'nullable|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F101' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_NIKAH_CERAI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_PINDAH' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_PINDAH_LUAR' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_PENGGANTI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PUTUSAN_PRESIDEN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_PERISTIWA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_HILANG' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Kartu Keluarga')->value('ID_DOKUMEN');
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

        // Simpan data ke dalam tabel "arsip"
        $arsip->NO_DOK_KK = $request->input('NO_DOK_KK');
        $arsip->JUMLAH_BERKAS = $request->input('JUMLAH_BERKAS');
        $arsip->NO_BUKU = $request->input('NO_BUKU');
        $arsip->NO_RAK = $request->input('NO_RAK');
        $arsip->NO_BARIS = $request->input('NO_BARIS');
        $arsip->NO_BOKS = $request->input('NO_BOKS');
        $arsip->LOK_SIMPAN = $request->input('LOK_SIMPAN');
        $arsip->KETERANGAN = $request->input('KETERANGAN');
        $arsip->ID_DOKUMEN = $idDokumen;
        $arsip->TANGGAL_PINDAI = Carbon::now();

        if (!$arsip->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada perubahan pada Arsip',
                'data' => $arsipBeforeUpdate,
            ], 200);
        }
        $arsip->save();

        // Temukan info arsip Kk yang terkait
        $infoArsipKk = InfoArsipKk::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipKk) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip Kk tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip kk sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipKkBeforeUpdate = clone $infoArsipKk;

        $infoArsipKk ->NO_DOK_KK = $arsip->NO_DOK_KK;
        $infoArsipKk->NAMA_KEPALA = $request->input('NAMA_KEPALA');
        
        $tahunPembuatanDokKk = $infoArsipKk->TAHUN_PEMBUATAN_DOK_KK;
        $fileFields = [
            'FILE_LAMA',
            'FILE_F101',
            'FILE_NIKAH_CERAI',
            'FILE_SK_PINDAH',
            'FILE_SK_PINDAH_LUAR',
            'FILE_SK_PENGGANTI',
            'FILE_PUTUSAN_PRESIDEN',
            'FILE_KK_LAMA',
            'FILE_SK_PERISTIWA',
            'FILE_SK_HILANG',
            'FILE_KTP',
            'FILE_LAINNYA',
            'FILE_KK'
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
                        $folderPath = $tahunPembuatanDokKk . '/Arsip Kk';
                        $oldFileName = $infoArsipKk->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipKk ->$field = $fileName;
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
        if (!$infoArsipKk->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Datak Kartu Keluarga tidak ada perubahan',
                'data' => $infoArsipKkBeforeUpdate,
            ], 200);
        }
        $infoArsipKk ->save();

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
            'info_arsip_kk' => $infoArsipKk,
        ], 200);
    }
}
