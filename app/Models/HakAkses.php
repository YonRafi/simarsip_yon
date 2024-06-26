<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HakAkses extends Model
{
    protected $table = 'hak_akses';
    protected $primaryKey = 'ID_AKSES';
    public $timestamps = true;

    protected $fillable = [
        'NAMA_AKSES'
    ];


    public function arsip ()
    {
        return $this->hasMany(Operator::class);
    }

    public function Operator ()
    {
        return $this->hasMany(Operator::class);
    }
}
