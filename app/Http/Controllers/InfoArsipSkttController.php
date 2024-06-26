<?php

namespace App\Http\Controllers;

use App\Models\InfoArsipSktt;
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

class InfoArsipSkttController extends Controller
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

    public function simpanSktt(Request $request)
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
            'NO_DOK_SKTT' => 'required|string|max:25|unique:info_arsip_sktt',
            'NAMA' => 'required|string|max:50',
            'JENIS_KELAMIN' => 'required|string|max:15',
            'TEMPAT_LAHIR' => 'required|string|max:25',
            'TANGGAL_LAHIR' => 'required|date',
            'AGAMA' => 'required|string|max:15',
            'STATUS_KAWIN' => 'required|string|max:15',
            'KEBANGSAAN' => 'required|string|max:15',
            'NO_PASPOR' => 'nullable|string|max:50',
            'HUB_KELUARGA' => 'required|string|max:25',
            'PEKERJAAN' => 'required|string|max:25',
            'GOLDAR' => 'required|string|max:10',
            'ALAMAT' => 'required|string|max:50',
            'PROV' => 'required|string|max:50',
            'KOTA' => 'required|string|max:50',
            'ID_KELURAHAN' =>'required|integer',
            'ID_KECAMATAN' => 'required|integer',
            'TAHUN_PEMBUATAN_DOK_SKTT' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SKTT' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'SKTT')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_SKTT = $request->input('NO_DOK_SKTT');
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

        $infoArsipSktt = new InfoArsipSktt();
        $infoArsipSktt->ID_ARSIP = $arsip->ID_ARSIP;
        $infoArsipSktt->NO_DOK_SKTT = $arsip->NO_DOK_SKTT;
        $infoArsipSktt->NAMA = $request->input('NAMA');
        $infoArsipSktt->JENIS_KELAMIN = $request->input('JENIS_KELAMIN');
        $infoArsipSktt->TEMPAT_LAHIR = $request->input('TEMPAT_LAHIR');
        $infoArsipSktt->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
        $infoArsipSktt->AGAMA = $request->input('AGAMA');
        $infoArsipSktt->STATUS_KAWIN = $request->input('STATUS_KAWIN');
        $infoArsipSktt->KEBANGSAAN = $request->input('KEBANGSAAN');
        $infoArsipSktt->NO_PASPOR = $request->input('NO_PASPOR');
        $infoArsipSktt->HUB_KELUARGA = $request->input('HUB_KELUARGA');
        $infoArsipSktt->PEKERJAAN = $request->input('PEKERJAAN');
        $infoArsipSktt->GOLDAR = $request->input('GOLDAR');
        $infoArsipSktt->ALAMAT = $request->input('ALAMAT');
        $infoArsipSktt->PROV = $request->input('PROV');
        $infoArsipSktt->KOTA = $request->input('KOTA');
        $infoArsipSktt->ID_KELURAHAN = $request->input('ID_KELURAHAN');
        $infoArsipSktt->ID_KECAMATAN = $request->input('ID_KECAMATAN');
        $infoArsipSktt->TAHUN_PEMBUATAN_DOK_SKTT = $request->input('TAHUN_PEMBUATAN_DOK_SKTT');

        $kecamatan = Kecamatan::find($request->input('ID_KECAMATAN'));
        // Jika kecamatan tidak ditemukan
        if (!$kecamatan) {
            return response()->json(['error' => 'Kecamatan tidak valid'], 400);
        }
        $infoArsipSktt->ID_KECAMATAN = $kecamatan->ID_KECAMATAN;

        $id_kelurahan = $request->input('ID_KELURAHAN');
        $kelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)
                    ->where('ID_KECAMATAN', $kecamatan->ID_KECAMATAN)
                    ->first();
        // Jika kelurahan tidak ditemukan
        if (!$kelurahan) {
            return response()->json(['error' => 'Kelurahan tidak ditemukan sesuai kecamatan yang dipilih'], 400);
        }
        $infoArsipSktt->ID_KELURAHAN = $kelurahan->ID_KELURAHAN;

        $tahunPembuatanDokSktt = $infoArsipSktt->TAHUN_PEMBUATAN_DOK_SKTT;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_SKTT'
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
                        $folderPath = $tahunPembuatanDokSktt . '/Arsip Sktt';
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipSktt->$field = $fileName;
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
        $infoArsipSktt->save();

        if ($infoArsipSktt) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Sktt berhasil ditambahkan',
                'data' => $infoArsipSktt,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Sktt gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    public function updateSktt(Request $request, $ID_ARSIP)
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
            'NO_DOK_SKTT' => 'required|string|max:25',
            'NAMA' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SKTT' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'SKTT')->value('ID_DOKUMEN');
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
        $arsip->NO_DOK_SKTT = $request->input('NO_DOK_SKTT');
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

        $infoArsipSktt = InfoArsipSktt::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipSktt) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip SKTT tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip SKTT sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipSkttBeforeUpdate = clone $infoArsipSktt;

        // Simpan data ke dalam tabel "info_arsip_sktt"
        $infoArsipSktt->NO_DOK_SKTT = $arsip->NO_DOK_SKTT;
        $infoArsipSktt->NAMA = $request->input('NAMA');

        $tahunPembuatanDokSktt = $infoArsipSktt->TAHUN_PEMBUATAN_DOK_SKTT;
        $fileFields = [
            'FILE_LAMA',
            'FILE_LAINNYA',
            'FILE_SKTT'
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
                        $folderPath = $tahunPembuatanDokSktt . '/Arsip Sktt';
                        $oldFileName = $infoArsipSktt->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipSktt->$field = $fileName;
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
        if (!$infoArsipSktt->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Sktt tidak ada perubahan',
                'data' => $infoArsipSkttBeforeUpdate,
            ], 200);
        }
        $infoArsipSktt ->save();

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
            'info_arsip_Sktt' => $infoArsipSktt,
        ], 200);
    }

}
