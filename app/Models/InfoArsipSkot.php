<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipSkot extends Model
{
    protected $table = 'info_arsip_skot';
    protected $primaryKey = 'NO_DOK_SKOT';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'ID_KECAMATAN',
        'ID_KELURAHAN',
        'NAMA',
        'NAMA_PANGGIL',
        'NIK',
        'JENIS_KELAMIN',
        'TEMPAT_LAHIR',
        'TANGGAL_LAHIR',
        'AGAMA',
        'STATUS_KAWIN',
        'PEKERJAAN',
        'ALAMAT_ASAL',
        'PROV_ASAL',
        'KOTA_ASAL',
        'KEC_ASAL',
        'KEL_ASAL',
        'ALAMAT',
        'PROV',
        'KOTA',
        'TAHUN_PEMBUATAN_DOK_SKOT',
        'FILE_LAMA',
        'FILE_LAINNYA',
        'FILE_SKOT'
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
