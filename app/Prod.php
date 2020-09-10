<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Prod extends Model
{
    protected $table = 'prods';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['id','name','price'];
}
