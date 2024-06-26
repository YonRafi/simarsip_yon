<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    protected $primaryKey = 'ID_KECAMATAN';
    public $timestamps = true;

    protected $fillable = [
        'NAMA_KECAMATAN'
    ];

    public function Kelurahan()
    {
        return $this->hasMany(Kelurahan::class,'ID_KELURAHAN');
    }

    public function InfoArsipSuratPindah()
    {
        return $this->hasMany(InfoArsipSuratPindah::class,'NO_DOK_SURAT_PINDAH');
    }

    public function InfoArsipKk()
    {
        return $this->hasMany(InfoArsipKk::class, 'NO_DOK_KK');
    }

    public function InfoArsipSkot()
    {
        return $this->hasMany(InfoArsipSkot::class, 'NO_DOK_SKOT');
    }

    public function InfoArsipSktt()
    {
        return $this->hasMany(InfoArsipSktt::class, 'NO_DOK_SKTT');
    }

    public function InfoArsipKtp()
    {
        return $this->hasMany(InfoArsipKtp::class, 'NO_DOK_KTP');
    }
}
