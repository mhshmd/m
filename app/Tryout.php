<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tryout extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tryOutId',
        'name',
        'subjectId',
        'type',
        'startDate',
        'endDate',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'tryout';
    protected $primaryKey = 'tryOutId'; 
    public $timestamps = false;
}