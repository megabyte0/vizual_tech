<?php

namespace App\Console\Commands;

use App\Characteristics;
use App\Prod;
use App\ProdCharacteristic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:json {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import json to db';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        //echo getcwd(),"\n";
        //TODO:check if file exists
        $contents=file_get_contents($this->argument('filename'));
        $json=json_decode($contents,true);
        //var_dump($json);
        $this->dbAddProds($json);//TODO:update
        $char_name_id_map=$this->dbAddChars($json);
        //var_dump($char_name_id_map);
        $this->dbAddProdsChars($json,$char_name_id_map);
        return 0;
    }

    private function dbAddProds($json) {
        $already_in_db = Prod::get();
        $assoc_id_map = [];
        foreach ($already_in_db as $item) {
            $assoc_id_map[$item->id] = $item;
        }
        $missing = [];
        foreach ($json as $item) {
            if ((!array_key_exists($item['id'], $assoc_id_map)) &&
                (!array_key_exists($item['id'], $missing))) {
                $new_item = [];
                foreach (['id', 'name', 'price'] as $field) {
                    $new_item[$field] = $item[$field];
                }
                $missing[] = $new_item;
            }
        }
        // https://stackoverflow.com/a/48886957
        DB::table('prods')->insert($missing);
    }

    private function get_assoc_map($db,$fkey,$fvalue) {
        $assoc_map = [];
        foreach ($db as $item) {
            $assoc_map[$fkey($item)] = $fvalue($item);
        }
        return $assoc_map;
    }

    private function dbAddChars($json) {
        $assoc_name_map=$this->get_assoc_map(Characteristics::get(),
            function ($item) {return $item->name;},
            function ($item) {return $item->id;});
        $missing = [];
        $to_insert = [];
        foreach ($json as $item) {
            if (array_key_exists('characteristics', $item)) {
                foreach ($item['characteristics'] as $key => $value) {
                    if ((!array_key_exists($key, $assoc_name_map)) &&
                        (!array_key_exists($key, $missing))) {
                        $missing[$key] = NULL;
                        $to_insert[] = ['name' => $key];
                    }
                }
            }
        }
        DB::table('characteristics')->insert($to_insert);
        return $this->get_assoc_map(Characteristics::get(),
            function ($item) {return $item->name;},
            function ($item) {return $item->id;});
    }

    private function dbAddProdsChars($json,$char_name_id_map) {
        $already_in_db=[];
        foreach (ProdCharacteristic::get() as $item) {
            if (!array_key_exists($item->prod_id,$already_in_db)) {
                $already_in_db[$item->prod_id]=[];
            }
            if (!array_key_exists($item->characteristic_id,$already_in_db[$item->prod_id])) {
                $already_in_db[$item->prod_id][$item->characteristic_id]=$item;
            }
        }
        $to_delete=[];
        foreach ($already_in_db as $prod_id=>$prod) { //array copy
            if (!array_key_exists($prod_id,$to_delete)){
                $to_delete[$prod_id]=[];
            }
            foreach ($prod as $char_id=>$item) {
                $to_delete[$prod_id][$char_id]=$item;
            }
        }
        $to_insert=[];
        $to_update=[];
        foreach ($json as $item) {
            if (array_key_exists('characteristics', $item)) {
                foreach ($item['characteristics'] as $key => $value) {
                    if ((!array_key_exists($item['id'],$already_in_db))||
                        (!array_key_exists($char_name_id_map[$key],$already_in_db[$item['id']]))) {
                        $to_insert[]=['prod_id'=>$item['id'],
                            'characteristic_id'=>$char_name_id_map[$key],
                            'value'=>$value];
                        $to_delete[$item['id']][$char_name_id_map[$key]]=NULL;
                    } elseif ($already_in_db[$item['id']][$char_name_id_map[$key]]->value!==$value) {
                        $to_update[]=['item'=>$item,'value'=>$value];
                        $to_delete[$item['id']][$char_name_id_map[$key]]=NULL;
                    }
                }
            }
        }
        //method appears rather lengthy
        DB::table('prod_characteristic')->insert($to_insert);
        foreach ($to_update as $item) {
            $item['item']->value=$item['value'];
            $item['item']->save();
        }
        foreach ($to_delete as $prod_id=>$prod) {
            foreach ($prod as $char_id=>$item) {
                if ($item!==NULL) {
                    $item->delete();
                }
            }
        }
    }
}
