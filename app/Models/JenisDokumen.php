<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisDokumen extends Model
{
    protected $table = 'jenis_dokumen';
    protected $primaryKey = 'ID_DOKUMEN';
    public $timestamps = true;

    protected $fillable = [
        'NAMA_DOKUMEN'
    ];

    public function arsip()
    {
        return $this->hasMany(Arsip::class);
    }
}
