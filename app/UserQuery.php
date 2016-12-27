<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserQuery extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'sender',
        'activeTransaksiId',
        'level0',
        'level1',
        'level2',
        'level3',
        'level4',
        'backContent',
        'backStatus',
        'maxOption',
        'codes',
        'lastOperator',
        'codeSelected',
        'tujuan',
        'platform',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'userquery';
    protected $primaryKey = 'id'; 
    public $timestamps = true;
}
