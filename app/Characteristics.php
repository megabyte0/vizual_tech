<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Characteristics extends Model
{
    protected $table = 'characteristics';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['name'];
}
