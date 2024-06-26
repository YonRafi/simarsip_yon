<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoArsipPengakuan extends Model
{
    protected $table = 'info_arsip_pengakuan';
    protected $primaryKey = 'NO_DOK_PENGAKUAN';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'NAMA_ANAK',
        'TANGGAL_LAHIR',
        'TEMPAT_LAHIR',
        'JENIS_KELAMIN',
        'NO_PP',
        'TANGGAL_PP',
        'NO_AKTA_KELAHIRAN',
        'NAMA_AYAH',
        'NAMA_IBU',
        'TAHUN_PEMBUATAN_DOK_PENGAKUAN',
        'FILE_LAMA',
        'FILE_LAINNYA',
        'FILE_PENGAKUAN'
    ];

    public function arsip()
    {
        return $this->hasMany(Arsip::class, 'ID_ARSIP');
    }
}
