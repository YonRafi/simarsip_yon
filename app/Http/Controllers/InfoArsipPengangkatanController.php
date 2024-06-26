<?php

namespace App\Http\Controllers;

use App\Models\InfoArsipPengangkatan;
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

class InfoArsipPengangkatanController extends Controller
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

    public function simpanPengangkatan(Request $request)
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
            'NO_DOK_PENGANGKATAN' => 'required|string|max:25|unique:info_arsip_pengangkatan',
            'NAMA_ANAK' => 'required|string|max:50',
            'NIK' => 'required|string|max:16',
            'TANGGAL_LAHIR' => 'required|date',
            'JENIS_KELAMIN' => 'required|string|max:15',
            'NO_PP' => 'required|string|max:25',
            'TANGGAL_PP' => 'required|date',
            'NAMA_AYAH' => 'required|string|max:50',
            'NAMA_IBU' => 'required|string|max:50',
            'THN_PEMBUATAN_DOK_PENGANGKATAN' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PENGANGKATAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Pengangkatan Anak')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_PENGANGKATAN = $request->input('NO_DOK_PENGANGKATAN');
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
        $infoArsipPengangkatan = new InfoArsipPengangkatan();
        $infoArsipPengangkatan->ID_ARSIP = $idArsip;
        $infoArsipPengangkatan->NO_DOK_PENGANGKATAN = $arsip->NO_DOK_PENGANGKATAN;
        $infoArsipPengangkatan->NAMA_ANAK = $request->input('NAMA_ANAK');
        $infoArsipPengangkatan->NIK = $request->input('NIK');
        $infoArsipPengangkatan->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
        $infoArsipPengangkatan->JENIS_KELAMIN = $request->input('JENIS_KELAMIN');
        $infoArsipPengangkatan->NO_PP = $request->input('NO_PP');
        $infoArsipPengangkatan->TANGGAL_PP = $request->input('TANGGAL_PP');
        $infoArsipPengangkatan->NAMA_AYAH = $request->input('NAMA_AYAH');
        $infoArsipPengangkatan->NAMA_IBU = $request->input('NAMA_IBU');
        $infoArsipPengangkatan->THN_PEMBUATAN_DOK_PENGANGKATAN = $request->input('THN_PEMBUATAN_DOK_PENGANGKATAN');

        $tahunPembuatanDokPengangkatan = $request->THN_PEMBUATAN_DOK_PENGANGKATAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_PENGANGKATAN',
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
                        $folderPath = $tahunPembuatanDokPengangkatan . '/Arsip Pengangkatan';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPengangkatan->$field = $fileName;
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
        $infoArsipPengangkatan->save();

        if ($infoArsipPengangkatan) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Pengangkatan berhasil ditambahkan',
                'data' => $infoArsipPengangkatan,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Pengangkatan gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    public function updatePengangkatan(Request $request, $ID_ARSIP)
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
            'NO_DOK_PENGANGKATAN' => 'required|string|max:25',
            'NAMA_ANAK' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PENGANGKATAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Pengangkatan Anak')->value('ID_DOKUMEN');
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
        $arsip->NO_DOK_PENGANGKATAN = $request->input('NO_DOK_PENGANGKATAN');
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
                'message' => 'Tidak ada perubahan pada Arsip',
                'data' => $arsipBeforeUpdate,
            ], 200);
        }

        // Simpan perubahan pada data arsip
        $arsip->save();

        // Temukan info arsip pengangkatan yang terkait
        $infoArsipPengangkatan = InfoArsipPengangkatan::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipPengangkatan) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip pengangkatan tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip pengangkatan sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipPengangkatanBeforeUpdate = clone $infoArsipPengangkatan;

        // Update data info arsip pengangkatan
        $infoArsipPengangkatan->NO_DOK_PENGANGKATAN = $arsip->NO_DOK_PENGANGKATAN;
        $infoArsipPengangkatan->NAMA_ANAK = $request->input('NAMA_ANAK');

        $tahunPembuatanDokPengangkatan = $infoArsipPengangkatan->THN_PEMBUATAN_DOK_PENGANGKATAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_PENGANGKATAN',
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
                        $folderPath = $tahunPembuatanDokPengangkatan . '/Arsip Pengangkatan';
                        $oldFileName = $infoArsipPengangkatan->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPengangkatan->$field = $fileName;
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

        // Periksa apakah ada perubahan pada data info arsip pengangkatan
        if (!$infoArsipPengangkatan->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Pengangkatan tidak ada perubahan',
                'data' => $infoArsipPengangkatanBeforeUpdate,
            ], 200);
        }
        $infoArsipPengangkatan->save();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $infoArsipPengangkatan,
        ], 200);
    }

}
