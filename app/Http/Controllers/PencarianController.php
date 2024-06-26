<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\Operator;
use App\Models\HistoryPelayanan;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\JenisDokumen;
use Illuminate\Support\Facades\Storage;

class PencarianController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

     public function pencarianFilter(Request $request)
     {
         $validator = app('validator')->make($request->all(), [
             'JENIS_DOKUMEN' => 'nullable|exists:jenis_dokumen,ID_DOKUMEN',
             'NO_DOKUMEN' => 'nullable|string',
             'NAMA' => 'nullable|string',
         ]);

         if ($validator->fails()) {
             return response()->json([
                 'success' => false,
                 'message' => 'Validasi gagal',
                 'errors' => $validator->errors()
             ], 400);
         }

         $query = Arsip::with('jenisDokumen');

         if ($request->has('JENIS_DOKUMEN') && $request->JENIS_DOKUMEN != null) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('ID_DOKUMEN', $request->JENIS_DOKUMEN);
            });
        }

         if ($request->has('NO_DOKUMEN')) {
             $query->where(function ($q) use ($request) {
                 $q->where('NO_DOK_PENGANGKATAN', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_SURAT_PINDAH', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_PERCERAIAN', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_PENGESAHAN', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_KEMATIAN', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_KELAHIRAN', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_PENGAKUAN', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_PERKAWINAN', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_KK', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_SKOT', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_SKTT', 'LIKE', '%' . $request->NO_DOKUMEN . '%')
                     ->orWhere('NO_DOK_KTP', 'LIKE', '%' . $request->NO_DOKUMEN . '%');
             });
         }

         if ($request->has('NAMA')) {
             $query->where(function ($q) use ($request) {
                 $q->whereHas('infoArsipPengangkatan', function ($arsipquery) use ($request) {
                     $arsipquery->where('NAMA_ANAK', 'LIKE', '%' . $request->NAMA . '%');
                 })
                 ->orWhereHas('infoArsipSuratPindah', function ($arsipquery) use ($request) {
                     $arsipquery->where('NAMA_KEPALA', 'LIKE', '%' . $request->NAMA . '%');
                 })
                 ->orWhereHas('infoArsipPerceraian', function ($arsipquery) use ($request) {
                     $arsipquery->where(function ($q) use ($request) {
                         $q->where('NAMA_PRIA', 'LIKE', '%' . $request->NAMA . '%')
                           ->orWhere('NAMA_WANITA', 'LIKE', '%' . $request->NAMA . '%');
                     });
                 })
                 ->orWhereHas('infoArsipPengesahan', function ($arsipquery) use ($request) {
                     $arsipquery->where('NAMA_ANAK', 'LIKE', '%' . $request->NAMA . '%');
                 })
                 ->orWhereHas('infoArsipKematian', function ($arsipquery) use ($request) {
                     $arsipquery->where('NAMA', 'LIKE', '%' . $request->NAMA . '%');
                 })
                 ->orWhereHas('infoArsipKelahiran', function ($arsipquery) use ($request) {
                     $arsipquery->where('NAMA', 'LIKE', '%' . $request->NAMA . '%');
                 })
                 ->orWhereHas('infoArsipPengakuan', function ($arsipquery) use ($request) {
                     $arsipquery->where('NAMA_ANAK', 'LIKE', '%' . $request->NAMA . '%');
                 })
                 ->orWhereHas('infoArsipPerkawinan', function ($arsipquery) use ($request) {
                     $arsipquery->where(function ($q) use ($request) {
                         $q->where('NAMA_PRIA', 'LIKE', '%' . $request->NAMA . '%')
                           ->orWhere('NAMA_WANITA', 'LIKE', '%' . $request->NAMA . '%');
                     });
                 })
                 ->orWhereHas('infoArsipKk', function ($arsipquery) use ($request) {
                     $arsipquery->where('NAMA_KEPALA', 'LIKE', '%' . $request->NAMA . '%');
                 })
                 ->orWhereHas('infoArsipSkot', function ($arsipquery) use ($request) {
                    $arsipquery->where('NAMA', 'LIKE', '%' . $request->NAMA . '%');
                 })
                 ->orWhereHas('infoArsipSktt', function ($arsipquery) use ($request) {
                     $arsipquery->where('NAMA', 'LIKE', '%' . $request->NAMA . '%');
                 })
                 ->orWhereHas('infoArsipKtp', function ($arsipquery) use ($request) {
                     $arsipquery->where('NAMA', 'LIKE', '%' . $request->NAMA . '%');
                 });
             });
         }

         $arsips = $query->get();
         $formattedArsips = $arsips->map(function ($arsip) {
             $NAMA = [];
             $DOKUMEN = [];
             $models = [
                 'infoArsipPengangkatan' => ['NAMA_ANAK', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_PENGANGKATAN'],
                 'infoArsipSuratPindah' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_SKP_WNI', 'FILE_KTP_ASAL', 'FILE_NIKAH_CERAI', 'FILE_AKTA_KELAHIRAN', 'FILE_KK', 'FILE_F101', 'FILE_102','FILE_F103', 'FILE_DOK_PENDUKUNG', 'FILE_LAINNYA', 'FILE_SURAT_PINDAH'],
                 'infoArsipPerceraian' => ['NAMA_PRIA', 'NAMA_WANITA', 'FILE_LAMA', 'FILE_F201', 'FILE_FC_PP', 'FILE_KUTIPAN_PERKAWINAN', 'FILE_KTP', 'FILE_KK', 'FILE_SPTJM', 'FILE_LAINNYA', 'FILE_AKTA_PERCERAIAN', 'FILE_AKTA_PERKAWINAN'],
                 'infoArsipPengesahan' => ['NAMA_ANAK', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_PENGESAHAN'],
                 'infoArsipKematian' => ['NAMA', 'FILE_LAMA', 'FILE_F201', 'FILE_SK_KEMATIAN', 'FILE_KK', 'FILE_KTP', 'FILE_KTP_SUAMI_ISTRI', 'FILE_KUTIPAN_KEMATIAN', 'FILE_FC_PP', 'FILE_FC_DOK_PERJALANAN', 'FILE_DOK_PENDUKUNG', 'FILE_SPTJM', 'FILE_LAINNYA', 'FILE_AKTA_KEMATIAN'],
                 'infoArsipKelahiran' => ['NAMA', 'FILE_LAMA', 'FILE_KK', 'FILE_KTP_AYAH', 'FILE_KTP_IBU', 'FILE_F102', 'FILE_F201', 'FILE_BUKU_NIKAH', 'FILE_KUTIPAN_KELAHIRAN', 'FILE_SURAT_KELAHIRAN', 'FILE_SPTJM_PENERBITAN', 'FILE_PELAPORAN_KELAHIRAN', 'FILE_LAINNYA', 'FILE_AKTA_KELAHIRAN'],
                 'infoArsipPengakuan' => ['NAMA_ANAK', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_PENGAKUAN'],
                 'infoArsipPerkawinan' => ['NAMA_PRIA', 'NAMA_WANITA', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_F201', 'FILE_FC_SK_KAWIN', 'FILE_FC_PASFOTO', 'FILE_KTP', 'FILE_KK', 'FILE_AKTA_KEMATIAN', 'FILE_AKTA_PERCERAIAN', 'FILE_SPTJM', 'FILE_LAINNYA', 'FILE_AKTA_PERKAWINAN'],
                 'infoArsipKk' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_F101', 'FILE_NIKAH_CERAI', 'FILE_SK_PINDAH', 'FILE_SK_PINDAH_LUAR', 'FILE_SK_PENGGANTI', 'FILE_PUTUSAN_PRESIDEN', 'FILE_KK_LAMA', 'FILE_SK_PERISTIWA', 'FILE_SK_HILANG', 'FILE_KTP', 'FILE_LAINNYA', 'FILE_KK'],
                 'infoArsipSkot' => ['NAMA', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_SKOT'],
                 'infoArsipSktt' => ['NAMA', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_SKTT'],
                 'infoArsipKtp' => ['NAMA', 'FILE_LAMA', 'FILE_KK', 'FILE_KUTIPAN_KTP', 'FILE_SK_HILANG', 'FILE_AKTA_LAHIR', 'FILE_IJAZAH', 'FILE_SURAT_NIKAH_CERAI', 'FILE_SURAT_PINDAH', 'FILE_LAINNYA', 'FILE_KTP'],
             ];

             foreach ($models as $relation => $columns) {
                 if (is_array($columns)) {
                     foreach ($columns as $column) {
                         if (!empty($arsip->$relation->$column)) {
                             if (strpos($column, 'NAMA') !== false) {
                                 $NAMA[] = $arsip->$relation->$column;
                             } elseif (strpos($column, 'FILE_') !== false) {
                                 $DOKUMEN[] = $arsip->$relation->$column;
                             }
                         }
                     }
                 } else {
                     if (!empty($arsip->$relation->$columns)) {
                         if (strpos($columns, 'NAMA') !== false) {
                             $NAMA[] = $arsip->$relation->$columns;
                         } elseif (strpos($columns, 'FILE_') !== false) {
                             $DOKUMEN[] = $arsip->$relation->$columns;
                         }
                     }
                 }
             }

             $NAMA = implode(', ', $NAMA);
             $DOKUMEN = implode(', ', array_filter($DOKUMEN));

             return [
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
                 'NAMA' => $NAMA,
                 'DOKUMEN' => $DOKUMEN,
                 'JUMLAH_BERKAS' => $arsip->JUMLAH_BERKAS,
                 'NO_BUKU' => $arsip->NO_BUKU,
                 'NO_RAK' => $arsip->NO_RAK,
                 'NO_BARIS' => $arsip->NO_BARIS,
                 'NO_BOKS' => $arsip->NO_BOKS,
                 'LOK_SIMPAN' => $arsip->LOK_SIMPAN,
                 'TANGGAL_PINDAI' => $arsip->TANGGAL_PINDAI,
                 'KETERANGAN' => $arsip->KETERANGAN,
             ];
         });

         if ($formattedArsips->isNotEmpty()) {
             return response()->json([
                 'success' => true,
                 'message' => 'Sukses Menampilkan Data Arsip',
                 'arsips' => $formattedArsips
             ], 200);
         } else {
             return response()->json([
                 'success' => false,
                 'message' => 'Tidak ada data Arsip',
                 'arsips' => []
             ], 404);
         }
     }

    public function getAllArsip()
    {
        $arsips = Arsip::with('jenisDokumen')
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
                       ])->get();

        if ($arsips->isNotEmpty()) {
            $formattedArsips = $arsips->map(function ($arsip) {
                $NAMA = [];
                $DOKUMEN = [];
                $models = [
                    'infoArsipPengangkatan' => ['NAMA_ANAK', 'FILE_LAMA','FILE_LAINNYA','FILE_PENGANGKATAN'],

                    'infoArsipSuratPindah' => ['NAMA_KEPALA','FILE_LAMA','FILE_SKP_WNI','FILE_KTP_ASAL','FILE_NIKAH_CERAI',
                                            'FILE_AKTA_KELAHIRAN','FILE_KK','FILE_F101','FILE_102','FILE_F103','FILE_DOK_PENDUKUNG',
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

                    'infoArsipKk' => ['NAMA_KEPALA', 'FILE_LAMA','FILE_F101','FILE_NIKAH_CERAI','FILE_SK_PINDAH','FILE_SK_PINDAH_LUAR','FILE_SK_PENGGANTI','FILE_PUTUSAN_PRESIDEN','FILE_KK_LAMA','FILE_SK_PERISTIWA','FILE_SK_HILANG',
                                    'FILE_KTP','FILE_LAINNYA','FILE_KK'],

                    'infoArsipSkot' => ['NAMA', 'FILE_LAMA','FILE_LAINNYA','FILE_SKOT'],

                    'infoArsipSktt' => ['NAMA', 'FILE_LAMA','FILE_LAINNYA','FILE_SKTT'],

                    'infoArsipKtp' => ['NAMA', 'FILE_LAMA','FILE_KK','FILE_KUTIPAN_KTP','FILE_SK_HILANG','FILE_AKTA_LAHIR',
                                        'FILE_IJAZAH','FILE_SURAT_NIKAH_CERAI','FILE_SURAT_PINDAH','FILE_LAINNYA','FILE_KTP'],
                ];

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

                $NAMA = implode(', ', $NAMA);
                $DOKUMEN = implode(', ', array_filter($DOKUMEN));

                return [
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
                    'NAMA' => $NAMA,
                    'DOKUMEN' => $DOKUMEN,
                    'JUMLAH_BERKAS' => $arsip->JUMLAH_BERKAS,
                    'NO_BUKU' => $arsip->NO_BUKU,
                    'NO_RAK' => $arsip->NO_RAK,
                    'NO_BARIS' => $arsip->NO_BARIS,
                    'NO_BOKS' => $arsip->NO_BOKS,
                    'LOK_SIMPAN' => $arsip->LOK_SIMPAN,
                    'TANGGAL_PINDAI' => $arsip->TANGGAL_PINDAI,
                    'KETERANGAN' => $arsip->KETERANGAN,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Sukses Menampilkan Data Arsip',
                'arsips' => $formattedArsips
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data Arsip',
                'arsips' => []
            ], 404);
        }
    }

    public function getArsipById($ID_ARSIP)
    {
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

        if ($arsip) {
            $formattedArsip = [
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
                'DOKUMEN' => '',
                'JUMLAH_BERKAS' => $arsip->JUMLAH_BERKAS,
                'NO_BUKU' => $arsip->NO_BUKU,
                'NO_RAK' => $arsip->NO_RAK,
                'NO_BARIS' => $arsip->NO_BARIS,
                'NO_BOKS' => $arsip->NO_BOKS,
                'LOK_SIMPAN' => $arsip->LOK_SIMPAN,
                'TANGGAL_PINDAI' => $arsip->TANGGAL_PINDAI,
                'KETERANGAN' => $arsip->KETERANGAN,
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

            $NAMA = [];
            $models = [
                'infoArsipPengangkatan' => ['NAMA_ANAK', 'FILE_LAMA','FILE_LAINNYA','FILE_PENGANGKATAN'],
                'infoArsipSuratPindah' => ['NAMA_KEPALA','FILE_LAMA','FILE_SKP_WNI','FILE_KTP_ASAL','FILE_NIKAH_CERAI',
                                            'FILE_AKTA_KELAHIRAN','FILE_KK','FILE_F101','FILE_102','FILE_F103','FILE_DOK_PENDUKUNG',
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
                'infoArsipKk' => ['NAMA_KEPALA', 'FILE_LAMA','FILE_F101','FILE_NIKAH_CERAI','FILE_SK_PINDAH','FILE_SK_PINDAH_LUAR', 'FILE_SK_PENGGANTI','FILE_PUTUSAN_PRESIDEN','FILE_KK_LAMA','FILE_SK_PERISTIWA','FILE_SK_HILANG',
                                    'FILE_KTP','FILE_LAINNYA','FILE_KK'],
                'infoArsipSkot' => ['NAMA', 'FILE_LAMA','FILE_LAINNYA','FILE_SKOT'],
                'infoArsipSktt' => ['NAMA', 'FILE_LAMA','FILE_LAINNYA','FILE_SKTT'],
                'infoArsipKtp' => ['NAMA', 'FILE_LAMA','FILE_KK','FILE_KUTIPAN_KTP','FILE_SK_HILANG','FILE_AKTA_LAHIR',
                                    'FILE_IJAZAH','FILE_SURAT_NIKAH_CERAI','FILE_SURAT_PINDAH','FILE_LAINNYA','FILE_KTP'],
            ];

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
            return response()->json([
                'success' => false,
                'message' => 'Arsip tidak ditemukan',
                'arsip' => null
            ], 404);
        }
    }

    public function getArsipDokumenById($ID_ARSIP)
    {
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

        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Arsip tidak ditemukan',
            ], 404);
        }
        $models = [
            'infoArsipPengangkatan' => ['FILE_LAMA','FILE_LAINNYA','FILE_PENGANGKATAN'],
            'infoArsipSuratPindah' => ['FILE_LAMA','FILE_SKP_WNI','FILE_KTP_ASAL','FILE_NIKAH_CERAI',
                                        'FILE_AKTA_KELAHIRAN','FILE_KK','FILE_F101','FILE_102','FILE_F103','FILE_DOK_PENDUKUNG',
                                        'FILE_LAINNYA','FILE_SURAT_PINDAH'],
            'infoArsipPerceraian' => ['FILE_LAMA','FILE_F201','FILE_FC_PP',
                                        'FILE_KUTIPAN_PERKAWINAN','FILE_KTP','FILE_KK','FILE_SPTJM','FILE_LAINNYA',
                                        'FILE_AKTA_PERCERAIAN','FILE_AKTA_PERKAWINAN'],
            'infoArsipPengesahan' => ['FILE_LAMA','FILE_LAINNYA','FILE_PENGESAHAN'],
            'infoArsipKematian' => ['FILE_LAMA','FILE_F201','FILE_SK_KEMATIAN','FILE_KK','FILE_KTP',
                                    'FILE_KTP_SUAMI_ISTRI','FILE_KUTIPAN_KEMATIAN','FILE_FC_PP','FILE_FC_DOK_PERJALANAN',
                                    'FILE_DOK_PENDUKUNG','FILE_SPTJM','FILE_LAINNYA','FILE_AKTA_KEMATIAN'],
            'infoArsipKelahiran' => ['FILE_LAMA','FILE_KK','FILE_KTP_AYAH','FILE_KTP_IBU','FILE_F102','FILE_F201',
                                    'FILE_BUKU_NIKAH','FILE_KUTIPAN_KELAHIRAN','FILE_SURAT_KELAHIRAN','FILE_SPTJM_PENERBITAN',
                                    'FILE_PELAPORAN_KELAHIRAN','FILE_LAINNYA','FILE_AKTA_KELAHIRAN'],
            'infoArsipPengakuan' => ['FILE_LAMA','FILE_LAINNYA','FILE_PENGAKUAN'],
            'infoArsipPerkawinan' => ['FILE_LAMA','FILE_LAINNYA','FILE_F201','FILE_FC_SK_KAWIN',
                                        'FILE_FC_PASFOTO','FILE_KTP','FILE_KK','FILE_AKTA_KEMATIAN','FILE_AKTA_PERCERAIAN',
                                        'FILE_SPTJM','FILE_LAINNYA','FILE_AKTA_PERKAWINAN'],
            'infoArsipKk' => ['FILE_LAMA','FILE_F101','FILE_NIKAH_CERAI','FILE_SK_PINDAH','FILE_SK_PINDAH_LUAR',
                                'FILE_SK_PENGGANTI','FILE_PUTUSAN_PRESIDEN','FILE_KK_LAMA','FILE_SK_PERISTIWA','FILE_SK_HILANG',
                                'FILE_KTP','FILE_LAINNYA','FILE_KK'],
            'infoArsipSkot' => ['FILE_LAMA','FILE_LAINNYA','FILE_SKOT'],
            'infoArsipSktt' => ['FILE_LAMA','FILE_LAINNYA','FILE_SKTT'],
            'infoArsipKtp' => ['FILE_LAMA','FILE_KK','FILE_KUTIPAN_KTP','FILE_SK_HILANG','FILE_AKTA_LAHIR',
                                'FILE_IJAZAH','FILE_SURAT_NIKAH_CERAI','FILE_SURAT_PINDAH','FILE_LAINNYA','FILE_KTP'],
        ];
        
        $formattedDokumen = [];
        foreach ($models as $relation => $columns) {
            foreach ($columns as $column) {
                if (!empty($arsip->$relation->$column) && strpos($column, 'FILE_') !== false) {
                    $formattedDokumen[$column] = $arsip->$relation->$column;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sukses Menampilkan Data Arsip',
            'arsip' => $formattedDokumen
        ], 200);
    }

}

