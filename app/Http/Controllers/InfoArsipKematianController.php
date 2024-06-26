<?php

namespace App\Http\Controllers;

use App\Models\InfoArsipKematian;
use App\Models\User;
use App\Models\Arsip;
use App\Models\JenisDokumen;
use App\Models\HakAkses;
use App\Models\Operator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InfoArsipKematianController extends Controller
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


    public function simpanKematian(Request $request)
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
            'NO_DOK_KEMATIAN' => 'required|string|max:25|unique:info_arsip_kematian',
            'NAMA' => 'required|string|max:50',
            'NIK' => 'required|integer',
            'TEMPAT_LAHIR' => 'required|string|max:25',
            'TANGGAL_LAHIR' => 'required|date',
            'TANGGAL_MATI' => 'required|date',
            'TEMPAT_MATI' => 'required|string|max:25',
            'ALAMAT' => 'required|string|max:50',
            'JENIS_KELAMIN' => 'required|string|max:15',
            'AGAMA' => 'required|string|max:15',
            'TANGGAL_LAPOR' => 'required|date',
            'TAHUN_PEMBUATAN_DOK_KEMATIAN' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F201' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_KEMATIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP_SUAMI_ISTRI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KUTIPAN_KEMATIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_PP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_DOK_PERJALANAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_DOK_PENDUKUNG' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SPTJM' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_KEMATIAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Kematian')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_KEMATIAN = $request->input('NO_DOK_KEMATIAN');
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

        $infoArsipKematian = new InfoArsipKematian();
        $infoArsipKematian->ID_ARSIP = $idArsip;
        $infoArsipKematian->NO_DOK_KEMATIAN = $arsip->NO_DOK_KEMATIAN;
        $infoArsipKematian->NAMA = $request->input('NAMA');
        $infoArsipKematian->NIK = $request->input('NIK');
        $infoArsipKematian->TEMPAT_LAHIR = $request->input('TEMPAT_LAHIR');
        $infoArsipKematian->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
        $infoArsipKematian->TANGGAL_MATI = $request->input('TANGGAL_MATI');
        $infoArsipKematian->TEMPAT_MATI = $request->input('TEMPAT_MATI');
        $infoArsipKematian->ALAMAT = $request->input('ALAMAT');
        $infoArsipKematian->JENIS_KELAMIN = $request->input('JENIS_KELAMIN');
        $infoArsipKematian->AGAMA = $request->input('AGAMA');
        $infoArsipKematian->TANGGAL_LAPOR = $request->input('TANGGAL_LAPOR');
        $infoArsipKematian->TAHUN_PEMBUATAN_DOK_KEMATIAN = $request->input('TAHUN_PEMBUATAN_DOK_KEMATIAN');

        $tahunPembuatanDokKematian = $infoArsipKematian->TAHUN_PEMBUATAN_DOK_KEMATIAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_F201',
            'FILE_SK_KEMATIAN',
            'FILE_KK',
            'FILE_KTP',
            'FILE_KTP_SUAMI_ISTRI',
            'FILE_KUTIPAN_KEMATIAN',
            'FILE_FC_PP',
            'FILE_FC_DOK_PERJALANAN',
            'FILE_DOK_PENDUKUNG',
            'FILE_SPTJM',
            'FILE_LAINNYA',
            'FILE_AKTA_KEMATIAN',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $allowedExtensions = ['pdf'];
                $file = $request->file($field);
                $extension = $file->getClientOriginalExtension();

                if (in_array($extension, $allowedExtensions)) {
                    if ($file->getSize() <= 25000000) {
                        $fileName = $file->getClientOriginalName();
                        $folderPath = $tahunPembuatanDokKematian . '/Arsip Kematian';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipKematian->$field = $fileName;
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
        $infoArsipKematian->save();

        if ($infoArsipKematian) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Kematian berhasil ditambahkan',
                'data' => $infoArsipKematian,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Kematian gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    public function updateKematian(Request $request, $ID_ARSIP)
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
            'NO_DOK_KEMATIAN' => 'required|string|max:25',
            'NAMA' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F201' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_KEMATIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP_SUAMI_ISTRI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KUTIPAN_KEMATIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_PP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_DOK_PERJALANAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_DOK_PENDUKUNG' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SPTJM' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_KEMATIAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Kematian')->value('ID_DOKUMEN');
        $arsip = Arsip::find($ID_ARSIP);

        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Arsip tidak ditemukan',
            ], 404);
        }

        // Simpan data arsip sebelum diupdate untuk memeriksa apakah ada perubahan
        $arsipBeforeUpdate = clone $arsip;

        // Update data arsip
        $arsip->NO_DOK_KEMATIAN = $request->input('NO_DOK_KEMATIAN');
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

        $infoArsipKematian = InfoArsipKematian::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipKematian) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip kematian tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip kematian sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipKematianBeforeUpdate = clone $infoArsipKematian;

        // Update data info arsip kematian
        $infoArsipKematian->NO_DOK_KEMATIAN = $arsip->NO_DOK_KEMATIAN;
        $infoArsipKematian->NAMA = $request->input('NAMA');

        $tahunPembuatanDokKematian = $infoArsipKematian->TAHUN_PEMBUATAN_DOK_KELAHIRAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_F201',
            'FILE_SK_KEMATIAN',
            'FILE_KK',
            'FILE_KTP',
            'FILE_KTP_SUAMI_ISTRI',
            'FILE_KUTIPAN_KEMATIAN',
            'FILE_FC_PP',
            'FILE_FC_DOK_PERJALANAN',
            'FILE_DOK_PENDUKUNG',
            'FILE_SPTJM',
            'FILE_LAINNYA',
            'FILE_AKTA_KEMATIAN',
        ];

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
                        $folderPath = $tahunPembuatanDokKematian . '/Arsip Kematian';
                        $oldFileName = $infoArsipKematian->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipKematian->$field = $fileName;
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

        // Periksa apakah ada perubahan pada data info arsip kematian
        if (!$infoArsipKematian->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Kematian tidak ada perubahan',
                'data' => $infoArsipKematianBeforeUpdate,
            ], 200);
        }
        $infoArsipKematian->save();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $infoArsipKematian,
        ], 200);
    }

}
