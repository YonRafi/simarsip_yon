<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipSktt extends Model
{
    protected $table = 'info_arsip_sktt';
    protected $primaryKey = 'NO_DOK_SKTT';
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
        'TAHUN_PEMBUATAN_DOK_SKTT',
        'FILE_LAMA',
        'FILE_LAINNYA',
        'FILE_SKTT'
    ];

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'ID_ARSIP');
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
