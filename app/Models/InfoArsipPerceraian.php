<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipPerceraian extends Model
{
    protected $table = 'info_arsip_perceraian';
    protected $primaryKey = 'NO_DOK_PERCERAIAN';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'NAMA_PRIA',
        'NAMA_WANITA',
        'ALAMAT_PRIA',
        'ALAMAT_WANITA',
        'NO_PP',
        'TANGGAL_PP',
        'DOMISILI_CERAI',
        'NO_PERKAWINAN',
        'TANGGAL_DOK_PERKAWINAN',
        'TAHUN_PEMBUATAN_DOK_PERCERAIAN',
        'FILE_LAMA',
        'FILE_F201',
        'FILE_FC_PP',
        'FILE_AKTA_PERKAWINAN',
        'FILE_KTP',
        'FILE_KK',
        'FILE_SPTJM',
        'FILE_LAINNYA',
        'FILE_AKTA_PERCERAIAN',
        'FILE_AKTA_PERKAWINAN'
    ];

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'ID_ARSIP');
    }
}
