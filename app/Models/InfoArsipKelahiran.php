<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipKelahiran extends Model
{
    protected $table = 'info_arsip_kelahiran';
    protected $primaryKey = 'NO_DOK_KELAHIRAN';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'NAMA',
        'TEMPAT_LAHIR',
        'TANGGAL_LAHIR',
        'ANAK_KE',
        'NAMA_AYAH',
        'NAMA_IBU',
        'NO_KK',
        'TAHUN_PEMBUATAN_DOK_KELAHIRAN',
        'STATUS_KELAHIRAN',
        'STATUS_PENDUDUK',
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
        'FILE_AKTA_KELAHIRAN'
    ];

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'ID_ARSIP');
    }
}
