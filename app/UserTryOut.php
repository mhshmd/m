<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTryOut extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userTOId',
        'tryOutId',
        'userId',
        'bagian',
        'status',
        'created_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'usertryout';
    protected $primaryKey = 'userTOId'; 
    public $timestamps = true;
}