<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipPerkawinan extends Model
{
    protected $table = 'info_arsip_perkawinan';
    protected $primaryKey = 'NO_DOK_PERKAWINAN';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'NAMA_PRIA',
        'NAMA_WANITA',
        'TANGGAL_DOK_PERKAWINAN',
        'TEMPAT_KAWIN',
        'AGAMA_KAWIN',
        'AYAH_PRIA',
        'IBU_PRIA',
        'AYAH_WANITA',
        'IBU_WANITA',
        'TAHUN_PEMBUATAN_DOK_PERKAWINAN',
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

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'ID_ARSIP');
    }
}
