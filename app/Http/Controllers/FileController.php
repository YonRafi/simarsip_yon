<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    public function getDokumen(Request $request)
    {
        $validator = app('validator')->make($request->all(), [
            'TAHUN_PEMBUATAN' => 'nullable|integer',
            'JENIS_DOKUMEN' => 'nullable|exists:jenis_dokumen,ID_DOKUMEN',
            'NO_DOKUMEN' => 'nullable|string',
            'NAMA_FILE' => 'nullable|string',
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

        if ($request->has('NAMA_FILE')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('infoArsipPengangkatan', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_PENGANGKATAN', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipSuratPindah', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SKP_WNI', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KTP_ASAL', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_NIKAH_CERAI', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_AKTA_KELAHIRAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KK', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_F101', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_F102', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_F103', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_DOK_PENDUKUNG', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SURAT_PINDAH', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipPerceraian', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_F201', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_FC_PP', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KUTIPAN_PERKAWINAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KTP', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KK', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SPTJM', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_AKTA_PERCERAIAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_AKTA_PERKAWINAN', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipPengesahan', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_PENGESAHAN', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipKematian', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_F201', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SK_KEMATIAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KK', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KTP', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KTP_SUAMI_ISTRI', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KUTIPAN_KEMATIAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_FC_PP', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_FC_DOK_PERJALANAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_DOK_PENDUKUNG', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SPTJM', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_AKTA_KEMATIAN', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipKelahiran', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KK', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KTP_AYAH', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KTP_IBU', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_F102', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_F201', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_BUKU_NIKAH', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KUTIPAN_KELAHIRAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SURAT_KELAHIRAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SPTJM_PENERBITAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_PELAPORAN_KELAHIRAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_AKTA_KELAHIRAN', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipPengakuan', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_PENGAKUAN', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipPerkawinan', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_F201', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_FC_SK_KAWIN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_FC_PASFOTO', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KTP', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KK', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_AKTA_KEMATIAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_AKTA_PERCERAIAN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SPTJM', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_AKTA_PERKAWINAN', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipKk', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_F101', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_NIKAH_CERAI', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SK_PINDAH', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SK_PINDAH_LUAR', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SK_PENGGANTI', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_PUTUSAN_PRESIDEN', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KK_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SK_PERISTIWA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SK_HILANG', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KTP', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KK', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipSkot', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SKOT', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipSktt', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SKTT', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                })
                ->orWhereHas('infoArsipKtp', function ($arsipquery) use ($request) {
                    $arsipquery->where(function ($q) use ($request) {
                        $q->where('FILE_LAMA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KK', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KUTIPAN_KTP', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SK_HILANG', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_AKTA_LAHIR', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_IJAZAH', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SURAT_NIKAH_CERAI', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_SURAT_PINDAH', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_LAINNYA', 'LIKE', '%' . $request->NAMA_FILE . '%')
                          ->orWhere('FILE_KTP', 'LIKE', '%' . $request->NAMA_FILE . '%');
                    });
                });
            });
        }
        $arsip = $query->first();
        if (!$arsip) {
            return response()->json(['error' => 'Data not found.'], 404);
        }

        $pathtahun = $request->TAHUN_PEMBUATAN;

        $pathdokumen = '';
        if ($arsip->infoArsipPengangkatan) {
            $pathdokumen = 'Arsip Pengangkatan';
        } elseif ($arsip->infoArsipSuratPindah) {
            $pathdokumen = 'Arsip Surat Pindah';
        } elseif ($arsip->infoArsipPerceraian) {
            $pathdokumen = 'Arsip Perceraian';
        } elseif ($arsip->infoArsipPengesahan) {
            $pathdokumen = 'Arsip Pengesahan';
        } elseif ($arsip->infoArsipKematian) {
            $pathdokumen = 'Arsip Kematian';
        } elseif ($arsip->infoArsipKelahiran) {
            $pathdokumen = 'Arsip Kelahiran';
        } elseif ($arsip->infoArsipPengakuan) {
            $pathdokumen = 'Arsip Pengakuan';
        } elseif ($arsip->infoArsipPerkawinan) {
            $pathdokumen = 'Arsip Perkawinan';
        } elseif ($arsip->infoArsipKk) {
            $pathdokumen = 'Arsip Kk';
        } elseif ($arsip->infoArsipSkot) {
            $pathdokumen = 'Arsip Skot';
        } elseif ($arsip->infoArsipSktt) {
            $pathdokumen = 'Arsip Sktt';
        } elseif ($arsip->infoArsipKtp) {
            $pathdokumen = 'Arsip Ktp';
        }

        $folderPath = 'public/'. $pathtahun .'/'. $pathdokumen;
        $fileName = $request->NAMA_FILE;
        $filePath = $folderPath . '/' . $fileName;

        // Memeriksa apakah file ada di storage
        if (Storage::exists($filePath)) {
            return response()->download(storage_path('app/' . $filePath), $fileName, [
                'Content-Type' => 'application/pdf',
            ]);
        } else {
            return response()->json(['error' => 'File not found.'], 404);
        }
    }
}
