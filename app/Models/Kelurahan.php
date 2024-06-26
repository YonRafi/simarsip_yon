<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    protected $table = 'kelurahan';
    protected $primaryKey = 'ID_KELURAHAN';
    public $timestamps = true;

    protected $fillable = [
        'ID_KECAMATAN',
        'NAMA_KELURAHAN'
    ];

    public function Kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'ID_KECAMATAN');
    }

    public function InfoArsipSuratPindah()
    {
        return $this->hasMany(InfoArsipSuratPindah::class, 'NO_DOK_SURAT_PINDAH');
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
