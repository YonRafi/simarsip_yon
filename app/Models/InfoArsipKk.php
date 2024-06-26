<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipKk extends Model
{
    protected $table = 'info_arsip_kk';
    protected $primaryKey = 'NO_DOK_KK';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'ID_KELURAHAN',
        'ID_KECAMATAN',
        'NAMA_KEPALA',
        'ALAMAT',
        'RT',
        'RW',
        'KODEPOS',
        'PROV',
        'KOTA',
        'TAHUN_PEMBUATAN_DOK_KK',
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

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'ID_ARSIP');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class, 'ID_KELURAHAN');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'ID_KECAMATAN');
    }
}
