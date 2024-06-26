<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Arsip;
use Firebase\JWT\JWT;
use App\Models\HakAkses;
use App\Models\Operator;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Permission;
use App\Models\InfoArsipKk;
use App\Models\InfoArsipKtp;
use App\Models\JenisDokumen;
use Illuminate\Http\Request;
use App\Models\InfoArsipSkot;
use App\Models\InfoArsipSktt;
use App\Models\InfoArsipKematian;
use App\Models\InfoArsipKelahiran;
use App\Models\InfoArsipPengakuan;
use App\Models\InfoArsipPengesahan;
use App\Models\InfoArsipPerceraian;
use App\Models\InfoArsipPerkawinan;
use App\Models\InfoArsipSuratPindah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\InfoArsipPengangkatan;

class ManajemenController extends Controller
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

    public function getArsipById($ID_PERMISSION)
    {
        $permissionRequest = Permission::find($ID_PERMISSION);

        // Jika permintaan tidak ditemukan, kembalikan respons dengan status 404
        if (!$permissionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ijin tidak ditemukan',
            ], 404);
        }
        $ID_ARSIP = $permissionRequest->ID_ARSIP;
        // Cari arsip berdasarkan ID
        $arsip = Arsip::with('jenisDokumen')
                      ->with([
                          'infoArsipPengangkatan',
                          'infoArsipSuratPindah',
                          'infoArsipPerceraian',
                          'infoArsipPengesahan',
                          'infoArsipKematian',
                          'infoArsipKelahiran',
                          'infoArsipPengakuan',
                          'infoArsipPerkawinan',
                          'infoArsipKk',
                          'infoArsipSkot',
                          'infoArsipSktt',
                          'infoArsipKtp'
                      ])
                      ->find($ID_ARSIP);

        // Jika arsip ditemukan
        if ($arsip) {
            // Format data sesuai kebutuhan
            $formattedArsip = [
                'ID_PERMISSION' => $permissionRequest->ID_PERMISSION,
                'STATUS' => $permissionRequest->STATUS,
                'ID_ARSIP' => $arsip->ID_ARSIP,
                'ID_DOKUMEN' => $arsip->ID_DOKUMEN,
                'NAMA_DOKUMEN' => $arsip->jenisDokumen->NAMA_DOKUMEN ?? null,
                'NO_DOKUMEN' => implode(', ', array_filter([
                    $arsip->NO_DOK_PENGANGKATAN,
                    $arsip->NO_DOK_SURAT_PINDAH,
                    $arsip->NO_DOK_PERCERAIAN,
                    $arsip->NO_DOK_PENGESAHAN,
                    $arsip->NO_DOK_KEMATIAN,
                    $arsip->NO_DOK_KELAHIRAN,
                    $arsip->NO_DOK_PENGAKUAN,
                    $arsip->NO_DOK_PERKAWINAN,
                    $arsip->NO_DOK_KK,
                    $arsip->NO_DOK_SKOT,
                    $arsip->NO_DOK_SKTT,
                    $arsip->NO_DOK_KTP,
                ])),
                'NAMA' => '',
                'JUMLAH_BERKAS' => $arsip->JUMLAH_BERKAS,
                'NO_BUKU' => $arsip->NO_BUKU,
                'NO_RAK' => $arsip->NO_RAK,
                'NO_BARIS' => $arsip->NO_BARIS,
                'NO_BOKS' => $arsip->NO_BOKS,
                'LOK_SIMPAN' => $arsip->LOK_SIMPAN,
                'TANGGAL_PINDAI' => $arsip->TANGGAL_PINDAI,
                'KETERANGAN' => $arsip->KETERANGAN,


                // Tambahkan kolom lain sesuai kebutuhan
            ];

            $models = [
                'infoArsipPengangkatan',
                'infoArsipSuratPindah',
                'infoArsipPerceraian',
                'infoArsipPengesahan',
                'infoArsipKematian',
                'infoArsipKelahiran',
                'infoArsipPengakuan',
                'infoArsipPerkawinan',
                'infoArsipKk',
                'infoArsipSkot',
                'infoArsipSktt',
                'infoArsipKtp'
            ];

            foreach ($models as $relation) {
                if ($arsip->$relation) {
                    // Menggabungkan data ke dalam $formattedArsip tanpa membungkusnya dalam array
                    $formattedArsip['INFO_ARSIP'] = $arsip->$relation;
                    break; // Berhenti setelah menemukan data yang ada
                }
            }


            // Mendapatkan NAMA dan dokumen dari setiap tabel terkait
            $NAMA = [];
            $models = [
                'infoArsipPengangkatan' => ['NAMA_ANAK', 'FILE_LAMA','FILE_LAINNYA','FILE_PENGANGKATAN'],
                'infoArsipSuratPindah' => ['NAMA_KEPALA','FILE_LAMA','FILE_SKP_WNI','FILE_KTP_ASAL','FILE_NIKAH_CERAI',
                                            'FILE_AKTA_KELAHIRAN','FILE_KK','FILE_F101','FILE_102','FILE_DOK_PENDUKUNG',
                                            'FILE_LAINNYA','FILE_SURAT_PINDAH'],
                'infoArsipPerceraian' => ['NAMA_PRIA', 'NAMA_WANITA','FILE_LAMA','FILE_F201','FILE_FC_PP',
                                            'FILE_KUTIPAN_PERKAWINAN','FILE_KTP','FILE_KK','FILE_SPTJM','FILE_LAINNYA',
                                            'FILE_AKTA_PERCERAIAN','FILE_AKTA_PERKAWINAN'],
                'infoArsipPengesahan' => ['NAMA_ANAK', 'FILE_LAMA','FILE_LAINNYA','FILE_PENGESAHAN'],
                'infoArsipKematian' => ['NAMA', 'FILE_LAMA','FILE_F201','FILE_SK_KEMATIAN','FILE_KK','FILE_KTP',
                                        'FILE_KTP_SUAMI_ISTRI','FILE_KUTIPAN_KEMATIAN','FILE_FC_PP','FILE_FC_DOK_PERJALANAN',
                                        'FILE_DOK_PENDUKUNG','FILE_SPTJM','FILE_LAINNYA','FILE_AKTA_KEMATIAN'],
                'infoArsipKelahiran' => ['NAMA', 'FILE_LAMA','FILE_KK','FILE_KTP_AYAH','FILE_KTP_IBU','FILE_F102','FILE_F201',
                                        'FILE_BUKU_NIKAH','FILE_KUTIPAN_KELAHIRAN','FILE_SURAT_KELAHIRAN','FILE_SPTJM_PENERBITAN',
                                        'FILE_PELAPORAN_KELAHIRAN','FILE_LAINNYA','FILE_AKTA_KELAHIRAN'],
                'infoArsipPengakuan' => ['NAMA_ANAK', 'FILE_LAMA','FILE_LAINNYA','FILE_PENGAKUAN'],
                'infoArsipPerkawinan' => ['NAMA_PRIA', 'NAMA_WANITA','FILE_LAMA','FILE_LAINNYA','FILE_F201','FILE_FC_SK_KAWIN',
                                            'FILE_FC_PASFOTO','FILE_KTP','FILE_KK','FILE_AKTA_KEMATIAN','FILE_AKTA_PERCERAIAN',
                                            'FILE_SPTJM','FILE_LAINNYA','FILE_AKTA_PERKAWINAN'],
                'infoArsipKk' => ['NAMA_KEPALA', 'FILE_LAMA','FILE_F101','FILE_NIKAH_CERAI','FILE_SK_PINDAH','FILE_SK_PINDAH_LUAR',
                                    'FILE_SK_PENGGANTI','FILE_PUTUSAN_PRESIDEN','FILE_KK_LAMA','FILE_SK_PERISTIWA','FILE_SK_HILANG',
                                    'FILE_KTP','FILE_LAINNYA','FILE_KK'],
                'infoArsipSkot' => ['NAMA', 'FILE_LAMA','FILE_LAINNYA','FILE_SKOT'],
                'infoArsipSktt' => ['NAMA', 'FILE_LAMA','FILE_LAINNYA','FILE_SKTT'],
                'infoArsipKtp' => ['NAMA', 'FILE_LAMA','FILE_KK','FILE_KUTIPAN_KTP','FILE_SK_HILANG','FILE_AKTA_LAHIR',
                                    'FILE_IJAZAH','FILE_SURAT_NIKAH_CERAI','FILE_SURAT_PINDAH','FILE_LAINNYA','FILE_KTP'],
            ];


            // Mendapatkan NAMA dan dokumen dari setiap tabel terkait
            foreach ($models as $relation => $columns) {
                if (is_array($columns)) {
                    foreach ($columns as $column) {
                        if (!empty($arsip->$relation->$column)) {
                            if (strpos($column, 'NAMA'&'NAMA_') !== false) {
                                $NAMA[] = $arsip->$relation->$column;
                            } elseif (strpos($column, 'FILE_') !== false) {
                                $DOKUMEN[] = $arsip->$relation->$column;
                            }
                        }
                    }
                } else {
                    if (!empty($arsip->$relation->$columns)) {
                        if (strpos($columns, 'NAMA'&'NAMA_') !== false) {
                            $NAMA[] = $arsip->$relation->$columns;
                        } elseif (strpos($columns, 'FILE_') !== false) {
                            $DOKUMEN[] = $arsip->$relation->$columns;
                        }
                    }
                }
            }

            // Gabungkan NAMA dan dokumen menjadi satu string
            $formattedArsip['NAMA'] = implode(', ', $NAMA);

            $formattedDokumen = [];
            foreach ($models as $relation => $columns) {
                foreach ($columns as $column) {
                    if (!empty($arsip->$relation->$column) && strpos($column, 'FILE_') !== false) {
                        $formattedDokumen[$column] = $arsip->$relation->$column;
                    }
                }
            }

            $formattedArsip['DOKUMEN'] = $formattedDokumen;

            return response()->json([
                'success' => true,
                'message' => 'Sukses Menampilkan Data Arsip',
                'arsip' => $formattedArsip
            ], 200);
        } else {
            // Jika arsip tidak ditemukan
            return response()->json([
                'success' => false,
                'message' => 'Arsip tidak ditemukan',
                'arsip' => null
            ], 404);
        }
    }

    public function editInput( Request $request,$ID_PERMISSION, $ID_ARSIP )
    {
        $permissionRequest = Permission::find($ID_PERMISSION);

        // Jika permintaan tidak ditemukan, kembalikan respons dengan status 404
        if (!$permissionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ijin tidak ditemukan',
            ], 404);
        }
        // Cari arsip berdasarkan ID_ARSIP dengan informasi jenis dokumen terkait
        $arsip = Arsip::with('jenisDokumen')
                      ->with([
                          'infoArsipPengangkatan',
                          'infoArsipSuratPindah',
                          'infoArsipPerceraian',
                          'infoArsipPengesahan',
                          'infoArsipKematian',
                          'infoArsipKelahiran',
                          'infoArsipPengakuan',
                          'infoArsipPerkawinan',
                          'infoArsipKk',
                          'infoArsipSkot',
                          'infoArsipSktt',
                          'infoArsipKtp'
                      ])->find($ID_ARSIP);

        // Jika arsip tidak ditemukan, kembalikan respons dengan status 404
        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Arsip tidak ditemukan',
            ], 404);
        }

        $jenisDokumen = $arsip->jenisDokumen->NAMA_DOKUMEN;

        // Lakukan proses upload dokumen sesuai dengan jenis dokumen
        switch ($jenisDokumen) {
            case 'Akta Pengangkatan Anak':
                    // Validasi input
                $validator = app('validator')->make($request->all(), [
                    'JUMLAH_BERKAS' => 'nullable|integer',
                    'NO_BUKU' => 'nullable|integer',
                    'NO_RAK' => 'nullable|integer',
                    'NO_BARIS' => 'nullable|integer',
                    'NO_BOKS' => 'nullable|integer',
                    'LOK_SIMPAN' => 'nullable|string|max:25',
                    'KETERANGAN' => 'nullable|string|max:15',
                    // 'NAMA_ANAK' => 'nullable|string|max:50',
                    'NIK' => 'nullable|string|max:16',
                    'TANGGAL_LAHIR' => 'nullable|date',
                    'JENIS_KELAMIN' => 'nullable|string|max:15',
                    'NO_PP' => 'nullable|string|max:25',
                    'TANGGAL_PP' => 'nullable|date',
                    'NAMA_AYAH' => 'nullable|string|max:50',
                    'NAMA_IBU' => 'nullable|string|max:50',
                    'THN_PEMBUATAN_DOK_PENGANGKATAN' => 'nullable|integer',
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
                // Isi kolom-kolom lainnya sesuai dengan nilai dari request
                // $infoArsipPengangkatan->NAMA_ANAK = $request->input('NAMA_ANAK');
                $infoArsipPengangkatan->NIK = $request->input('NIK');
                $infoArsipPengangkatan->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
                $infoArsipPengangkatan->JENIS_KELAMIN = $request->input('JENIS_KELAMIN');
                $infoArsipPengangkatan->NO_PP = $request->input('NO_PP');
                $infoArsipPengangkatan->TANGGAL_PP = $request->input('TANGGAL_PP');
                $infoArsipPengangkatan->NAMA_AYAH = $request->input('NAMA_AYAH');
                $infoArsipPengangkatan->NAMA_IBU = $request->input('NAMA_IBU');
                $infoArsipPengangkatan->THN_PEMBUATAN_DOK_PENGANGKATAN = $request->input('THN_PEMBUATAN_DOK_PENGANGKATAN');

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
                    // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                    return response()->json([
                        'success' => true,
                        'message' => 'Data Pengangkatan tidak ada perubahan',
                        'data' => $infoArsipPengangkatanBeforeUpdate,
                    ], 200);
                }

                // Simpan perubahan pada data info arsip pengangkatan
                $infoArsipPengangkatan->save();
                break;
            case 'Surat Pindah':
                            // Validasi input
                    $validator = app('validator')->make($request->all(), [
                        'JUMLAH_BERKAS' => 'nullable|integer',
                        'NO_BUKU' => 'nullable|integer',
                        'NO_RAK' => 'nullable|integer',
                        'NO_BARIS' => 'nullable|integer',
                        'NO_BOKS' => 'nullable|integer',
                        'LOK_SIMPAN' => 'nullable|string|max:25',
                        'KETERANGAN'=>'nullable|string|max:15',
                        // 'NO_DOK_SURAT_PINDAH' => 'required|string|max:25|unique:info_arsip_surat_pindah',
                        'NO_KK' => 'nullable|integer',
                        // 'NAMA_KEPALA' => 'nullable|string|max:50',
                        'NIK_KEPALA' => 'nullable|integer',
                        'ALASAN_PINDAH' => 'nullable|string|max:50',
                        'ALAMAT' => 'nullable|string|max:50',
                        'RT' => 'nullable|integer',
                        'RW' => 'nullable|integer',
                        'PROV' => 'nullable|string|max:50',
                        'KOTA' => 'nullable|string|max:50',
                        'ID_KECAMATAN' => 'nullable|integer',
                        'ID_KELURAHAN' =>'nullable|integer',
                        'KODEPOS' =>'nullable|integer',
                        'ALAMAT_TUJUAN' => 'nullable|string|max:50',
                        'RT_TUJUAN' => 'nullable|integer',
                        'RW_TUJUAN' => 'nullable|integer',
                        'PROV_TUJUAN' => 'nullable|string|max:25',
                        'KOTA_TUJUAN' => 'nullable|string|max:25',
                        'KEC_TUJUAN' => 'nullable|string|max:25',
                        'KEL_TUJUAN' => 'nullable|string|max:25',
                        'KODEPOS_TUJUAN' =>'nullable|integer',
                        'THN_PEMBUATAN_DOK_SURAT_PINDAH' => 'nullable|integer',
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

                    $infoArsipSuratPindah = InfoArsipSuratPindah::where('ID_ARSIP', $ID_ARSIP)->first();

                    if (!$infoArsipSuratPindah) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Info arsip Surat Pindah tidak ditemukan',
                        ], 404);
                    }

                    // Simpan data info arsip SKTT sebelum diupdate untuk memeriksa apakah ada perubahan
                    $infoArsipSuratPindahBeforeUpdate = clone $infoArsipSuratPindah;

                    // Simpan data ke dalam tabel "info_arsip_sktt"
                    $infoArsipSuratPindah->NO_KK = $request->input('NO_KK');
                    // $infoArsipSuratPindah->NAMA_KEPALA = $request->input('NAMA_KEPALA');
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

                    
                    if (!$infoArsipSuratPindah->isDirty()) {
                        // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                        return response()->json([
                            'success' => true,
                            'message' => 'Data Surat Pindah tidak ada perubahan',
                            'data' => $infoArsipSuratPindahBeforeUpdate,
                        ], 200);
                    }
                $infoArsipSuratPindah->save();
                break;
            case 'Akta Perceraian':
                        // Validasi input
                    $validator = app('validator')->make($request->all(), [
                        'JUMLAH_BERKAS' => 'nullable|integer',
                        'NO_BUKU' => 'nullable|integer',
                        'NO_RAK' => 'nullable|integer',
                        'NO_BARIS' => 'nullable|integer',
                        'NO_BOKS' => 'nullable|integer',
                        'LOK_SIMPAN' => 'nullable|string|max:25',
                        'KETERANGAN'=>'nullable|string|max:15',
                        // 'NAMA_PRIA' => 'nullable|string|max:50',
                        'NAMA_WANITA' => 'nullable|string|max:50',
                        'ALAMAT_PRIA' => 'nullable|string|max:255',
                        'ALAMAT_WANITA' => 'nullable|string|max:255',
                        'NO_PP' => 'nullable|string|max:25',
                        'TANGGAL_PP' => 'nullable|date',
                        'DOMISILI_CERAI' => 'nullable|string|max:255',
                        'NO_PERKAWINAN' => 'nullable|string|max:25',
                        'TANGGAL_DOK_PERKAWINAN' => 'nullable|date',
                        'TAHUN_PEMBUATAN_DOK_PERCERAIAN' => 'nullable|integer',
                        'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
                        'FILE_F201' => 'nullable|file|mimes:pdf|max:25000',
                        'FILE_FC_PP' => 'nullable|file|mimes:pdf|max:25000',
                        'FILE_KUTIPAN_PERKAWINAN' => 'nullable|file|mimes:pdf|max:25000',
                        'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
                        'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
                        'FILE_SPTJM' => 'nullable|file|mimes:pdf|max:25000',
                        'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
                        'FILE_AKTA_PERCERAIAN' => 'nullable|file|mimes:pdf|max:25000',
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
                    // $infoArsipPerceraian->NAMA_PRIA = $request->input('NAMA_PRIA');
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
                        // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                        return response()->json([
                            'success' => true,
                            'message' => 'Data Perceraian tidak ada perubahan',
                            'data' => $infoArsipPerceraianBeforeUpdate,
                        ], 200);
                    }
                $infoArsipPerceraian->save();
                break;
            case 'Akta Pengesahan Anak':
                        // Validasi input
                    $validator = app('validator')->make($request->all(), [
                        'JUMLAH_BERKAS' => 'nullable|integer',
                        'NO_BUKU' => 'nullable|integer',
                        'NO_RAK' => 'nullable|integer',
                        'NO_BARIS' => 'nullable|integer',
                        'NO_BOKS' => 'nullable|integer',
                        'LOK_SIMPAN' => 'nullable|string|max:25',
                        'KETERANGAN' => 'nullable|string|max:15',
                        // 'NAMA_ANAK' => 'required|string|max:50',
                        'TANGGAL_LAHIR' => 'nullable|date',
                        'TEMPAT_LAHIR' => 'nullable|string|max:25',
                        'JENIS_KELAMIN' => 'nullable|string|max:15',
                        'NO_AKTA_KELAHIRAN' => 'nullable|string|max:25',
                        'NO_PP' => 'nullable|string|max:25',
                        'TANGGAL_PP' => 'nullable|date',
                        'NAMA_AYAH' => 'nullable|string|max:50',
                        'NAMA_IBU' => 'nullable|string|max:50',
                        'TAHUN_PEMBUATAN_DOK_PENGESAHAN' => 'nullable|integer',
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
                    // $infoArsipPengesahan->NAMA_ANAK = $request->input('NAMA_ANAK');
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
                        // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                        return response()->json([
                            'success' => true,
                            'message' => 'Data Pengesahan tidak ada perubahan',
                            'data' => $infoArsipPengesahanBeforeUpdate,
                        ], 200);
                    }
                $infoArsipPengesahan->save();
                break;
            case 'Akta Kematian':
                    // Validasi input
                $validator = app('validator')->make($request->all(), [
                    'JUMLAH_BERKAS' => 'nullable|integer',
                    'NO_BUKU' => 'nullable|integer',
                    'NO_RAK' => 'nullable|integer',
                    'NO_BARIS' => 'nullable|integer',
                    'NO_BOKS' => 'nullable|integer',
                    'LOK_SIMPAN' => 'nullable|string|max:25',
                    'KETERANGAN'=>'nullable|string|max:15',
                    // 'NAMA' => 'required|string|max:50',
                    'NIK' => 'nullable|integer',
                    'TEMPAT_LAHIR' => 'nullable|string|max:25',
                    'TANGGAL_LAHIR' => 'nullable|date',
                    'TANGGAL_MATI' => 'nullable|date',
                    'TEMPAT_MATI' => 'nullable|string|max:25',
                    'ALAMAT' => 'nullable|string|max:50',
                    'JENIS_KELAMIN' => 'nullable|string|max:15',
                    'AGAMA' => 'nullable|string|max:15',
                    'TANGGAL_LAPOR' => 'nullable|date',
                    'TAHUN_PEMBUATAN_DOK_KEMATIAN' => 'nullable|integer',
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

                // Temukan info arsip kematian yang terkait
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
                // $infoArsipKematian->NAMA = $request->input('NAMA');
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
                    // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                    return response()->json([
                        'success' => true,
                        'message' => 'Data Kematian tidak ada perubahan',
                        'data' => $infoArsipKematianBeforeUpdate,
                    ], 200);
                }
                $infoArsipKematian->save();
                break;
            case 'Akta Kelahiran':
                // Validasi input
                $validator = app('validator')->make($request->all(), [
                    'JUMLAH_BERKAS' => 'nullable|integer',
                    'NO_BUKU' => 'nullable|integer',
                    'NO_RAK' => 'nullable|integer',
                    'NO_BARIS' => 'nullable|integer',
                    'NO_BOKS' => 'nullable|integer',
                    'LOK_SIMPAN' => 'nullable|string|max:25',
                    'KETERANGAN'=>'nullable|string|max:15',
                    // 'NAMA' => 'required|string|max:50',
                    'TEMPAT_LAHIR' => 'nullable|string|max:25',
                    'TANGGAL_LAHIR' => 'nullable|date',
                    'ANAK_KE' => 'nullable|integer',
                    'NAMA_AYAH' => 'nullable|string|max:50',
                    'NAMA_IBU' => 'nullable|string|max:50',
                    'NO_KK' => 'nullable|integer',
                    'TAHUN_PEMBUATAN_DOK_KELAHIRAN' => 'nullable|integer',
                    'STATUS_KELAHIRAN' => 'nullable|string|max:25',
                    'STATUS_PENDUDUK' => 'nullable|string|max:25',
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
                // $infoArsipKelahiran->NAMA = $request->input('NAMA');
                $infoArsipKelahiran->TEMPAT_LAHIR = $request->input('TEMPAT_LAHIR', $infoArsipKelahiran->TEMPAT_LAHIR);
                $infoArsipKelahiran->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR', $infoArsipKelahiran->TANGGAL_LAHIR);
                $infoArsipKelahiran->ANAK_KE = $request->input('ANAK_KE', $infoArsipKelahiran->ANAK_KE);
                $infoArsipKelahiran->NAMA_AYAH = $request->input('NAMA_AYAH', $infoArsipKelahiran->NAMA_AYAH);
                $infoArsipKelahiran->NAMA_IBU = $request->input('NAMA_IBU', $infoArsipKelahiran->NAMA_IBU);
                $infoArsipKelahiran->NO_KK = $request->input('NO_KK', $infoArsipKelahiran->NO_KK);
                $infoArsipKelahiran->TAHUN_PEMBUATAN_DOK_KELAHIRAN = $request->input('TAHUN_PEMBUATAN_DOK_KELAHIRAN', $infoArsipKelahiran->TAHUN_PEMBUATAN_DOK_KELAHIRAN);
                $infoArsipKelahiran->STATUS_KELAHIRAN = $request->input('STATUS_KELAHIRAN', $infoArsipKelahiran->STATUS_KELAHIRAN);
                $infoArsipKelahiran->STATUS_PENDUDUK = $request->input('STATUS_PENDUDUK', $infoArsipKelahiran->STATUS_PENDUDUK);

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
                $infoArsipKelahiran->save();
                break;
            case 'Akta Pengakuan Anak':
                $validator = app('validator')->make($request->all(), [
                    'JUMLAH_BERKAS' => 'nullable|integer',
                    'NO_BUKU' => 'nullable|integer',
                    'NO_RAK' => 'nullable|integer',
                    'NO_BARIS' => 'nullable|integer',
                    'NO_BOKS' => 'nullable|integer',
                    'LOK_SIMPAN' => 'nullable|string|max:25',
                    'KETERANGAN'=>'nullable|string|max:15',
                    // 'NO_DOK_PENGAKUAN' => 'nullable|string|max:25|',
                    // 'NAMA_ANAK' => 'nullable|string|max:50',
                    'TANGGAL_LAHIR' => 'nullable|date',
                    'TEMPAT_LAHIR' => 'nullable|string|max:25',
                    'JENIS_KELAMIN' => 'nullable|string|max:15',
                    'NO_PP' => 'nullable|string|max:25',
                    'TANGGAL_PP' => 'nullable|date',
                    'NO_AKTA_KELAHIRAN' => 'nullable|string|max:25',
                    'NAMA_AYAH' => 'nullable|string|max:50',
                    'NAMA_IBU' => 'nullable|string|max:50',
                    'TAHUN_PEMBUATAN_DOK_PENGAKUAN' => 'nullable|integer',
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

                // Update data arsip
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

                // Update data info arsip pengakuan
                // Isi kolom-kolom lainnya sesuai dengan nilai dari request
                // $infoArsipPengakuan->NAMA_ANAK = $request->input('NAMA_ANAK');
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
                    // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                    return response()->json([
                        'success' => true,
                        'message' => 'Data Pengakuan tidak ada perubahan',
                        'data' => $infoArsipPengakuanBeforeUpdate,
                    ], 200);
                }
                $infoArsipPengakuan->save();
                break;
            case 'Akta Perkawinan':
                $validator = app('validator')->make($request->all(), [
                    'JUMLAH_BERKAS' => 'nullable|integer',
                    'NO_BUKU' => 'nullable|integer',
                    'NO_RAK' => 'nullable|integer',
                    'NO_BARIS' => 'nullable|integer',
                    'NO_BOKS' => 'nullable|integer',
                    'LOK_SIMPAN' => 'nullable|string|max:25',
                    'KETERANGAN' => 'nullable|string|max:15',
                    // 'NO_DOK_PERKAWINAN' => 'required|string|max:25|unique:info_arsip_perkawinan
                    // 'NAMA_PRIA' => 'required|string|max:50',
                    'NAMA_WANITA' => 'nullable|string|max:50',
                    'TANGGAL_DOK_PERKAWINAN' => 'nullable|date',
                    'TEMPAT_KAWIN' => 'nullable|string|max:25',
                    'AGAMA_KAWIN' => 'nullable|string|max:15',
                    'AYAH_PRIA' => 'nullable|string|max:50',
                    'IBU_PRIA' => 'nullable|string|max:50',
                    'AYAH_WANITA' => 'nullable|string|max:50',
                    'IBU_WANITA' => 'nullable|string|max:50',
                    'TAHUN_PEMBUATAN_DOK_PERKAWINAN' => 'nullable|integer',
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
                // Isi kolom-kolom lainnya sesuai dengan nilai dari request
                // $infoArsipPerkawinan->NAMA_PRIA = $request->input('NAMA_PRIA');
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
                    // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                    return response()->json([
                        'success' => true,
                        'message' => 'Data Perkawinan tidak ada perubahan',
                        'data' => $infoArsipPerkawinanBeforeUpdate,
                    ], 200);
                }
                $infoArsipPerkawinan->save();
                break;
            case 'Kartu Keluarga':
                $validator = app('validator')->make($request->all(),[
                    'JUMLAH_BERKAS' => 'nullable|integer',
                    'NO_BUKU' => 'nullable|integer',
                    'NO_RAK' => 'nullable|integer',
                    'NO_BARIS' => 'nullable|integer',
                    'NO_BOKS' => 'nullable|integer',
                    'LOK_SIMPAN' => 'nullable|string|max:25',
                    'KETERANGAN'=>'nullable|string|max:15',
                    // 'NO_DOK_KK' => 'required|integer|unique:info_arsip_kk',
                    // 'NAMA_KEPALA' => 'nullable|string|max:50',
                    'ALAMAT' => 'nullable|string|max:50',
                    'RT' => 'nullable|integer|max:5',
                    'RW' => 'nullable|integer|max:5',
                    'KODEPOS' => 'nullable|integer|',
                    'PROV' => 'nullable|string|max:50',
                    'KOTA' => 'nullable|string|max:50',
                    'ID_KECAMATAN' => 'nullable|integer',
                    'ID_KELURAHAN' => 'nullable|integer',
                    'TAHUN_PEMBUATAN_DOK_KK' => 'nullable|integer',
                    'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_F101' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_NIKAH_CERAI' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_SK_PINDAH' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_SK_PINDAH_LUAR' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_SK_PENGGANTI' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_PUTUSAN_PRESIDEN' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_KK_LAMA' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_SK_PERISTIWA' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_SK_HILANG' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_KTP' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_KK' => 'nullable|file|mimes:pdf|max:25000',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $validator->errors()
                    ], 400);
                }

                $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'Kartu Keluarga')->value('ID_DOKUMEN');
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

                // Simpan data ke dalam tabel "arsip"
                $arsip->JUMLAH_BERKAS = $request->input('JUMLAH_BERKAS');
                $arsip->NO_BUKU = $request->input('NO_BUKU');
                $arsip->NO_RAK = $request->input('NO_RAK');
                $arsip->NO_BARIS = $request->input('NO_BARIS');
                $arsip->NO_BOKS = $request->input('NO_BOKS');
                $arsip->LOK_SIMPAN = $request->input('LOK_SIMPAN');
                $arsip->KETERANGAN = $request->input('KETERANGAN');
                $arsip->ID_DOKUMEN = $idDokumen;
                $arsip->TANGGAL_PINDAI = Carbon::now();

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

                // Temukan info arsip Kk yang terkait
                $infoArsipKk = InfoArsipKk::where('ID_ARSIP', $ID_ARSIP)->first();

                if (!$infoArsipKk) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Info arsip Kk tidak ditemukan',
                    ], 404);
                }

                // Simpan data info arsip KTP sebelum diupdate untuk memeriksa apakah ada perubahan
                $infoArsipKkBeforeUpdate = clone $infoArsipKk;

                // $infoArsipKk->NAMA_KEPALA = $request->input('NAMA_KEPALA');
                $infoArsipKk->ALAMAT = $request->input('ALAMAT');
                $infoArsipKk->RT = $request->input('RT');
                $infoArsipKk->RW = $request->input('RW');
                $infoArsipKk->KODEPOS = $request->input('KODEPOS');
                $infoArsipKk->PROV = $request->input('PROV');
                $infoArsipKk->KOTA = $request->input('KOTA');
                $infoArsipKk->TAHUN_PEMBUATAN_DOK_KK = $request->input('TAHUN_PEMBUATAN_DOK_KK');
                $kecamatan = Kecamatan::find($request->input('ID_KECAMATAN'));
                // Jika kecamatan tidak ditemukan
                if (!$kecamatan) {
                    return response()->json(['error' => 'Kecamatan tidak valid'], 400);
                }
                $infoArsipKk ->ID_KECAMATAN = $kecamatan->ID_KECAMATAN;
                $id_kelurahan = $request->input('ID_KELURAHAN');
                $kelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)
                            ->where('ID_KECAMATAN', $kecamatan->ID_KECAMATAN)
                            ->first();
                // Jika kelurahan tidak ditemukan
                if (!$kelurahan) {
                    return response()->json(['error' => 'Kelurahan tidak ditemukan sesuai kecamatan yang dipilih'], 400);
                }
                $infoArsipKk ->ID_KELURAHAN = $kelurahan->ID_KELURAHAN;
                $tahunPembuatanDokKk = $infoArsipKk->TAHUN_PEMBUATAN_DOK_KK;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_F101',
                    'FILE_NIKAH_CERAI',
                    'FILE_SK_PINDAH',
                    'FILE_SK_PINDAH_LUAR',
                    'FILE_SK_PENGGANTI',
                    'FILE_PUTUSAN_PRESIDEN',
                    'FILE_KK_LAMA',
                    'FILE_SK_PERISTIWA',
                    'FILE_SK_HILANG',
                    'FILE_KTP',
                    'FILE_LAINNYA',
                    'FILE_KK'
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
                                $folderPath = $tahunPembuatanDokKk . '/Arsip Kk';
                                $oldFileName = $infoArsipKk->$field;
                                if ($oldFileName) {
                                    $oldFilePath = $folderPath . '/' . $oldFileName;
                                    if (Storage::disk('public')->exists($oldFilePath)) {
                                        Storage::disk('public')->delete($oldFilePath);
                                    }
                                }
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipKk ->$field = $fileName;
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
                if (!$infoArsipKk->isDirty()) {
                    // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                    return response()->json([
                        'success' => true,
                        'message' => 'Data Ktp tidak ada perubahan',
                        'data' => $infoArsipKkBeforeUpdate,
                    ], 200);
                }
                $infoArsipKk ->save();
                break;
            case 'SKOT':
                $validator = app('validator')->make($request->all(), [
                    'JUMLAH_BERKAS' => 'nullable|integer',
                    'NO_BUKU' => 'nullable|integer',
                    'NO_RAK' => 'nullable|integer',
                    'NO_BARIS' => 'nullable|integer',
                    'NO_BOKS' => 'nullable|integer',
                    'LOK_SIMPAN' => 'nullable|string|max:25',
                    'KETERANGAN'=>'nullable|string|max:15',
                    // 'NO_DOK_SKOT' => 'required|string|max:25|unique:info_arsip_skot',
                    // 'NAMA' => 'nullable|string|max:50',
                    'NAMA_PANGGIL' => 'nullable|string|max:25',
                    'NIK' => 'nullable|integer',
                    'JENIS_KELAMIN' => 'nullable|string|max:15',
                    'TEMPAT_LAHIR' => 'nullable|string|max:25',
                    'TANGGAL_LAHIR' => 'nullable|date',
                    'AGAMA' => 'nullable|string|max:15',
                    'STATUS_KAWIN' => 'nullable|string|max:15',
                    'PEKERJAAN' => 'nullable|string|max:25',
                    'ALAMAT_ASAL' => 'nullable|string|max:50',
                    'PROV_ASAL' => 'nullable|string|max:25',
                    'KOTA_ASAL' => 'nullable|string|max:25',
                    'KEC_ASAL' => 'nullable|string|max:25',
                    'KEL_ASAL' => 'nullable|string|max:25',
                    'ALAMAT' => 'nullable|string|max:50',
                    'PROV' => 'nullable|string|max:50',
                    'KOTA' => 'nullable|string|max:50',
                    'ID_KELURAHAN' =>'nullable|integer',
                    'ID_KECAMATAN' => 'nullable|integer',
                    'TAHUN_PEMBUATAN_DOK_SKOT' => 'nullable|integer',
                    'FILE_LAMA' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_LAINNYA' => 'nullable|file|mimes:pdf|max:25000',
                    'FILE_SKOT' => 'nullable|file|mimes:pdf|max:25000',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $validator->errors()
                    ], 400);
                }

                $idDokumen = JenisDokumen::where('NAMA_DOKUMEN', 'SKOT')->value('ID_DOKUMEN');
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

                $infoArsipSkot = InfoArsipSkot::where('ID_ARSIP', $ID_ARSIP)->first();

                if (!$infoArsipSkot) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Info arsip SKOT tidak ditemukan',
                    ], 404);
                }

                // Simpan data info arsip SKOT sebelum diupdate untuk memeriksa apakah ada perubahan
                $infoArsipSkotBeforeUpdate = clone $infoArsipSkot;

                // Simpan data ke dalam tabel "info_arsip_skot"
                // $infoArsipSkot->NAMA = $request->input('NAMA');
                $infoArsipSkot->NAMA_PANGGIL = $request->input('NAMA_PANGGIL');
                $infoArsipSkot->NIK = $request->input('NIK');
                $infoArsipSkot->JENIS_KELAMIN = $request->input('JENIS_KELAMIN');
                $infoArsipSkot->TEMPAT_LAHIR = $request->input('TEMPAT_LAHIR');
                $infoArsipSkot->TANGGAL_LAHIR = $request->input('TANGGAL_LAHIR');
                $infoArsipSkot->AGAMA = $request->input('AGAMA');
                $infoArsipSkot->STATUS_KAWIN = $request->input('STATUS_KAWIN');
                $infoArsipSkot->PEKERJAAN = $request->input('PEKERJAAN');
                $infoArsipSkot->ALAMAT_ASAL = $request->input('ALAMAT_ASAL');
                $infoArsipSkot->PROV_ASAL = $request->input('PROV_ASAL');
                $infoArsipSkot->KOTA_ASAL = $request->input('KOTA_ASAL');
                $infoArsipSkot->KEC_ASAL = $request->input('KEC_ASAL');
                $infoArsipSkot->KEL_ASAL = $request->input('KEL_ASAL');
                $infoArsipSkot->ALAMAT = $request->input('ALAMAT');
                $infoArsipSkot->PROV = $request->input('PROV');
                $infoArsipSkot->KOTA = $request->input('KOTA');
                $infoArsipSkot->TAHUN_PEMBUATAN_DOK_SKOT = $request->input('TAHUN_PEMBUATAN_DOK_SKOT');
                $kecamatan = Kecamatan::find($request->input('ID_KECAMATAN'));
                // Jika kecamatan tidak ditemukan
                if (!$kecamatan) {
                    return response()->json(['error' => 'Kecamatan tidak valid'], 400);
                }
                $infoArsipSkot->ID_KECAMATAN = $kecamatan->ID_KECAMATAN;

                $id_kelurahan = $request->input('ID_KELURAHAN');
                $kelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)
                            ->where('ID_KECAMATAN', $kecamatan->ID_KECAMATAN)
                            ->first();
                // Jika kelurahan tidak ditemukan
                if (!$kelurahan) {
                    return response()->json(['error' => 'Kelurahan tidak ditemukan sesuai kecamatan yang dipilih'], 400);
                }
                $infoArsipSkot->ID_KELURAHAN = $kelurahan->ID_KELURAHAN;
                $tahunPembuatanDokSkot = $infoArsipSkot->TAHUN_PEMBUATAN_DOK_SKOT;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_LAINNYA',
                    'FILE_SKOT'
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
                                $folderPath = $tahunPembuatanDokSkot . '/Arsip Skot';
                                $oldFileName = $infoArsipSkot->$field;
                                if ($oldFileName) {
                                    $oldFilePath = $folderPath . '/' . $oldFileName;
                                    if (Storage::disk('public')->exists($oldFilePath)) {
                                        Storage::disk('public')->delete($oldFilePath);
                                    }
                                }
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipSkot->$field = $fileName;
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
                if (!$infoArsipSkot->isDirty()) {
                    // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                    return response()->json([
                        'success' => true,
                        'message' => 'Data Skot tidak ada perubahan',
                        'data' => $infoArsipSkotBeforeUpdate,
                    ], 200);
                }
                $infoArsipSkot->save();
                break;
            case 'SKTT':
                $validator = app('validator')->make($request->all(), [
                    'JUMLAH_BERKAS' => 'nullable|integer',
                    'NO_BUKU' => 'nullable|integer',
                    'NO_RAK' => 'nullable|integer',
                    'NO_BARIS' => 'nullable|integer',
                    'NO_BOKS' => 'nullable|integer',
                    'LOK_SIMPAN' => 'nullable|string|max:25',
                    'KETERANGAN'=>'nullable|string|max:15',
                    // 'NO_DOK_SKTT' => 'required|string|max:25|unique:info_arsip_sktt',
                    // 'NAMA' => 'required|string|max:50',
                    'JENIS_KELAMIN' => 'nullable|string|max:15',
                    'TEMPAT_LAHIR' => 'nullable|string|max:25',
                    'TANGGAL_LAHIR' => 'nullable|date',
                    'AGAMA' => 'nullable|string|max:15',
                    'STATUS_KAWIN' => 'nullable|string|max:15',
                    'KEBANGSAAN' => 'nullable|string|max:15',
                    'NO_PASPOR' => 'nullable|string|max:50',
                    'HUB_KELUARGA' => 'nullable|string|max:25',
                    'PEKERJAAN' => 'nullable|string|max:25',
                    'GOLDAR' => 'nullable|string|max:10',
                    'ALAMAT' => 'nullable|string|max:50',
                    'PROV' => 'nullable|string|max:50',
                    'KOTA' => 'nullable|string|max:50',
                    'ID_KELURAHAN' =>'nullable|integer',
                    'ID_KECAMATAN' => 'nullable|integer',
                    'TAHUN_PEMBUATAN_DOK_SKTT' => 'nullable|integer',
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
                // $infoArsipSktt->NAMA = $request->input('NAMA');
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

                if (!$infoArsipSktt->isDirty()) {
                    // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                    return response()->json([
                        'success' => true,
                        'message' => 'Data Sktt tidak ada perubahan',
                        'data' => $infoArsipSkttBeforeUpdate,
                    ], 200);
                }
                $infoArsipSktt->save();
                break;
            case 'Kartu Tanda Penduduk':
                $validator = app('validator')->make($request->all(), [
                    'JUMLAH_BERKAS' => 'nullable|integer',
                    'NO_BUKU' => 'nullable|integer',
                    'NO_RAK' => 'nullable|integer',
                    'NO_BARIS' => 'nullable|integer',
                    'NO_BOKS' => 'nullable|integer',
                    'LOK_SIMPAN' => 'nullable|string|max:25',
                    'KETERANGAN' => 'nullable|string|max:15',
                    // 'NAMA' => 'nullable|string|max:50',
                    'JENIS_KELAMIN' => 'nullable|string|max:15',
                    'TEMPAT_LAHIR' => 'nullable|string|max:25',
                    'TANGGAL_LAHIR' => 'nullable|date',
                    'AGAMA' => 'nullable|string|max:15',
                    'STATUS_KAWIN' => 'nullable|string|max:15',
                    'KEBANGSAAN' => 'nullable|string|max:15',
                    'NO_PASPOR' => 'nullable|string|max:25',
                    'HUB_KELUARGA' => 'nullable|string|max:25',
                    'PEKERJAAN' => 'nullable|string|max:25',
                    'GOLDAR' => 'nullable|string|max:10',
                    'ALAMAT' => 'nullable|string|max:50',
                    'PROV' => 'nullable|string|max:50',
                    'KOTA' => 'nullable|string|max:50',
                    'ID_KECAMATAN'=> 'nullable|integer',
                    'ID_KELURAHAN'=> 'nullable|integer',
                    'TAHUN_PEMBUATAN_KTP' => 'nullable|integer',
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

                // Update data info arsip KTP
                // Isi kolom-kolom lainnya sesuai dengan nilai dari request
                // $infoArsipKtp->NAMA = $request->input('NAMA');
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
                $infoArsipKtp->TAHUN_PEMBUATAN_KTP = $request->input('TAHUN_PEMBUATAN_KTP');
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
                if (!$infoArsipKtp->isDirty()) {
                    // Jika tidak ada perubahan, kembalikan respons tanpa melakukan penyimpanan ulang
                    return response()->json([
                        'success' => true,
                        'message' => 'Data Ktp tidak ada perubahan',
                        'data' => $infoArsipKtpBeforeUpdate,
                    ], 200);
                }
                $infoArsipKtp->save();
                break;
            // Tambahkan case lain sesuai dengan jenis dokumen yang ada
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis dokumen tidak didukung',
                    'jenis_dokumen' => $jenisDokumen,
                ], 400);
        }

        // Simpan dokumen sesuai dengan jenis dokumen
        Permission::where('ID_PERMISSION', $ID_PERMISSION)
          ->where('ID_ARSIP', $ID_ARSIP)
          ->update(['STATUS' => 'Disetujui']);
        // Mengembalikan respons berhasil
        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diupdate'
        ], 200);
    }

}




