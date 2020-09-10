<?php

namespace App\Http\Controllers;

use App\Characteristics;
use Illuminate\Http\Request;

class CharacteristicsController extends Controller
{
    public function getAll() {
        return response()->json(array_merge(['id','name'],array_values(self::getAllAsArray()),['price']));
    }
    public static function getAllAsArray() {
        $res=[];
        foreach (Characteristics::all() as $item) {
            $res[$item->id]=$item->name;
        }
        return $res;
    }
}
