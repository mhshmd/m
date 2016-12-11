<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Submaterial extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'SubmaterialId','subjectId', 'subMaterialName',
        'model1', 'model2', 'model3',  
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'submaterial';
    protected $primaryKey = 'SubmaterialId'; 
    public $timestamps = false;
}
