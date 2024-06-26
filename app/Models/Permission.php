<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';
    protected $primaryKey = 'ID_PERMISSION';
    public $timestamps = true;

    protected $fillable = [
        'ID_OPERATOR',
        'ID_ARSIP',
        'STATUS'
    ];

    public function Operator()
    {
        return $this->belongsTo(Operator::class, 'ID_OPERATOR');
    }

    public function Arsip()
    {
        return $this->hasMany(Arsip::class, 'ID_ARSIP');
    }
}
