<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProdCharacteristic extends Model
{
    protected $table = 'prod_characteristic';
    //protected $primaryKey = 'id';
    //public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['prod_id','characteristic_id','value'];
}
