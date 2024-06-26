<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Arsip;
use App\Models\JenisDokumen;
use App\Models\HakAkses;
use App\Models\Operator;
use App\Models\InfoArsipKelahiran;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InfoArsipKelahiranController extends Controller
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

    public function simpanKelahiran(Request $request)
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
            'NO_DOK_KELAHIRAN' => 'required|string|max:25|unique:info_arsip_kelahiran',
            'NAMA' => 'required|string|max:50',
            'TEMPAT_LAHIR' => 'required|string|max:25',
            'TANGGAL_LAHIR' => 'required|date',
            'ANAK_KE' => 'required|integer',
            'NAMA_AYAH' => 'required|string|max:50',
            'NAMA_IBU' => 'required|string|max:50',
            'NO_KK' => 'required|integer',
            'TAHUN_PEMBUATAN_DOK_KELAHIRAN' => 'required|integer',
            'STATUS_KELAHIRAN' => 'required|string|max:25',
            'STATUS_PENDUDUK' => 'required|string|max:25',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP_AYAH' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP_IBU' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F102' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F201' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_BUKU_NIKAH' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KUTIPAN_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SURAT_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SPTJM_PENERBITAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PELAPORAN_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Kelahiran')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_KELAHIRAN = $request->input('NO_DOK_KELAHIRAN');
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
        // Simpan data ke dalam tabel "info_arsip_kelahiran"
        $infoArsipKelahiran = new InfoArsipKelahiran();
        $infoArsipKelahiran->ID_ARSIP = $idArsip;
        $infoArsipKelahiran->NO_DOK_KELAHIRAN = $arsip->NO_DOK_KELAHIRAN; // Mengambil NO_DOK_KELAHIRAN dari tabel arsip
        // Isi kolom-kolom lainnya sesuai dengan nilai dari request
        $infoArsipKelahiran->NAMA = $request->input('NAMA');
        $infoArsipKelahiran->TEMPAT_LAHIR = $request->input('TEMPAT_LAHIR');
        $infoArsipKelahiran->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
        $infoArsipKelahiran->ANAK_KE = $request->input('ANAK_KE');
        $infoArsipKelahiran->NAMA_AYAH = $request->input('NAMA_AYAH');
        $infoArsipKelahiran->NAMA_IBU = $request->input('NAMA_IBU');
        $infoArsipKelahiran->NO_KK = $request->input('NO_KK');
        $infoArsipKelahiran->TAHUN_PEMBUATAN_DOK_KELAHIRAN = $request->input('TAHUN_PEMBUATAN_DOK_KELAHIRAN');
        $infoArsipKelahiran->STATUS_KELAHIRAN = $request->input('STATUS_KELAHIRAN');
        $infoArsipKelahiran->STATUS_PENDUDUK = $request->input('STATUS_PENDUDUK');
        // Simpan file-file jika diberikan

        $tahunPembuatanDokKelahiran = $infoArsipKelahiran->TAHUN_PEMBUATAN_DOK_KELAHIRAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_KK',
            'FILE_KTP_AYAH',
            'FILE_KTP_IBU',
            'FILE_F102',
            'FILE_F201',
            'FILE_BUKU_NIKAH',
            'FILE_KUTIPAN_KELAHIRAN',
            'FILE_SURAT_KELAHIRAN',
            'FILE_SPTJM_PENERBITAN',
            'FILE_PELAPORAN_KELAHIRAN',
            'FILE_LAINNYA',
            'FILE_AKTA_KELAHIRAN',
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
                        $folderPath = $tahunPembuatanDokKelahiran . '/Arsip Kelahiran';
                        $file->storeAs($folderPath, $fileName, 'public');
                        // Simpan nama file ke dalam database sesuai dengan field yang sesuai
                        $infoArsipKelahiran->$field = $fileName;
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
        $infoArsipKelahiran->save();

        if ($infoArsipKelahiran) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Kelahiran berhasil ditambahkan',
                'data' => $infoArsipKelahiran,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Kelahiran gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }


    public function updateKelahiran(Request $request, $ID_ARSIP)
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
            'NO_DOK_KELAHIRAN' => 'required|string|max:25',
            'NAMA' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP_AYAH' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP_IBU' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F102' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_F201' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_BUKU_NIKAH' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KUTIPAN_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SURAT_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SPTJM_PENERBITAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PELAPORAN_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_KELAHIRAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Kelahiran')->value('ID_DOKUMEN');
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
        $arsip->NO_DOK_KELAHIRAN = $request->input('NO_DOK_KELAHIRAN');
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
            // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada perubahan pada Arsip ',
                'data' => $arsipBeforeUpdate,
            ], 200);
        }

        // Simpan perubahan pada data arsip
        $arsip->save();

        // Temukan info arsip kelahiran yang terkait
        $infoArsipKelahiran = InfoArsipKelahiran::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipKelahiran) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip kelahiran tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip kelahiran sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipKelahiranBeforeUpdate = clone $infoArsipKelahiran;

        // Update data info arsip kelahiran
        $infoArsipKelahiran->NAMA = $request->input('NAMA');
        $infoArsipKelahiran->NO_DOK_KELAHIRAN = $arsip->NO_DOK_KELAHIRAN;

        $tahunPembuatanDokKelahiran = $infoArsipKelahiran->TAHUN_PEMBUATAN_DOK_KELAHIRAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_KK',
            'FILE_KTP_AYAH',
            'FILE_KTP_IBU',
            'FILE_F102',
            'FILE_F201',
            'FILE_BUKU_NIKAH',
            'FILE_KUTIPAN_KELAHIRAN',
            'FILE_SURAT_KELAHIRAN',
            'FILE_SPTJM_PENERBITAN',
            'FILE_PELAPORAN_KELAHIRAN',
            'FILE_LAINNYA',
            'FILE_AKTA_KELAHIRAN',
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
                        $folderPath = $tahunPembuatanDokKelahiran . '/Arsip Kelahiran';
                        $oldFileName = $infoArsipKelahiran->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        // Simpan nama file ke dalam database sesuai dengan field yang sesuai
                        $infoArsipKelahiran->$field = $fileName;
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
        // Periksa apakah ada perubahan pada data info arsip kelahiran
        if (!$infoArsipKelahiran->isDirty()) {
            // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
            return response()->json([
                'success' => true,
                'message' => 'Data Kelahiran tidak ada perubahan',
                'data' => $infoArsipKelahiranBeforeUpdate,
            ], 200);
        }

        // Simpan perubahan pada data info arsip kelahiran
        $infoArsipKelahiran->save();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $infoArsipKelahiran,
        ], 200);
    }


}
