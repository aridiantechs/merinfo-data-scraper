<?php

error_reporting(1);

    function storeData($data)
    {        
        $myfile = fopen('./uploads/matched_output.txt', "a") or die("Unable to open file!");
        $txt =  ($data);

        fwrite($myfile, $txt);
        fclose($myfile);
    
        return;
    }


        
    $file_name = "matched_output";
    

    // File 1
    $mainfile = [];
    $file = fopen("source/filemain.txt", "r") or die("Unable to open file 1");
        while (($input = fgets($file)) !== false) {
            $mainfile[] = $input;
    }


    // File 2
    $datafile = [];
    $file = fopen("source/filesource.txt", "r") or die("Unable to open file 2");
        while (($input = fgets($file)) !== false) {
            $datafile[] = $input;
    }


    // File 3
    $actual_file = [];
    $file = fopen("source/actual_file.txt", "r") or die("Unable to open file 3");
        while (($input = fgets($file)) !== false) {
            $actual_file[] = $input;
    }

    // print_r($actual_file);die();       

    $changes = 0;
    foreach ($mainfile as $key => $value) {

        // if($key == 50)
            // die();

        if(trim($value) == trim($datafile[$key])){
            
            storeData(trim($actual_file[$key]) . "\t". $value);
            
            continue;
        }
        else{
            
            $changes++;
            
            storeData(trim($actual_file[$key]) . "\t" . $datafile[$key]);
        }
    }

echo $changes;
    // if (strpos(trim($line), trim($input_word)) !== false){
