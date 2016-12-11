<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Engstat extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'engStatId',
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
    protected $table = 'engstat';
    protected $primaryKey = 'engStatId'; 
    public $timestamps = true;
}