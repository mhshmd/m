<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'activeDuration', 
        'syarat',
        'cekNomor',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'operator';
    protected $primaryKey = 'id'; 
    public $timestamps = false;
}
