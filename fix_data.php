<?php

	error_reporting(1);

    function storeData($data)
    {        
        $myfile = fopen('./uploads/fixed_output.txt', "a") or die("Unable to open file!");
        $txt =  ($data);

        fwrite($myfile, $txt);
        fwrite($myfile, "\n");
        fclose($myfile);
    
        return;
    }

    // Actual File
    $actual_file = [];
    $file = fopen("new_task/actual_data.txt", "r") or die("Unable to open file 3");
        while (($input = fgets($file)) !== false) {
            $actual_file[] = $input;
    }    

    // Data File
    $datafile = [];
    
    $file = fopen("new_task/email_data.txt", "r") or die("Unable to open file 2");
    
    while (($input = fgets($file)) !== false) {
    	
    	$datafile[] = $input;
    
    }


    foreach ($datafile as $key => $value) {

    	$single_data_row = explode(',' , $value);

	    foreach ($single_data_row as $val) {

	    	// print_r($actual_file[$key]);
	        storeData(trim($actual_file[$key]) . "\t". trim($val));

	    }

	    // if($key == 5)
	    	// die();

    }
            
    