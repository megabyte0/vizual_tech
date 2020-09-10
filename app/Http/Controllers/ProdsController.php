<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\ProdCharacteristic;
use Illuminate\Http\Request;

class ProdsController extends Controller
{
    public function getAll(Request $request) {
        $input = $request->all();
        $query = DB::table('prods');
        if (array_key_exists('name',$input)) {
            $query=$query->where('name','like','%'.($input['name']).'%');
        }
        $chars_id_name_map=CharacteristicsController::getAllAsArray();
        if (array_reduce(array_values($chars_id_name_map),function ($carry,$item) use ($input) {
            return $carry || array_key_exists($item,$input);
        })) {
            //$query=$query->join('prod_characteristic','prod_id','=','prod.id');
            //не уверен как сделать это без multiple inner joinов
            //а т.к. будет медленно, то лучше на чистом php
            $chars_name_value_to_prod_id=[];
            foreach (ProdCharacteristic::all() as $item) {
                if (!array_key_exists($chars_id_name_map[$item->characteristic_id],$chars_name_value_to_prod_id)) {
                    $chars_name_value_to_prod_id[$chars_id_name_map[$item->characteristic_id]]=[];
                }
                if (!array_key_exists($item->value,$chars_name_value_to_prod_id[$chars_id_name_map[$item->characteristic_id]])) {
                    $chars_name_value_to_prod_id[$chars_id_name_map[$item->characteristic_id]][$item->value]=[];
                }
                $chars_name_value_to_prod_id[$chars_id_name_map[$item->characteristic_id]][$item->value][]=$item->prod_id;
            }
            $prod_ids_intersect=[];
            foreach ($chars_id_name_map as $char_id => $char_name) {
                if (array_key_exists($char_name,$input)) {
                    if (array_key_exists($input[$char_name],$chars_name_value_to_prod_id[$char_name])) {
                        $prod_ids_intersect[]=$chars_name_value_to_prod_id[$char_name][$input[$char_name]];
                    } else {
                        $prod_ids_intersect[]=[];
                    }
                }
            }
            $query=$query->whereIn('id',
                count($prod_ids_intersect)>1?
                array_intersect(...$prod_ids_intersect):
                    $prod_ids_intersect);
        }
        //dd($query->toSql(),$query->getBindings());
        $res=[];
        foreach ($query->select(['prods.id as id','prods.name as name','price'])->get() as $item) {
            $item_as_array=[];
            foreach (['id','name','price'] as $field) {
                $item_as_array[$field]=$item->{$field};
            }
            $res[$item->id]=$item_as_array;
        }

        //сорри нет времени думать как это сделать в ларе
        $prod_chars=ProdCharacteristic::whereIn('prod_id',array_keys($res))->get();
        foreach ($prod_chars as $item) {
            $res[$item->prod_id][$chars_id_name_map[$item->characteristic_id]]=$item->value;
        }
        return response()->json(array_values($res));
    }
}
