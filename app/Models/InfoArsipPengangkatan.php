<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipPengangkatan extends Model
{
    protected $table = 'info_arsip_pengangkatan';
    protected $primaryKey = 'NO_DOK_PENGANGKATAN';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'NAMA_ANAK',
        'NIK',
        'TANGGAL_LAHIR',
        'JENIS_KELAMIN',
        'NO_PP',
        'TANGGAL_PP',
        'NAMA_AYAH',
        'NAMA_IBU',
        'THN_PEMBUATAN_DOK_PENGANGKATAN',
        'FILE_LAMA',
        'FILE_LAINNYA',
        'FILE_PENGANGKATAN'
    ];

    public function arsip()
    {
        return $this->hasMany(Arsip::class);
    }
}
