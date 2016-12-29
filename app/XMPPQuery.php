<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class XMPPQuery extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'query',
        'result',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'xmppquery';
    protected $primaryKey = 'id'; 
    public $timestamps = true;
}
