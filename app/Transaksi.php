<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'kode',
        'harga',
        'hargaBayar', 
        'tujuan',
        'sender',
        'platform',
        'pmethod',
        'status',
        'confirmed',
        'batasPembayaran',
        'showMe',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $table = 'transaksi';
    protected $primaryKey = 'id'; 
    public $timestamps = true;
}
