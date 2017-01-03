<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kuota extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kode',
        'name',
        'operator',
        'hargaDasar', 
        'hargaJual',
        'margin',
        'isAvailable',
        'isPromo',
        'deskripsi',
        'gb3g',
        'gb4g',
        'days',
        'is24jam',
        'expired',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'quota';
    protected $primaryKey = 'kode';
    public $incrementing = false; 
    public $timestamps = false;
}
