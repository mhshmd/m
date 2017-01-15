<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PreOrderData extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'sender',
        'name',
        'kelas',
        'pesanan',
        'totalHarga',
        'pmethod',
        'statusPembayaran',
        'statusPenerimaan',
        'showMe',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'preorder';
    protected $primaryKey = 'id'; 
    public $timestamps = true;
}
