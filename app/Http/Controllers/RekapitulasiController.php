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
use App\Models\Kecamatan;
use App\Models\Kelurahan;

class RekapitulasiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function filterBaseKecamatan (Request $request)
    {
        // Validasi input
        $validator = app('validator')->make($request->all(), [
            'JENIS_DOKUMEN' => 'nullable|exists:jenis_dokumen,ID_DOKUMEN',
            'ID_KECAMATAN' => 'nullable|exists:kecamatan,ID_KECAMATAN',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $namaKecamatan = Kecamatan::where('ID_KECAMATAN', $request->ID_KECAMATAN)->value('NAMA_KECAMATAN');
        $query = Arsip::with('jenisDokumen')
            ->with([
                'infoArsipSuratPindah',
                'infoArsipKk',
                'infoArsipSkot',
                'infoArsipSktt',
                'infoArsipKtp'
            ]);

        if ($request->has('JENIS_DOKUMEN') && $request->JENIS_DOKUMEN != null) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('ID_DOKUMEN', $request->JENIS_DOKUMEN);
            });
        }

        $query->where(function ($q) use ($request) {
            $q->whereHas('infoArsipSuratPindah', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            })->orWhereHas('infoArsipKk', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            })->orWhereHas('infoArsipSkot', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            })->orWhereHas('infoArsipSktt', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            })->orWhereHas('infoArsipKtp', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            });
        });

        $arsips = $query->get();

        // Jika arsip ditemukan
        if ($arsips->isNotEmpty()) {
            $formattedArsips = $arsips->map(function ($arsip) {
                $formattedArsip = [
                    'ID_ARSIP' => $arsip->ID_ARSIP,
                    'ID_DOKUMEN' => $arsip->ID_DOKUMEN,
                    'NAMA_DOKUMEN' => $arsip->jenisDokumen->NAMA_DOKUMEN ?? null,
                    'NO_DOKUMEN' => implode(', ', array_filter([
                        $arsip->NO_DOK_SURAT_PINDAH,
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
                ];

                $models = [
                    'infoArsipSuratPindah',
                    'infoArsipKk',
                    'infoArsipSkot',
                    'infoArsipSktt',
                    'infoArsipKtp'
                ];

                foreach ($models as $relation) {
                    if ($arsip->$relation) {
                        // Menggabungkan data ke dalam $formattedArsip tanpa membungkusnya dalam array
                        $formattedArsip['INFO_ARSIP'] = $arsip->$relation;
                        $id_kecamatan = $arsip->$relation->ID_KECAMATAN;
                        $namaKecamatan = Kecamatan::where('ID_KECAMATAN', $id_kecamatan)->value('NAMA_KECAMATAN');
                        $formattedArsip['INFO_ARSIP']->ID_KECAMATAN = $namaKecamatan;
                        $id_kelurahan = $arsip->$relation->ID_KELURAHAN;
                        $namakelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)->value('NAMA_KELURAHAN');
                        $formattedArsip['INFO_ARSIP']->ID_KELURAHAN = $namakelurahan;
                        break;
                    }
                }

                // Mendapatkan dokumen dari setiap tabel terkait

                $columnsMapping = [
                    'infoArsipSuratPindah' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_SKP_WNI', 'FILE_KTP_ASAL', 'FILE_NIKAH_CERAI', 'FILE_AKTA_KELAHIRAN', 'FILE_KK', 'FILE_F101', 'FILE_102', 'FILE_DOK_PENDUKUNG', 'FILE_LAINNYA', 'FILE_SURAT_PINDAH'],
                    'infoArsipKk' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_F101', 'FILE_NIKAH_CERAI', 'FILE_SK_PINDAH', 'FILE_SK_PINDAH_LUAR', 'FILE_SK_PENGGANTI', 'FILE_PUTUSAN_PRESIDEN', 'FILE_KK_LAMA', 'FILE_SK_PERISTIWA', 'FILE_SK_HILANG', 'FILE_KTP', 'FILE_LAINNYA', 'FILE_KK'],
                    'infoArsipSkot' => ['NAMA', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_SKOT'],
                    'infoArsipSktt' => ['NAMA', 'FILE_LAINNYA', 'FILE_SKTT'],
                    'infoArsipKtp' => ['NAMA', 'FILE_KK', 'FILE_KUTIPAN_KTP', 'FILE_SK_HILANG', 'FILE_AKTA_LAHIR', 'FILE_IJAZAH', 'FILE_SURAT_NIKAH_CERAI', 'FILE_SURAT_PINDAH', 'FILE_LAINNYA', 'FILE_KTP'],
                ];

                $formattedDokumen = [];
                foreach ($columnsMapping as $relation => $columns) {
                    foreach ($columns as $column) {
                        if (!empty($arsip->$relation->$column) && strpos($column, 'FILE_') !== false) {
                            $formattedDokumen[$column] = $arsip->$relation->$column;
                        }
                    }
                }

                $formattedArsip['DOKUMEN'] = $formattedDokumen;

                return $formattedArsip;
            });

            return response()->json([
                'success' => true,
                'message' => 'Sukses Menampilkan Rekapitulasi Kecamatan : '. $namaKecamatan,
                'arsips' => $formattedArsips
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

    public function filterBaseKelurahan (Request $request)
    {
        // Validasi input
        $validator = app('validator')->make($request->all(), [
            'JENIS_DOKUMEN' => 'nullable|exists:jenis_dokumen,ID_DOKUMEN',
            'ID_KELURAHAN' => 'nullable|exists:kelurahan,ID_KELURAHAN',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $namaKelurahan = Kelurahan::where('ID_KELURAHAN', $request->ID_KELURAHAN)->value('NAMA_KELURAHAN');
        $query = Arsip::with('jenisDokumen')
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
            ]);

        if ($request->has('JENIS_DOKUMEN') && $request->JENIS_DOKUMEN != null) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('ID_DOKUMEN', $request->JENIS_DOKUMEN);
            });
        }

        $query->where(function ($q) use ($request) {
            $q->whereHas('infoArsipSuratPindah', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            })->orWhereHas('infoArsipKk', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            })->orWhereHas('infoArsipSkot', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            })->orWhereHas('infoArsipSktt', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            })->orWhereHas('infoArsipKtp', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            });
        });

        $arsips = $query->get();

        // Jika arsip ditemukan
        if ($arsips->isNotEmpty()) {
            $formattedArsips = $arsips->map(function ($arsip) {
                $formattedArsip = [
                    'ID_ARSIP' => $arsip->ID_ARSIP,
                    'ID_DOKUMEN' => $arsip->ID_DOKUMEN,
                    'NAMA_DOKUMEN' => $arsip->jenisDokumen->NAMA_DOKUMEN ?? null,
                    'NO_DOKUMEN' => implode(', ', array_filter([
                        $arsip->NO_DOK_SURAT_PINDAH,
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
                ];

                $models = [
                    'infoArsipSuratPindah',
                    'infoArsipKk',
                    'infoArsipSkot',
                    'infoArsipSktt',
                    'infoArsipKtp'
                ];

                foreach ($models as $relation) {
                    if ($arsip->$relation) {
                        // Menggabungkan data ke dalam $formattedArsip tanpa membungkusnya dalam array
                        $formattedArsip['INFO_ARSIP'] = $arsip->$relation;
                        $id_kecamatan = $arsip->$relation->ID_KECAMATAN;
                        $namaKecamatan = Kecamatan::where('ID_KECAMATAN', $id_kecamatan)->value('NAMA_KECAMATAN');
                        $formattedArsip['INFO_ARSIP']->ID_KECAMATAN = $namaKecamatan;
                        $id_kelurahan = $arsip->$relation->ID_KELURAHAN;
                        $namakelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)->value('NAMA_KELURAHAN');
                        $formattedArsip['INFO_ARSIP']->ID_KELURAHAN = $namakelurahan;
                        break;
                    }
                }

                // Mendapatkan dokumen dari setiap tabel terkait
                $columnsMapping = [
                    'infoArsipSuratPindah' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_SKP_WNI', 'FILE_KTP_ASAL', 'FILE_NIKAH_CERAI', 'FILE_AKTA_KELAHIRAN', 'FILE_KK', 'FILE_F101', 'FILE_102', 'FILE_DOK_PENDUKUNG', 'FILE_LAINNYA', 'FILE_SURAT_PINDAH'],
                    'infoArsipKk' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_F101', 'FILE_NIKAH_CERAI', 'FILE_SK_PINDAH', 'FILE_SK_PINDAH_LUAR', 'FILE_SK_PENGGANTI', 'FILE_PUTUSAN_PRESIDEN', 'FILE_KK_LAMA', 'FILE_SK_PERISTIWA', 'FILE_SK_HILANG', 'FILE_KTP', 'FILE_LAINNYA', 'FILE_KK'],
                    'infoArsipSkot' => ['NAMA', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_SKOT'],
                    'infoArsipSktt' => ['NAMA', 'FILE_LAINNYA', 'FILE_SKTT'],
                    'infoArsipKtp' => ['NAMA', 'FILE_KK', 'FILE_KUTIPAN_KTP', 'FILE_SK_HILANG', 'FILE_AKTA_LAHIR', 'FILE_IJAZAH', 'FILE_SURAT_NIKAH_CERAI', 'FILE_SURAT_PINDAH', 'FILE_LAINNYA', 'FILE_KTP'],
                ];

                $formattedDokumen = [];
                foreach ($columnsMapping as $relation => $columns) {
                    foreach ($columns as $column) {
                        if (!empty($arsip->$relation->$column) && strpos($column, 'FILE_') !== false) {
                            $formattedDokumen[$column] = $arsip->$relation->$column;
                        }
                    }
                }

                $formattedArsip['DOKUMEN'] = $formattedDokumen;

                return $formattedArsip;
            });

            return response()->json([
                'success' => true,
                'message' => 'Sukses Menampilkan Rekapitulasi Kelurahan : '. $namaKelurahan,
                'arsips' => $formattedArsips
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

    public function filterBaseTahun (Request $request)
    {
            // Validasi input
        $validator = app('validator')->make($request->all(), [
            'JENIS_DOKUMEN' => 'nullable|exists:jenis_dokumen,ID_DOKUMEN',
            'TAHUN_PEMBUATAN' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $tahun = $request->TAHUN_PEMBUATAN;
        $query = Arsip::with('jenisDokumen')
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
            ]);

        if ($request->has('JENIS_DOKUMEN') && $request->JENIS_DOKUMEN != null) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('ID_DOKUMEN', $request->JENIS_DOKUMEN);
            });
        }

        if ($request->has('TAHUN_PEMBUATAN')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('infoArsipPengangkatan', function ($arsipquery) use ($request) {
                    $arsipquery->where('THN_PEMBUATAN_DOK_PENGANGKATAN', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipSuratPindah', function ($arsipquery) use ($request) {
                    $arsipquery->where('THN_PEMBUATAN_DOK_SURAT_PINDAH', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipPerceraian', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_DOK_PERCERAIAN', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipPengesahan', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_DOK_PENGESAHAN', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipKematian', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_DOK_KEMATIAN', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipKelahiran', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_DOK_KELAHIRAN', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipPengakuan', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_DOK_PENGAKUAN', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipPerkawinan', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_DOK_PERKAWINAN', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipKk', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_DOK_KK', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipSkot', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_DOK_SKOT', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipSktt', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_DOK_SKTT', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                })
                ->orWhereHas('infoArsipKtp', function ($arsipquery) use ($request) {
                    $arsipquery->where('TAHUN_PEMBUATAN_KTP', 'LIKE', '%' . $request->TAHUN_PEMBUATAN . '%');
                });
            });
        }

        $arsips = $query->get();

        // Jika arsip ditemukan
        if ($arsips->isNotEmpty()) {
            $formattedArsips = $arsips->map(function ($arsip) {
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
                        $id_kecamatan = $arsip->$relation->ID_KECAMATAN;
                        $namaKecamatan = Kecamatan::where('ID_KECAMATAN', $id_kecamatan)->value('NAMA_KECAMATAN');
                        $formattedArsip['INFO_ARSIP']->ID_KECAMATAN = $namaKecamatan;
                        $id_kelurahan = $arsip->$relation->ID_KELURAHAN;
                        $namakelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)->value('NAMA_KELURAHAN');
                        $formattedArsip['INFO_ARSIP']->ID_KELURAHAN = $namakelurahan;
                        break;
                    }
                }

                // Mendapatkan dokumen dari setiap tabel terkait
                $columnsMapping = [
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

                $formattedDokumen = [];
                foreach ($columnsMapping as $relation => $columns) {
                    foreach ($columns as $column) {
                        if (!empty($arsip->$relation->$column) && strpos($column, 'FILE_') !== false) {
                            $formattedDokumen[$column] = $arsip->$relation->$column;
                        }
                    }
                }

                $formattedArsip['DOKUMEN'] = $formattedDokumen;

                return $formattedArsip;
            });

            return response()->json([
                'success' => true,
                'message' => 'Sukses Menampilkan Rekapitulasi Tahun : '. $tahun,
                'arsips' => $formattedArsips
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

    public function filterBaseKelamin (Request $request)
    {
        // Validasi input
        $validator = app('validator')->make($request->all(), [
            'JENIS_DOKUMEN' => 'nullable|exists:jenis_dokumen,ID_DOKUMEN',
            'JENIS_KELAMIN' => 'nullable|',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $jenisKelamin = $request->JENIS_KELAMIN;
        $query = Arsip::with('jenisDokumen')
            ->with([
                'infoArsipPengangkatan',
                'infoArsipPengesahan',
                'infoArsipKematian',
                'infoArsipPengakuan',
                'infoArsipSkot',
                'infoArsipSktt',
                'infoArsipKtp'
            ]);

        if ($request->has('JENIS_DOKUMEN') && $request->JENIS_DOKUMEN != null) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('ID_DOKUMEN', $request->JENIS_DOKUMEN);
            });
        }

        $query->where(function ($q) use ($request) {
            $q->whereHas('infoArsipPengangkatan', function ($query) use ($request) {
                $query->where('JENIS_KELAMIN', $request->JENIS_KELAMIN);
            })->orWhereHas('infoArsipPengesahan', function ($query) use ($request) {
                $query->where('JENIS_KELAMIN', $request->JENIS_KELAMIN);
            })->orWhereHas('infoArsipSkot', function ($query) use ($request) {
                $query->where('JENIS_KELAMIN', $request->JENIS_KELAMIN);
            })->orWhereHas('infoArsipSktt', function ($query) use ($request) {
                $query->where('JENIS_KELAMIN', $request->JENIS_KELAMIN);
            })->orWhereHas('infoArsipKtp', function ($query) use ($request) {
                $query->where('JENIS_KELAMIN', $request->JENIS_KELAMIN);
            })->orWhereHas('infoArsipKematian', function ($query) use ($request) {
                $query->where('JENIS_KELAMIN', $request->JENIS_KELAMIN);
            })->orWhereHas('infoArsipPengakuan', function ($query) use ($request) {
                $query->where('JENIS_KELAMIN', $request->JENIS_KELAMIN);
            });
        });

        $arsips = $query->get();

        // Jika arsip ditemukan
        if ($arsips->isNotEmpty()) {
            $formattedArsips = $arsips->map(function ($arsip) {
                $formattedArsip = [
                    'ID_ARSIP' => $arsip->ID_ARSIP,
                    'ID_DOKUMEN' => $arsip->ID_DOKUMEN,
                    'NAMA_DOKUMEN' => $arsip->jenisDokumen->NAMA_DOKUMEN ?? null,
                    'NO_DOKUMEN' => implode(', ', array_filter([
                        $arsip->NO_DOK_PENGANGKATAN,
                        $arsip->NO_DOK_PENGESAHAN,
                        $arsip->NO_DOK_KEMATIAN,
                        $arsip->NO_DOK_PENGAKUAN,
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
                ];

                $models = [
                    'infoArsipPengangkatan',
                    'infoArsipPengesahan',
                    'infoArsipKematian',
                    'infoArsipPengakuan',
                    'infoArsipSkot',
                    'infoArsipSktt',
                    'infoArsipKtp'
                ];

                foreach ($models as $relation) {
                    if ($arsip->$relation) {
                        // Menggabungkan data ke dalam $formattedArsip tanpa membungkusnya dalam array
                        $formattedArsip['INFO_ARSIP'] = $arsip->$relation;
                        $id_kecamatan = $arsip->$relation->ID_KECAMATAN;
                        $namaKecamatan = Kecamatan::where('ID_KECAMATAN', $id_kecamatan)->value('NAMA_KECAMATAN');
                        $formattedArsip['INFO_ARSIP']->ID_KECAMATAN = $namaKecamatan;
                        $id_kelurahan = $arsip->$relation->ID_KELURAHAN;
                        $namakelurahan = Kelurahan::where('ID_KELURAHAN', $id_kelurahan)->value('NAMA_KELURAHAN');
                        $formattedArsip['INFO_ARSIP']->ID_KELURAHAN = $namakelurahan;
                        break;
                    }
                }

                // Mendapatkan dokumen dari setiap tabel terkait
                $columnsMapping = [
                    'infoArsipPengangkatan' => ['FILE_LAMA', 'FILE_LAINNYA', 'FILE_PENGANGKATAN'],

                    'infoArsipPengesahan' => ['FILE_LAMA', 'FILE_LAINNYA', 'FILE_PENGESAHAN'],
                    'infoArsipKematian' => ['FILE_LAMA', 'FILE_F201', 'FILE_SK_KEMATIAN', 'FILE_KK', 'FILE_KTP',
                                            'FILE_KTP_SUAMI_ISTRI', 'FILE_KUTIPAN_KEMATIAN', 'FILE_FC_PP', 'FILE_FC_DOK_PERJALANAN',
                                            'FILE_DOK_PENDUKUNG', 'FILE_SPTJM', 'FILE_LAINNYA', 'FILE_AKTA_KEMATIAN'],
                    'infoArsipPengakuan' => ['FILE_LAMA', 'FILE_LAINNYA', 'FILE_PENGAKUAN'],

                    'infoArsipSkot' => ['FILE_LAMA', 'FILE_LAINNYA', 'FILE_SKOT'],
                    'infoArsipSktt' => ['FILE_LAMA', 'FILE_LAINNYA', 'FILE_SKTT'],
                    'infoArsipKtp' => ['FILE_LAMA', 'FILE_KK', 'FILE_KUTIPAN_KTP', 'FILE_SK_HILANG', 'FILE_AKTA_LAHIR',
                                        'FILE_IJAZAH', 'FILE_SURAT_NIKAH_CERAI', 'FILE_SURAT_PINDAH', 'FILE_LAINNYA', 'FILE_KTP'],
                ];

                $formattedDokumen = [];
                foreach ($columnsMapping as $relation => $columns) {
                    foreach ($columns as $column) {
                        if (!empty($arsip->$relation->$column) && strpos($column, 'FILE_') !== false) {
                            $formattedDokumen[$column] = $arsip->$relation->$column;
                        }
                    }
                }

                $formattedArsip['DOKUMEN'] = $formattedDokumen;

                return $formattedArsip;
            });

            return response()->json([
                'success' => true,
                'message' => 'Sukses Menampilkan Rekapitulasi Jenis Kelamin '. $jenisKelamin,
                'arsips' => $formattedArsips
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

}


