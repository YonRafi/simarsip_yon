<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipKtp extends Model
{
    protected $table = 'info_arsip_ktp';
    protected $primaryKey = 'NO_DOK_KTP';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'ID_KELURAHAN',
        'ID_KECAMATAN',
        'NAMA',
        'JENIS_KELAMIN',
        'TEMPAT_LAHIR',
        'TANGGAL_LAHIR',
        'AGAMA',
        'STATUS_KAWIN',
        'KEBANGSAAN',
        'NO_PASPOR',
        'HUB_KELUARGA',
        'PEKERJAAN',
        'GOLDAR',
        'ALAMAT',
        'PROV',
        'KOTA',
        'TAHUN_PEMBUATAN_KTP',
        'FILE_LAMA',
        'FILE_KK',
        'FILE_KUTIPAN_KTP',
        'FILE_SK_HILANG',
        'FILE_AKTA_LAHIR',
        'FILE_IJAZAH',
        'FILE_SURAT_NIKAH_CERAI',
        'FILE_SURAT_PINDAH',
        'FILE_LAINNYA',
        'FILE_KTP'
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
