<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kelas',
        'tingkat',
        'pj',
        'hp',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'kelas';
    protected $primaryKey = 'kelas'; 
    public $incrementing = false; 
    public $timestamps = false;
}
