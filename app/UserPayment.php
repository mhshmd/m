<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPayment extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'paymentId',
        'email',
        'price',
        'sand',
        'phone',
        'pesan',
        'paid',
        'lifetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'userpayment';
    protected $primaryKey = 'paymentId'; 
    public $timestamps = true;
}