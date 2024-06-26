<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class infoArsipKematian extends Model
{
    protected $table = 'info_arsip_kematian';
    protected $primaryKey = 'NO_DOK_KEMATIAN';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'NAMA',
        'NIK',
        'TEMPAT_LAHIR',
        'TANGGAL_LAHIR',
        'TANGGAL_MATI',
        'TEMPAT_MATI',
        'ALAMAT',
        'JENIS_KELAMIN',
        'AGAMA',
        'TANGGAL_LAPOR',
        'TAHUN_PEMBUATAN_DOK_KEMATIAN',
        'FILE_LAMA',
        'FILE_F201',
        'FILE_SK_KEMATIAN',
        'FILE_KK',
        'FILE_KTP',
        'FILE_KTP_SUAMI_ISTRI',
        'FILE_AKTA_KEMATIAN',
        'FILE_FC_PP',
        'FILE_FC_DOK_PERJALANAN',
        'FILE_DOK_PENDUKUNG',
        'FILE_SPTJM',
        'FILE_LAINNYA',
        'FILE_AKTA_KEMATIAN'
    ];

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'ID_ARSIP');
    }
}
