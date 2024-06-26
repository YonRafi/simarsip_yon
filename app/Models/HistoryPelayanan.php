<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryPelayanan extends Model
{
    protected $table = 'history_pelayanan';
    protected $primaryKey = 'ID_HISTORY';
    public $timestamps = true;

    protected $fillable = [
        'ID_ARSIP',
        'KEGIATAN',
        'TANGGAL_PELAYAN'
    ];

    public function arsip()
    {
        return $this->hasOne(Arsip::class);
    }
}
