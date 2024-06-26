<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\JenisDokumen;
use App\Models\InfoArsipKtp;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Operator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class InfoArsipKtpController extends Controller
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


    public function simpanKtp(Request $request)
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
            'NO_DOK_KTP' => 'required|integer|unique:info_arsip_ktp',
            'NAMA' => 'required|string|max:50',
            'JENIS_KELAMIN' => 'required|string|max:15',
            'TEMPAT_LAHIR' => 'required|string|max:25',
            'TANGGAL_LAHIR' => 'required|date',
            'AGAMA' => 'required|string|max:15',
            'STATUS_KAWIN' => 'required|string|max:15',
            'KEBANGSAAN' => 'required|string|max:15',
            'NO_PASPOR' => 'nullable|string|max:25',
            'HUB_KELUARGA' => 'required|string|max:25',
            'PEKERJAAN' => 'required|string|max:25',
            'GOLDAR' => 'nullable|string|max:10',
            'ALAMAT' => 'required|string|max:50',
            'PROV' => 'required|string|max:50',
            'KOTA' => 'required|string|max:50',
            'ID_KECAMATAN'=> 'required|integer',
            'ID_KELURAHAN'=> 'required|integer',
            'TAHUN_PEMBUATAN_KTP' => 'required|integer',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KUTIPAN_KTP' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SK_HILANG' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_AKTA_LAHIR' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_IJAZAH' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SURAT_NIKAH_CERAI' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_SURAT_PINDAH' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Mendapatkan ID_DOKUMEN untuk dokumen "Akta Kelahiran"
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Kartu Tanda Penduduk')->value('ID_DOKUMEN');

        // Simpan data ke dalam tabel "arsip"
        $arsip = new Arsip();
        $arsip->NO_DOK_KTP = $request->input('NO_DOK_KTP');
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
        $idDokKtp = $arsip->NO_DOK_KTP;
        // Simpan data ke dalam tabel "info_arsip_kelahiran"
        $infoArsipKtp = new InfoArsipKtp();
        $infoArsipKtp->ID_ARSIP = $idArsip;
        $infoArsipKtp->NO_DOK_KTP = $idDokKtp;
        $infoArsipKtp->NAMA = $request->input('NAMA');
        $infoArsipKtp->JENIS_KELAMIN = $request->input('JENIS_KELAMIN');
        $infoArsipKtp->TEMPAT_LAHIR = $request->input('TEMPAT_LAHIR');
        $infoArsipKtp->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
        $infoArsipKtp->AGAMA = $request->input('AGAMA');
        $infoArsipKtp->STATUS_KAWIN = $request->input('STATUS_KAWIN');
        $infoArsipKtp->KEBANGSAAN = $request->input('KEBANGSAAN');
        $infoArsipKtp->NO_PASPOR = $request->input('NO_PASPOR');
        $infoArsipKtp->HUB_KELUARGA = $request->input('HUB_KELUARGA');
        $infoArsipKtp->PEKERJAAN = $request->input('PEKERJAAN');
        $infoArsipKtp->GOLDAR = $request->input('GOLDAR');
        $infoArsipKtp->ALAMAT = $request->input('ALAMAT');
        $infoArsipKtp->PROV = $request->input('PROV');
        $infoArsipKtp->KOTA = $request->input('KOTA');

        $kecamatan = Kecamatan::find($request->input('ID_KECAMATAN'));
        // Jika kecamatan tidak ditemukan
        if (!$kecamatan) {
            return response()->json(['error' => 'Kecamatan tidak valid'], 400);
        }
        $infoArsipKtp->ID_KECAMATAN = $kecamatan->ID_KECAMATAN;

        $id_kelurahan = $request->input('ID_KELURAHAN');
        $kelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)
                    ->where('ID_KECAMATAN', $kecamatan->ID_KECAMATAN)
                    ->first();
        // Jika kelurahan tidak ditemukan
        if (!$kelurahan) {
            return response()->json(['error' => 'Kelurahan tidak ditemukan sesuai kecamatan yang dipilih'], 400);
        }
        $infoArsipKtp->ID_KELURAHAN = $kelurahan->ID_KELURAHAN;
        $infoArsipKtp->TAHUN_PEMBUATAN_KTP = $request->input('TAHUN_PEMBUATAN_KTP');
        $tahunPembuatanDokKtp = $infoArsipKtp->TAHUN_PEMBUATAN_KTP;
        $fileFields = [
            'FILE_LAMA',
            'FILE_KK',
            'FILE_KUTIPAN_KTP',
            'FILE_SK_HILANG',
            'FILE_AKTA_LAHIR',
            'FILE_IJAZAH',
            'FILE_SURAT_NIKAH_CERAI',
            'FILE_SURAT_PINDAH',
            'FILE_LAINNYA',
            'FILE_KTP',
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
                        $folderPath = $tahunPembuatanDokKtp . '/Arsip Ktp';
                        $file->storeAs($folderPath, $fileName, 'public');
                        // Simpan path file ke dalam database sesuai dengan field yang sesuai
                        $infoArsipKtp->$field = $fileName;
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

        $infoArsipKtp->save();

        if ($infoArsipKtp) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip Ktp berhasil ditambahkan',
                'data' => $infoArsipKtp,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip Ktp gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }


    public function updateKtp (Request $request, $ID_ARSIP)
    {
        $validator = app('validator')->make($request->all(), [
            'JUMLAH_BERKAS' => 'nullable|integer',
            'NO_BUKU' => 'nullable|integer',
            'NO_RAK' => 'nullable|integer',
            'NO_BARIS' => 'nullable|integer',
            'NO_BOKS' => 'nullable|integer',
            'LOK_SIMPAN' => 'nullable|string|max:25',
            'KETERANGAN' => 'nullable|string|max:15',
            'NO_DOK_KTP' => 'required|integer',
            'NAMA' => 'required|string|max:50',
            'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
            'FILE_KK' => 'nullable|file|max:25000|mimes:pdf',
            'FILE_KUTIPAN_KTP' => 'nullable|file|max:25000|mimes:pdf',
            'FILE_SK_HILANG' => 'nullable|file|max:25000|mimes:pdf',
            'FILE_AKTA_LAHIR' => 'nullable|file|max:25000|mimes:pdf',
            'FILE_IJAZAH' => 'nullable|file|max:25000|mimes:pdf',
            'FILE_SURAT_NIKAH_CERAI' => 'nullable|file|max:25000|mimes:pdf',
            'FILE_SURAT_PINDAH' => 'nullable|file|max:25000|mimes:pdf',
            'FILE_LAINNYA' => 'nullable|file|max:25000|mimes:pdf',
            'FILE_KTP' => 'nullable|file|max:25000|mimes:pdf',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }
        $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Kartu Tanda Penduduk')->value('ID_DOKUMEN');
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
        $arsip->NO_DOK_KTP = $request->input('NO_DOK_KTP');
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

        // Temukan info arsip KTP yang terkait
        $infoArsipKtp = InfoArsipKtp::where('ID_ARSIP', $ID_ARSIP)->first();

        if (!$infoArsipKtp) {
            return response()->json([
                'success' => false,
                'message' => 'Info arsip KTP tidak ditemukan',
            ], 404);
        }

        // Simpan data info arsip KTP sebelum diupdate untuk memeriksa apakah ada perubahan
        $infoArsipKtpBeforeUpdate = clone $infoArsipKtp;

        $infoArsipKtp->NO_DOK_KTP = $arsip->NO_DOK_KTP;
        $infoArsipKtp->NAMA = $request->input('NAMA');
        $tahunPembuatanDokKtp = $infoArsipKtp->TAHUN_PEMBUATAN_KTP;
        $fileFields = [
            'FILE_LAMA',
            'FILE_KK',
            'FILE_KUTIPAN_KTP',
            'FILE_SK_HILANG',
            'FILE_AKTA_LAHIR',
            'FILE_IJAZAH',
            'FILE_SURAT_NIKAH_CERAI',
            'FILE_SURAT_PINDAH',
            'FILE_LAINNYA',
            'FILE_KTP',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $allowedExtensions = ['pdf'];
                $file = $request->file($field);
                $extension = $file->getClientOriginalExtension();

                if (in_array($extension, $allowedExtensions)) {
                    if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                        $fileName = $file->getClientOriginalName();
                        $folderPath = $tahunPembuatanDokKtp . '/Arsip Ktp';
                        $oldFileName = $infoArsipKtp->$field;
                        if ($oldFileName) {
                            $oldFilePath = $folderPath . '/' . $oldFileName;
                            if (Storage::disk('public')->exists($oldFilePath)) {
                                Storage::disk('public')->delete($oldFilePath);
                            }
                        }
                        $file->storeAs($folderPath, $fileName, 'public');
                        $infoArsipKtp ->$field = $fileName;
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
        if (!$infoArsipKtp->isDirty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Ktp tidak ada perubahan',
                'data' => $infoArsipKtpBeforeUpdate,
            ], 200);
        }
        $infoArsipKtp->save();

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
            'info_arsip_ktp' => $infoArsipKtp,
        ], 200);
    }
}
