<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestIntro extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'questIntroId',
        'text',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'questintro';
    protected $primaryKey = 'questIntroId'; 
    public $timestamps = false;
}