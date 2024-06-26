<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\InfoArsipPengesahan;
use App\Models\JenisDokumen;
use App\Models\Operator;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InfoArsipPengesahanController extends Controller
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

    public function simpanPengesahan(Request $request)
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
            'NO_DOK_PENGESAHAN' => 'required|string|max:25|unique:info_arsip_pengesahan',
            'NAMA_ANAK' => 'required|string|max:50',
            'TANGGAL_LAHIR' => 'required|date',
            'TEMPAT_LAHIR' => 'required|string|max:25',
            'JENIS_KELAMIN' => 'required|string|max:15',
            'NO_AKTA_KELAHIRAN' => 'required|string|max:25',
            'NO_PP' => 'required|string|max:25',
            'TANGGAL_PP' => 'required|date',
            'NAMA_AYAH' => 'required|string|max:50',
            'NAMA_IBU' => 'required|string|max:50',
            'TAHUN_PEMBUATAN_DOK_PENGESAHAN' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PENGESAHAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Pengesahan Anak')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_PENGESAHAN = $request->input('NO_DOK_PENGESAHAN');
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
        // Simpan data ke dalam tabel "info_arsip_pengakuan"
        $infoArsipPengesahan = new InfoArsipPengesahan();
        $infoArsipPengesahan->ID_ARSIP = $idArsip;
        $infoArsipPengesahan->NO_DOK_PENGESAHAN = $arsip->NO_DOK_PENGESAHAN;
        $infoArsipPengesahan->NAMA_ANAK = $request->input('NAMA_ANAK');
        $infoArsipPengesahan->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
        $infoArsipPengesahan->TEMPAT_LAHIR = $request->input('TEMPAT_LAHIR');
        $infoArsipPengesahan->JENIS_KELAMIN = $request->input('JENIS_KELAMIN');
        $infoArsipPengesahan->NO_AKTA_KELAHIRAN = $request->input('NO_AKTA_KELAHIRAN');
        $infoArsipPengesahan->NO_PP = $request->input('NO_PP');
        $infoArsipPengesahan->TANGGAL_PP = $request->input('TANGGAL_PP');
        $infoArsipPengesahan->NAMA_AYAH = $request->input('NAMA_AYAH');
        $infoArsipPengesahan->NAMA_IBU = $request->input('NAMA_IBU');
        $infoArsipPengesahan->TAHUN_PEMBUATAN_DOK_PENGESAHAN = $request->input('TAHUN_PEMBUATAN_DOK_PENGESAHAN');

        $tahunPembuatanDokPengesahan = $infoArsipPengesahan->TAHUTAHUN_PEMBUATAN_DOK_PENGESAHAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_PENGESAHAN',
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
                        $folderPath = $tahunPembuatanDokPengesahan . '/Arsip Pengesahan';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPengesahan->$field = $fileName;
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
        $infoArsipPengesahan->save();

        if ($infoArsipPengesahan) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Pengesahan berhasil ditambahkan',
                'data' => $infoArsipPengesahan,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Pengesahan gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    public function updatePengesahan(Request $request, $ID_ARSIP)
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
            'NO_DOK_PENGESAHAN' => 'required|string|max:25',
            'NAMA_ANAK' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PENGESAHAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Pengesahan Anak')->value('ID_DOKUMEN');
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
        $arsip->NO_DOK_PENGESAHAN = $request->input('NO_DOK_PENGESAHAN');
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

        // Simpan perubahan pada data arsip
        $arsip->save();

        // Temukan info arsip pengesahan yang terkait
        $infoArsipPengesahan = InfoArsipPengesahan::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipPengesahan) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip pengesahan tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip pengesahan sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipPengesahanBeforeUpdate = clone $infoArsipPengesahan;

        // Update data info arsip pengesahan
        $infoArsipPengesahan->NO_DOK_PENGESAHAN = $arsip->NO_DOK_PENGESAHAN;
        $infoArsipPengesahan->NAMA_ANAK = $request->input('NAMA_ANAK');

        $tahunPembuatanDokPengesahan = $infoArsipPengesahan->TAHUTAHUN_PEMBUATAN_DOK_PENGESAHAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_PENGESAHAN',
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
                        $folderPath = $tahunPembuatanDokPengesahan . '/Arsip Pengesahan';
                        $oldFileName = $infoArsipPengesahan->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPengesahan->$field = $fileName;
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
        if (!$infoArsipPengesahan->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Pengesahan tidak ada perubahan',
                'data' => $infoArsipPengesahanBeforeUpdate,
            ], 200);
        }
        $infoArsipPengesahan->save();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $infoArsipPengesahan,
        ], 200);
    }

}
