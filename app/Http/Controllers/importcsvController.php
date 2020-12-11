<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\importcsv;
use App\Mail\importcsvMail;
use Illuminate\Support\Facades\Mail;
use Validator;
use \App\Http\Requests\CreatecsvRequest;
class importcsvController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $CreatecsvRequest)
    { 
      
     // File upload location
      $location = 'uploads';$filename='MOCK_DATA.csv';
      $path = public_path($location.'/'.$filename);
      $file = pathinfo($path);
   
      // File Details 
      $filename =  $file['basename'];
      $extension = $file['extension'];      

      // Valid File Extensions
      $valid_extension = array("csv");

      // 2MB in Bytes
      $maxFileSize = 2097152; 
      $importDatahead_arr = array();
      $importData_arr = array();
      $msg=array();
      $data=array();  
      // Check file extension
      if(in_array(strtolower($extension),$valid_extension)){        

          // File upload location
          $location = 'uploads';          

          // Import CSV to Database
          $filepath = public_path($location."/".$filename);

          // Reading file
          $file = fopen($filepath,"r");
          
          $i = 0;
          
          while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
             $num = count($filedata);//env('CSV_COLUMN_NUM');
             $error_hg=0;
             // Skip first row (Remove below comment if you want to skip the first row)
             if($i == 0){ 
                for ($c=0; $c < $num; $c++) {
                  if($filedata [$c]=='' && $c==0){ $error_hg=1;
                        $msg['heading'][]='Header column ('.env('FIRST_COLUMN').' at 1st column) is missing in csv file';
                  }else if($filedata [$c]!=env('FIRST_COLUMN')  && $c==0){ $error_hg=1;
                        $msg['heading'][]='Header column ('.$filedata [$c].' at 1st column) is incorrect in csv file';
                  }
                  if($filedata [$c]==''  && $c==1){ $error_hg=1;
                        $msg['heading'][]='Header column ('.env('SECOND_COLUMN').' at 2nd column) is missing in csv file';
                  }else if($filedata [$c]!=env('SECOND_COLUMN')  && $c==1){ $error_hg=1;
                        $msg['heading'][]='Header column ('.$filedata [$c].' at 2nd column) is incorrect in csv file';
                  }
                  if($filedata [$c]==''  && $c==2){ $error_hg=1;
                        $msg['heading'][]='Header column ('.env('THIRD_COLUMN').' at 3rd column) is missing in csv file';
                  }else if($filedata [$c]!=env('THIRD_COLUMN')  && $c==2){ $error_hg=1;
                        $msg['heading'][]='Header column ('.$filedata [$c].' at 3rd column) is incorrect in csv file';
                  }                  
                }
                $i++;
                continue; 
             }
                $book = array();
                list(
                  $book['module_code'], 
                  $book['module_name'], 
                  $book['module_term']                  
                ) = $filedata;            
                $csv_errors = Validator::make(
                  $book, 
                  (new CreatecsvRequest)->rules()
                )->errors();
                 
             $rw=$i+1;
             for ($c=0; $c < $num; $c++) {                 
                
                $error=0;
                if($filedata [$c]=='' && $c==0){ $error=1;
                        $msg[env('FIRST_COLUMN')]['txt']=env('FIRST_COLUMN').' is missing at ';
                        $msg[env('FIRST_COLUMN')]['missarr'][]='row '.$rw;
                        //echo env('FIRST_COLUMN');echo "<br>";
                }
                if($filedata [$c]=='' && $c==1){ $error=1;
                        $msg[env('SECOND_COLUMN')]['txt']=env('SECOND_COLUMN').' is missing at ';
                        $msg[env('SECOND_COLUMN')]['missarr'][]='row '.$rw;
                        //echo env('SECOND_COLUMN');echo "<br>";
                }
                if($filedata [$c]=='' && $c==2){ $error=1;
                        $msg[env('THIRD_COLUMN')]['txt']=env('THIRD_COLUMN').' is missing at ';
                        $msg[env('THIRD_COLUMN')]['missarr'][]='row '.$rw;
                        //echo env('THIRD_COLUMN');echo "<br>";
                }
                //echo $error_hg; echo $error;
                if($error_hg==0 && $error==0){
                    $importData_arr[$i][] = $filedata [$c];
                }
             }
             $i++;
          }
          fclose($file);
          
           
           if(!empty($msg[env('FIRST_COLUMN')]['missarr'])){
                $yourArray=$msg[env('FIRST_COLUMN')]['missarr'];
                $lastItem = array_pop($yourArray); // c
                $text = implode(', ', $yourArray); // a, b
                $text .= ' and '.$lastItem; // a, b and c
                $msg[env('FIRST_COLUMN')]['txt'] .=$text;
            }
            if(!empty($msg[env('SECOND_COLUMN')]['missarr'])){
                $yourArray=$msg[env('SECOND_COLUMN')]['missarr'];
                $lastItem = array_pop($yourArray); // c
                $text = implode(', ', $yourArray); // a, b
                $text .= ' and '.$lastItem; // a, b and c
                $msg[env('SECOND_COLUMN')]['txt'] .=$text;
            }
            if(!empty($msg[env('THIRD_COLUMN')]['missarr'])){
                $yourArray=$msg[env('THIRD_COLUMN')]['missarr'];
                $lastItem = array_pop($yourArray); // c
                $text = implode(', ', $yourArray); // a, b
                $text .= ' and '.$lastItem; // a, b and c
                $msg[env('THIRD_COLUMN')]['txt'] .=$text;
            }
            //print_r($msg);
            if(!empty($msg['heading'])){
                foreach($msg['heading'] as $msg_val){
                    $data[]=$msg_val;
                }
            }
            if(!empty($msg[env('FIRST_COLUMN')])){
                $data[]=$msg[env('FIRST_COLUMN')]['txt'];
            }
            if(!empty($msg[env('SECOND_COLUMN')])){
                $data[]=$msg[env('SECOND_COLUMN')]['txt'];
            }
            if(!empty($msg[env('THIRD_COLUMN')])){
                $data[]=$msg[env('THIRD_COLUMN')]['txt'];
            }


            if ($csv_errors->any()){
                foreach ($csv_errors->import->all() as $message){
                  $data[]=$message;
                }                            
            }

           
           
          $i = 0;
          
          // Insert to MySQL database
          foreach($importData_arr as $importData){
                 
            $insertData = array(
               "module_code"=>$importData[0],
               "module_name"=>$importData[1],
               "module_term"=>$importData[2]);
            importcsv::insertData($insertData);

          }       
        
        
      }else{
        $data[]='Invalid File Extension';
        $csv_errors->add('extension', "Invalid File Extension");        
      }
      
      Mail::to(env('TO_EMAIL'))->send(new importcsvMail($data));
        
    }

    

    


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
