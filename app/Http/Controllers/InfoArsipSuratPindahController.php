<?php

namespace App\Http\Controllers;

use App\Models\InfoArsipSuratPindah;
use App\Models\User;
use App\Models\Arsip;
use App\Models\HakAkses;
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

class InfoArsipSuratPindahController extends Controller
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


    public function simpanSuratPindah(Request $request)
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
            'NO_DOK_SURAT_PINDAH' => 'required|string|max:25|unique:info_arsip_surat_pindah',
            'NO_KK' => 'required|integer',
            'NAMA_KEPALA' => 'required|string|max:50',
            'NIK_KEPALA' => 'required|integer',
            'ALASAN_PINDAH' => 'required|string|max:50',
            'ALAMAT' => 'required|string|max:50',
            'RT' => 'required',
            'RW' => 'required',
            'PROV' => 'required|string|max:50',
            'KOTA' => 'required|string|max:50',
            'ID_KECAMATAN' => 'required|integer',
            'ID_KELURAHAN' =>'required|integer',
            'KODEPOS' =>'required|integer',
            'ALAMAT_TUJUAN' => 'required|string|max:50',
            'RT_TUJUAN' => 'required',
            'RW_TUJUAN' => 'required',
            'PROV_TUJUAN' => 'required|string|max:25',
            'KOTA_TUJUAN' => 'required|string|max:25',
            'KEC_TUJUAN' => 'required|string|max:25',
            'KEL_TUJUAN' => 'required|string|max:25',
            'KODEPOS_TUJUAN' =>'required|integer',
            'THN_PEMBUATAN_DOK_SURAT_PINDAH' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SKP_WNI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP_ASAL' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_NIKAH_CERAI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F101' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F102' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F103' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_DOK_PENDUKUNG' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SURAT_PINDAH' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Surat Pindah')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_SURAT_PINDAH = $request->input('NO_DOK_SURAT_PINDAH');
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

        // Simpan data ke dalam tabel "info_arsip_surat_pindah"
        $infoArsipSuratPindah = new InfoArsipSuratPindah();
        $infoArsipSuratPindah->ID_ARSIP = $arsip->ID_ARSIP;
        $infoArsipSuratPindah->NO_DOK_SURAT_PINDAH = $arsip->NO_DOK_SURAT_PINDAH;
        $infoArsipSuratPindah->NO_KK = $request->input('NO_KK');
        $infoArsipSuratPindah->NAMA_KEPALA = $request->input('NAMA_KEPALA');
        $infoArsipSuratPindah->NIK_KEPALA = $request->input('NIK_KEPALA');
        $infoArsipSuratPindah->ALASAN_PINDAH = $request->input('ALASAN_PINDAH');
        $infoArsipSuratPindah->ALAMAT = $request->input('ALAMAT');
        $infoArsipSuratPindah->RT = $request->input('RT');
        $infoArsipSuratPindah->RW = $request->input('RW');
        $infoArsipSuratPindah->PROV = $request->input('PROV');
        $infoArsipSuratPindah->KOTA = $request->input('KOTA');
        $infoArsipSuratPindah->KODEPOS = $request->input('KODEPOS');
        $infoArsipSuratPindah->ALAMAT_TUJUAN = $request->input('ALAMAT_TUJUAN');
        $infoArsipSuratPindah->RT_TUJUAN = $request->input('RT_TUJUAN');
        $infoArsipSuratPindah->RW_TUJUAN = $request->input('RW_TUJUAN');
        $infoArsipSuratPindah->PROV_TUJUAN = $request->input('PROV_TUJUAN');
        $infoArsipSuratPindah->KOTA_TUJUAN = $request->input('KOTA_TUJUAN');
        $infoArsipSuratPindah->KEC_TUJUAN = $request->input('KEC_TUJUAN');
        $infoArsipSuratPindah->KEL_TUJUAN = $request->input('KEL_TUJUAN');
        $infoArsipSuratPindah->KODEPOS_TUJUAN = $request->input('KODEPOS_TUJUAN');
        $infoArsipSuratPindah->THN_PEMBUATAN_DOK_SURAT_PINDAH = $request->input('THN_PEMBUATAN_DOK_SURAT_PINDAH');

        $kecamatan = Kecamatan::find($request->input('ID_KECAMATAN'));
        // Jika kecamatan tidak ditemukan
        if (!$kecamatan) {
            return response()->json(['error' => 'Kecamatan tidak valid'], 400);
        }
        $infoArsipSuratPindah->ID_KECAMATAN = $kecamatan->ID_KECAMATAN;

        $id_kelurahan = $request->input('ID_KELURAHAN');
        $kelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)
                    ->where('ID_KECAMATAN', $kecamatan->ID_KECAMATAN)
                    ->first();
        // Jika kelurahan tidak ditemukan
        if (!$kelurahan) {
            return response()->json(['error' => 'Kelurahan tidak ditemukan sesuai kecamatan yang dipilih'], 400);
        }
        $infoArsipSuratPindah->ID_KELURAHAN = $kelurahan->ID_KELURAHAN;

        $tahunPembuatanDokSuratPindah= $infoArsipSuratPindah->THN_PEMBUATAN_DOK_SURAT_PINDAH;
        $fileFields = [
            'FILE_LAMA',
            'FILE_SKP_WNI',
            'FILE_KTP_ASAL',
            'FILE_NIKAH_CERAI',
            'FILE_AKTA_KELAHIRAN',
            'FILE_KK',
            'FILE_F101',
            'FILE_F102',
            'FILE_F103',
            'FILE_DOK_PENDUKUNG',
            'FILE_LAINNYA',
            'FILE_SURAT_PINDAH'
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
                        $folderPath = $tahunPembuatanDokSuratPindah . '/Arsip Surat Pindah';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipSuratPindah->$field = $fileName;
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
        $infoArsipSuratPindah->save();

        if ($infoArsipSuratPindah) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Surat Pindah berhasil ditambahkan',
                'data' => $infoArsipSuratPindah,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Surat Pindah gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    public function updateSuratPindah(Request $request, $ID_ARSIP)
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
            'NO_DOK_SURAT_PINDAH' => 'required|string|max:25',
            'NAMA_KEPALA' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SKP_WNI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP_ASAL' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_NIKAH_CERAI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F101' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F102' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F103' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_DOK_PENDUKUNG' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SURAT_PINDAH' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Surat Pindah')->value('ID_DOKUMEN');
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
        $arsip->NO_DOK_SURAT_PINDAH = $request->input('NO_DOK_SURAT_PINDAH');
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

        $infoArsipSuratPindah = InfoArsipSuratPindah::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipSuratPindah) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip Surat Pindah tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip Surat Pindah sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipSuratPindahBeforeUpdate = clone $infoArsipSuratPindah;

        $infoArsipSuratPindah->NO_DOK_SURAT_PINDAH = $arsip->NO_DOK_SURAT_PINDAH;
        $infoArsipSuratPindah->NAMA_KEPALA = $request->input('NAMA_KEPALA');

        $tahunPembuatanDokSuratPindah= $infoArsipSuratPindah->THN_PEMBUATAN_DOK_SURAT_PINDAH;
        $fileFields = [
            'FILE_LAMA',
            'FILE_SKP_WNI',
            'FILE_KTP_ASAL',
            'FILE_NIKAH_CERAI',
            'FILE_AKTA_KELAHIRAN',
            'FILE_KK',
            'FILE_F101',
            'FILE_F102',
            'FILE_F103',
            'FILE_DOK_PENDUKUNG',
            'FILE_LAINNYA',
            'FILE_SURAT_PINDAH'
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
                        $folderPath = $tahunPembuatanDokSuratPindah . '/Arsip Surat Pindah';
                        $oldFileName = $infoArsipSuratPindah->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipSuratPindah->$field = $fileName;
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
        if (!$infoArsipSuratPindah->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Surat Pindah tidak ada perubahan',
                'data' => $infoArsipSuratPindahBeforeUpdate,
            ], 200);
        }
        $infoArsipSuratPindah ->save();

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
            'info_arsip_surat_pindah' => $infoArsipSuratPindah,
        ], 200);
    }

}
