<?php

error_reporting(1);

    function storeData($data)
    {        
        $myfile = fopen('./uploads/output.txt', "a") or die("Unable to open file!");
        $txt =  ($data);

        fwrite($myfile, $txt);
        fclose($myfile);
    
        return;
    }

    $file_addresses = fopen("source/input.txt", "r") or die("Unable to open file!");

    $addresses = [];

    $i = 0;

    while (($input = fgets($file_addresses)) !== false) {

        echo $i++ . '        ';
        
        $find = false;

        $source = fopen("source/source.txt", "r") or die("Unable to open file!");
        while (($line = fgets($source)) !== false) {
            
            if (strpos(trim($line), trim($input)) !== false){
                storeData($line);
                $find = true;
                break;
            }
        }

        if(!$find)
            storeData($input);   
    }
