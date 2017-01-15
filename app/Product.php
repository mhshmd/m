<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kode',
        'name',
        'hargaDasar',
        'hargaJual',
        'category',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'product';
    protected $primaryKey = 'kode';  
    public $incrementing = false; 
    public $timestamps = false;
}
