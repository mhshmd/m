<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userId',
        'password', 
        'name',
        'status',
        'activated',
        'rank',
        'point',
        'uPictPath',
        'tanggal',
        'bulan',
        'tahun',
        'email',
        'provinceId',
        'lastActive',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'user';
    protected $primaryKey = 'userId'; 
    public $timestamps = false;
}
