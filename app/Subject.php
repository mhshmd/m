<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{

    /**Submaterial
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'SubjectId','subjectName', 
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'subject';
    protected $primaryKey = 'SubjectId'; 
    public $timestamps = false;
}
