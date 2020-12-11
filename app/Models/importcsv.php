<?php
#namespace App;
namespace App\Models;
use Illuminate\Support\Facades\DB;
#use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class importcsv extends Model
{
    #use HasFactory;
     public static function insertData($data){

      $value=DB::table('importcsvs')->where('module_code', $data['module_code'])->get();
      if($value->count() == 0){
         DB::table('importcsvs')->insert($data);
      }
   }
}
