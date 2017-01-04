<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserQuery extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'sender',
        'activeTransaksiId',
        'commandArray',
        'saved',
        'tujuan',
        'platform',
        'currentKuotaList',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'userquery';
    protected $primaryKey = 'id'; 
    public $timestamps = true;
}
