<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mathstat extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mathStatId',
        'tryOutId',
        'bagian',
        'userId',
        'questId', 
        'result',
        'selected',
        'created_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'mathstat';
    protected $primaryKey = 'mathStatId'; 
    public $timestamps = true;
}