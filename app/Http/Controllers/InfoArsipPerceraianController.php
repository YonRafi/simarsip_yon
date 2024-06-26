<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\InfoArsipPerceraian;
use App\Models\JenisDokumen;
use App\Models\Operator;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InfoArsipPerceraianController extends Controller
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


    public function simpanPerceraian(Request $request)
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
            'NO_DOK_PERCERAIAN' => 'required|string|max:25|unique:info_arsip_perceraian',
            'NAMA_PRIA' => 'required|string|max:50',
            'NAMA_WANITA' => 'required|string|max:50',
            'ALAMAT_PRIA' => 'required|string|max:255',
            'ALAMAT_WANITA' => 'required|string|max:255',
            'NO_PP' => 'required|string|max:25',
            'TANGGAL_PP' => 'required|date',
            'DOMISILI_CERAI' => 'required|string|max:255',
            'NO_PERKAWINAN' => 'required|string|max:25',
            'TANGGAL_DOK_PERKAWINAN' => 'required|date',
            'TAHUN_PEMBUATAN_DOK_PERCERAIAN' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F201' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_PP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KUTIPAN_PERKAWINAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SPTJM' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_PERCERAIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_PERKAWINAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Perceraian"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Perceraian')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_PERCERAIAN = $request->input('NO_DOK_PERCERAIAN');
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
        // Simpan data ke dalam tabel "info_arsip_Perceraian"
        $infoArsipPerceraian = new InfoArsipPerceraian();
        $infoArsipPerceraian->ID_ARSIP = $idArsip;
        $infoArsipPerceraian->NO_DOK_PERCERAIAN = $arsip->NO_DOK_PERCERAIAN;
        $infoArsipPerceraian->NAMA_PRIA = $request->input('NAMA_PRIA');
        $infoArsipPerceraian->NAMA_WANITA = $request->input('NAMA_WANITA');
        $infoArsipPerceraian->ALAMAT_PRIA = $request->input('ALAMAT_PRIA');
        $infoArsipPerceraian->ALAMAT_WANITA = $request->input('ALAMAT_WANITA');
        $infoArsipPerceraian->NO_PP = $request->input('NO_PP');
        $infoArsipPerceraian->TANGGAL_PP = $request->input('TANGGAL_PP');
        $infoArsipPerceraian->DOMISILI_CERAI = $request->input('DOMISILI_CERAI');
        $infoArsipPerceraian->NO_PERKAWINAN = $request->input('NO_PERKAWINAN');
        $infoArsipPerceraian->TANGGAL_DOK_PERKAWINAN = $request->input('TANGGAL_DOK_PERKAWINAN');
        $infoArsipPerceraian->TAHUN_PEMBUATAN_DOK_PERCERAIAN = $request->input('TAHUN_PEMBUATAN_DOK_PERCERAIAN');

        $tahunPembuatanDokPerceraian = $infoArsipPerceraian->TAHUN_PEMBUATAN_DOK_PERCERAIAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_F201',
            'FILE_FC_PP',
            'FILE_KUTIPAN_PERKAWINAN',
            'FILE_KTP',
            'FILE_KK',
            'FILE_SPTJM',
            'FILE_LAINNYA',
            'FILE_AKTA_PERCERAIAN',
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
                        $folderPath = $tahunPembuatanDokPerceraian . '/Arsip Perceraian';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPerceraian->$field = $fileName;
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
        $infoArsipPerceraian->save();

        if ($infoArsipPerceraian) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Perceraian  berhasil ditambahkan',
                'data' => $infoArsipPerceraian,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Perceraian gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    public function updatePerceraian(Request $request, $ID_ARSIP)
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
            'NO_DOK_PERCERAIAN' => 'required|string|max:25',
            'NAMA_PRIA' => 'required|string|max:50',
            'NAMA_WANITA' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F201' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_FC_PP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KUTIPAN_PERKAWINAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SPTJM' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_PERCERAIAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_PERKAWINAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Perceraian')->value('ID_DOKUMEN');
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

        // Update data arsip
        $arsip->NO_DOK_PERCERAIAN = $request->input('NO_DOK_PERCERAIAN');
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
                'message' => 'Tidak ada perubahan pada Arsip Perceraian',
                'data' => $arsipBeforeUpdate,
            ], 200);
        }

        // Simpan perubahan pada data arsip
        $arsip->save();

        // Temukan info arsip perceraian yang terkait
        $infoArsipPerceraian = InfoArsipPerceraian::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipPerceraian) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip perceraian tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip perceraian sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipPerceraianBeforeUpdate = clone $infoArsipPerceraian;

        // Update data info arsip perceraian
        $infoArsipPerceraian->NO_DOK_PERCERAIAN = $arsip->NO_DOK_PERCERAIAN;
        $infoArsipPerceraian->NAMA_PRIA = $request->input('NAMA_PRIA');
        $infoArsipPerceraian->NAMA_WANITA = $request->input('NAMA_WANITA');

        $tahunPembuatanDokPerceraian = $infoArsipPerceraian->TAHUN_PEMBUATAN_DOK_PERCERAIAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_F201',
            'FILE_FC_PP',
            'FILE_KUTIPAN_PERKAWINAN',
            'FILE_KTP',
            'FILE_KK',
            'FILE_SPTJM',
            'FILE_LAINNYA',
            'FILE_AKTA_PERCERAIAN',
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
                        $folderPath = $tahunPembuatanDokPerceraian . '/Arsip Perceraian';
                        $oldFileName = $infoArsipPerceraian->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPerceraian->$field = $fileName;
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

        // Periksa apakah ada perubahan pada data info arsip perceraian
        if (!$infoArsipPerceraian->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Perceraian tidak ada perubahan',
                'data' => $infoArsipPerceraianBeforeUpdate,
            ], 200);
        }
        $infoArsipPerceraian->save();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $infoArsipPerceraian,
        ], 200);
    }

}
