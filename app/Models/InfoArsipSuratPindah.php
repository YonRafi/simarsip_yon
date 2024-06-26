<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipSuratPindah extends Model
{
    protected $table = 'info_arsip_surat_pindah';
    protected $primaryKey = 'NO_DOK_SURAT_PINDAH';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'ID_KELURAHAN',
        'ID_KECAMATAN',
        'NO_KK',
        'NAMA_KEPALA',
        'NIK_KEPALA',
        'ALASAN_PINDAH',
        'ALAMAT',
        'RT',
        'RW',
        'PROV',
        'KOTA',
        'KODEPOS',
        'ALAMAT_TUJUAN',
        'RT_TUJUAN',
        'RW_TUJUAN',
        'PROV_TUJUAN',
        'KOTA_TUJUAN',
        'KEC_TUJUAN',
        'KEL_TUJUAN',
        'KODEPOS_TUJUAN',
        'THN_PEMBUATAN_DOK_SURAT_PINDAH',
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

    public function arsip()
    {
        return $this->hasOne(Arsip::class);
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'ID_KECAMATAN');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'ID_KELURAHAN');
    }
}
