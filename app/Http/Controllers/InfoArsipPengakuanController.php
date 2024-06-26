<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\InfoArsipPengakuan;
use App\Models\JenisDokumen;
use App\Models\Operator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InfoArsipPengakuanController extends Controller
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

    public function simpanPengakuan(Request $request)
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
            'NO_DOK_PENGAKUAN' => 'required|string|max:25|unique:info_arsip_pengakuan',
            'NAMA_ANAK' => 'required|string|max:50',
            'TANGGAL_LAHIR' => 'required|date',
            'TEMPAT_LAHIR' => 'required|string|max:25',
            'JENIS_KELAMIN' => 'required|string|max:15',
            'NO_PP' => 'required|string|max:25',
            'TANGGAL_PP' => 'required|date',
            'NO_AKTA_KELAHIRAN' => 'required|string|max:25',
            'NAMA_AYAH' => 'required|string|max:50',
            'NAMA_IBU' => 'required|string|max:50',
            'TAHUN_PEMBUATAN_DOK_PENGAKUAN' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PENGAKUAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Pengakuan Anak')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_PENGAKUAN = $request->input('NO_DOK_PENGAKUAN');
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
        $infoArsipPengakuan = new InfoArsipPengakuan();
        $infoArsipPengakuan->ID_ARSIP = $idArsip;
        $infoArsipPengakuan->NO_DOK_PENGAKUAN = $arsip->NO_DOK_PENGAKUAN;
        $infoArsipPengakuan->NAMA_ANAK = $request->input('NAMA_ANAK');
        $infoArsipPengakuan->TEMPAT_LAHIR = $request->input('TEMPAT_LAHIR');
        $infoArsipPengakuan->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
        $infoArsipPengakuan->JENIS_KELAMIN = $request->input('JENIS_KELAMIN');
        $infoArsipPengakuan->NO_PP = $request->input('NO_PP');
        $infoArsipPengakuan->TANGGAL_PP = $request->input('TANGGAL_PP');
        $infoArsipPengakuan->NO_AKTA_KELAHIRAN = $request->input('NO_AKTA_KELAHIRAN');
        $infoArsipPengakuan->NAMA_AYAH = $request->input('NAMA_AYAH');
        $infoArsipPengakuan->NAMA_IBU = $request->input('NAMA_IBU');
        $infoArsipPengakuan->TAHUN_PEMBUATAN_DOK_PENGAKUAN = $request->input('TAHUN_PEMBUATAN_DOK_PENGAKUAN');

        $tahunPembuatanDokPengakuan = $infoArsipPengakuan->TAHUN_PEMBUATAN_DOK_PENGAKUAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_PENGAKUAN',
        ];

        // Loop melalui setiap field file untuk menyimpannya
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $allowedExtensions = ['pdf'];
                $file = $request->file($field);
                $extension = $file->getClientOriginalExtension();
                if (in_array($extension, $allowedExtensions)) {
                    // Periksa ukuran file
                    if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                        $fileName = $file->getClientOriginalName();
                        $folderPath = $tahunPembuatanDokPengakuan . '/Arsip Pengakuan';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPengakuan->$field = $fileName;
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
        $infoArsipPengakuan->save();

        if ($infoArsipPengakuan) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Pengakuan berhasil ditambahkan',
                'data' => $infoArsipPengakuan,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Pengakuan gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }


    public function updatePengakuan(Request $request, $ID_ARSIP)
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
            'NO_DOK_PENGAKUAN' => 'required|string|max:25',
            'NAMA_ANAK' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_PENGAKUAN' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Akta Pengakuan Anak')->value('ID_DOKUMEN');
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

        $arsip->NO_DOK_PENGAKUAN = $request->input('NO_DOK_PENGAKUAN');
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

        // Temukan info arsip pengakuan yang terkait
        $infoArsipPengakuan = InfoArsipPengakuan::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipPengakuan) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip pengakuan tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip pengakuan sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipPengakuanBeforeUpdate = clone $infoArsipPengakuan;
        $infoArsipPengakuan->NO_DOK_PENGAKUAN = $arsip->NO_DOK_PENGAKUAN;
        $infoArsipPengakuan->NAMA_ANAK = $request->input('NAMA_ANAK');

        $tahunPembuatanDokPengakuan = $infoArsipPengakuan->TAHUN_PEMBUATAN_DOK_PENGAKUAN;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_PENGAKUAN',
        ];

        // Loop melalui setiap field file untuk menyimpannya
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $allowedExtensions = ['pdf'];
                $file = $request->file($field);
                $extension = $file->getClientOriginalExtension();

                if (in_array($extension, $allowedExtensions)) {
                    // Periksa ukuran file
                    if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                        $fileName = $file->getClientOriginalName();
                        $folderPath = $tahunPembuatanDokPengakuan . '/Arsip Pengakuan';
                        $oldFileName = $infoArsipPengakuan->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipPengakuan->$field = $fileName;
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

        // Periksa apakah ada perubahan pada data info arsip pengakuan
        if (!$infoArsipPengakuan->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Pengakuan tidak ada perubahan',
                'data' => $infoArsipPengakuanBeforeUpdate,
            ], 200);
        }
        $infoArsipPengakuan->save();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $infoArsipPengakuan,
        ], 200);
    }
}
