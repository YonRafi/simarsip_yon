<?php

namespace App\Http\Controllers;

use App\Models\InfoArsipPerkawinan;
use App\Models\User;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\JenisDokumen;
use App\Models\Operator;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InfoArsipPerkawinanController extends Controller
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

    public function simpanPerkawinan(Request $request)
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
            'NO_DOK_PERKAWINAN' => 'required|string|max:25|unique:info_arsip_perkawinan',
            'NAMA_PRIA' => 'required|string|max:50',
            'NAMA_WANITA' => 'required|string|max:50',
            'TANGGAL_DOK_PERKAWINAN' => 'required|date',
            'TEMPAT_KAWIN' => 'required|string|max:25',
            'AGAMA_KAWIN' => 'required|string|max:15',
            'AYAH_PRIA' => 'required|string|max:50',
            'IBU_PRIA' => 'required|string|max:50',
            'AYAH_WANITA' => 'required|string|max:50',
            'IBU_WANITA' => 'required|string|max:50',
            'TAHUN_PEMBUATAN_DOK_PERKAWINAN' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F201' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_SK_KAWIN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_PASFOTO' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_KEMATIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_PERCERAIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SPTJM' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_PERKAWINAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Perkawinan"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Perkawinan')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_PERKAWINAN = $request->input('NO_DOK_PERKAWINAN');
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
        // Simpan data ke dalam tabel "info_arsip_Perkawinan"
        $infoArsipPerkawinan = new InfoArsipPerkawinan();
        $infoArsipPerkawinan->ID_ARSIP = $idArsip;
        $infoArsipPerkawinan->NO_DOK_PERKAWINAN = $arsip->NO_DOK_PERKAWINAN;
        $infoArsipPerkawinan->NAMA_PRIA = $request->input('NAMA_PRIA');
        $infoArsipPerkawinan->NAMA_WANITA = $request->input('NAMA_WANITA');
        $infoArsipPerkawinan->TANGGAL_DOK_PERKAWINAN = $request->input('TANGGAL_DOK_PERKAWINAN');
        $infoArsipPerkawinan->TEMPAT_KAWIN = $request->input('TEMPAT_KAWIN');
        $infoArsipPerkawinan->AGAMA_KAWIN = $request->input('AGAMA_KAWIN');
        $infoArsipPerkawinan->AYAH_PRIA = $request->input('AYAH_PRIA');
        $infoArsipPerkawinan->IBU_PRIA = $request->input('IBU_PRIA');
        $infoArsipPerkawinan->AYAH_WANITA = $request->input('AYAH_WANITA');
        $infoArsipPerkawinan->IBU_WANITA = $request->input('IBU_WANITA');
        $infoArsipPerkawinan->TAHUN_PEMBUATAN_DOK_PERKAWINAN = $request->input('TAHUN_PEMBUATAN_DOK_PERKAWINAN');

        $tahunPembuatanDokPerkawinan = $infoArsipPerkawinan->TAHUN_PEMBUATAN_DOK_PERKAWINAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_F201',
            'FILE_FC_SK_KAWIN',
            'FILE_FC_PASFOTO',
            'FILE_KTP',
            'FILE_KK',
            'FILE_AKTA_KEMATIAN',
            'FILE_AKTA_PERCERAIAN',
            'FILE_SPTJM',
            'FILE_LAINNYA',
            'FILE_AKTA_PERKAWINAN'
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
                        $folderPath = $tahunPembuatanDokPerkawinan . '/Arsip Perkawinan';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPerkawinan->$field = $fileName;
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
        $infoArsipPerkawinan->save();

        if ($infoArsipPerkawinan) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Perkawinan berhasil ditambahkan',
                'data' => $infoArsipPerkawinan,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Perkawinan gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    public function updatePerkawinan(Request $request, $ID_ARSIP)
    {
        // Validasi input
        $validator = app('validator')->make($request->all(), [
            'JUMLAH_BERKAS' => 'nullable|integer',
            'NO_BUKU' => 'nullable|integer',
            'NO_RAK' => 'nullable|integer',
            'NO_BARIS' => 'nullable|integer',
            'NO_BOKS' => 'nullable|integer',
            'LOK_SIMPAN' => 'nullable|string|max:25',
            'KETERANGAN' => 'nullable|string|max:15',
            'NO_DOK_PERKAWINAN' => 'required|string|max:25',
            'NAMA_PRIA' => 'required|string|max:50',
            'NAMA_WANITA' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F201' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_SK_KAWIN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_PASFOTO' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_KEMATIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_PERCERAIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SPTJM' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_PERKAWINAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Perkawinan')->value('ID_DOKUMEN');
        // Temukan arsip perkawinan berdasarkan ID_ARSIP
        $arsip = Arsip::find($ID_ARSIP);

        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Arsip perkawinan tidak ditemukan',
            ], 404);
        }

        // Simpan data arsip sebelum diupdate untuk memeriksa apakah ada perubahan
        $arsipBeforeUpdate = clone $arsip;

        // Update data arsip
        $arsip->NO_DOK_PERKAWINAN = $request->input('NO_DOK_PERKAWINAN');
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
                'message' => 'Tidak ada perubahan pada Arsip Perkawinan',
                'data' => $arsipBeforeUpdate,
            ], 200);
        }

        // Simpan perubahan pada data arsip
        $arsip->save();

        // Temukan info arsip perkawinan yang terkait
        $infoArsipPerkawinan = InfoArsipPerkawinan::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipPerkawinan) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip perkawinan tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip perkawinan sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipPerkawinanBeforeUpdate = clone $infoArsipPerkawinan;

        // Update data info arsip perkawinan
        $infoArsipPerkawinan->NO_DOK_PERKAWINAN = $arsip->NO_DOK_PERKAWINAN;
        $infoArsipPerkawinan->NAMA_PRIA = $request->input('NAMA_PRIA');
        $infoArsipPerkawinan->NAMA_WANITA = $request->input('NAMA_WANITA');

        $tahunPembuatanDokPerkawinan = $infoArsipPerkawinan->TAHUN_PEMBUATAN_DOK_PERKAWINAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_F201',
            'FILE_FC_SK_KAWIN',
            'FILE_FC_PASFOTO',
            'FILE_KTP',
            'FILE_KK',
            'FILE_AKTA_KEMATIAN',
            'FILE_AKTA_PERCERAIAN',
            'FILE_SPTJM',
            'FILE_LAINNYA',
            'FILE_AKTA_PERKAWINAN'
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
                        $folderPath = $tahunPembuatanDokPerkawinan . '/Arsip Perkawinan';
                        $oldFileName = $infoArsipPerkawinan->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPerkawinan->$field = $fileName;
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

        if (!$infoArsipPerkawinan->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Perkawinan tidak ada perubahan',
                'data' => $infoArsipPerkawinanBeforeUpdate,
            ], 200);
        }
        $infoArsipPerkawinan->save();

        return response()->json([
            'success' => true,
            'message' => 'Data Perkawinan berhasil diperbarui',
            'data' => $infoArsipPerkawinan,
        ], 200);
    }

}
