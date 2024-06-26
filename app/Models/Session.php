<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'session';
    protected $primaryKey = 'ID_SESSION';
    public $timestamps = true;

    protected $fillable = [
        'JWT_TOKEN',
        'ID_OPERATOR'
    ];

    public function Operator()
    {
        return $this->belongsTo(Operator::class, 'ID_OPERATOR');
    }
}
