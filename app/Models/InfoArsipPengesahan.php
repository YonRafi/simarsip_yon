<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipPengesahan extends Model
{
    protected $table = 'info_arsip_pengesahan';
    protected $primaryKey = 'NO_DOK_PENGESAHAN';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'NAMA_ANAK',
        'TANGGAL_LAHIR',
        'TEMPAT_LAHIR',
        'JENIS_KELAMIN',
        'NO_AKTA_KELAHIRAN',
        'NO_PP',
        'TANGGAL_PP',
        'NAMA_AYAH',
        'NAMA_IBU',
        'TAHUN_PEMBUATAN_DOK_PENGESAHAN',
        'FILE_LAMA',
        'FILE_LAINNYA',
        'FILE_PENGESAHAN'
    ];

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'ID_ARSIP');
    }
}
