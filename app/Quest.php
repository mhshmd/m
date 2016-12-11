<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'questId',
        'subMaterialId',
        'subjectId', 
        'text',
        'a','b','c','d','e',
        'answer',
        'forWhat',
        'tryOutId',
        'latihanId',
        'questLevel',
        'qPictPath',
        'questIntroId',
        'pictPathA',
        'pictPathB',
        'pictPathC',
        'pictPathD',
        'pictPathE',
        'selected',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'quest';
    protected $primaryKey = 'questId'; 
    public $timestamps = false;
}
