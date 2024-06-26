<?php

namespace App\Models;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Operator extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'operator';
    protected $primaryKey = 'ID_OPERATOR';
    public $timestamps = true;

    protected $fillable = [
        'NAMA_OPERATOR',
        'EMAIL'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'PASSWORD',
    ];

    public function HakAkses()
    {
        return $this->belongsTo(HakAkses::class, 'ID_AKSES');
    }

    public function Session()
    {
        return $this->hasMany(Session::class, 'ID_OPERATOR');
    }
}
