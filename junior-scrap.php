<?php

error_reporting(E_ALL);

include_once('simple_html_dom.php');
require_once (__DIR__ . '/vendor/autoload.php');
use Rct567\DomQuery\DomQuery;
use HeadlessChromium\BrowserFactory;

    function get_web_page( $url )
    {
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(
    
            CURLOPT_CUSTOMREQUEST  => "GET",        //set request type post or get
            CURLOPT_POST           => false,        //set to GET
            CURLOPT_USERAGENT      => $user_agent, //set user agent
            CURLOPT_COOKIEFILE     => "cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR      => "cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            // CURLOPT_PROXY          => 'zproxy.lum-superproxy.io',
            // CURLOPT_PROXYPORT      => '22225',
            // CURLOPT_PROXYUSERPWD   => 'lum-customer-hl_fa848026-zone-daniel_sahlin_zone-country-se:0xwx5ytxlfcc',
            // CURLOPT_HTTPPROXYTUNNEL=> 1,
        );
        
        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }



    function getResidenceInfo( $uuid , $csrf )
    {
        $x_csrf = 'x-csrf-token:' . $csrf;

        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(
    
            CURLOPT_CUSTOMREQUEST  => "POST",        //set request type post or get
            CURLOPT_POST           => true,        //set to GET
            CURLOPT_USERAGENT      => $user_agent, //set user agent
            CURLOPT_COOKIEFILE     =>"cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR      =>"cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_PROXY          => 'zproxy.lum-superproxy.io',
            CURLOPT_PROXYPORT      => '22225',
            CURLOPT_PROXYUSERPWD   => 'lum-customer-hl_fa848026-zone-daniel_sahlin_zone:0xwx5ytxlfcc',
            CURLOPT_HTTPPROXYTUNNEL=> 1,
            CURLOPT_HTTPHEADER     => array(
                                        'origin: https://www.merinfo.se',
                                        $x_csrf,
                                        'Content-Type: application/json',
                                    ),

        );
        
        $ch = curl_init( 'https://www.merinfo.se/api/v1/person/residence' );
        curl_setopt_array( $ch, $options );

        $x_csrf = 'x-csrf-token:' . $csrf;

        $uuid_aaray['uuid'] = $uuid;
        $json_data = json_encode($uuid_aaray);


        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        // print_r($ch);die();

        $result = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $data = json_decode($result, true);

        if(is_array($data)){
            if(array_key_exists('data', $data))
                return $data['data']['description'];
            else
                return 'bostadsinformation';
        }
        else{
            return 'not exist';
        }
        
    }


    function headLessRequest($url){

        $browserCommand = 'google-chrome';

        $browserFactory = new BrowserFactory($browserCommand);
        $browser = $browserFactory->createBrowser([
                    'customFlags' => ['--no-sandbox'],
                ]);

        try {
            // creates a new page and navigate to an url
            $page = $browser->createPage();
            $page->navigate($url)->waitForNavigation();

            return $page->getHtml();
        }
        finally {
            $browser->close();
        }
    }

    function putTestHtml($html = '')
    {
        file_put_contents("uploads/html.txt", "");

        $myfile = fopen('./uploads/'.'html'.'.txt', "a") or die("Unable to open file!");
        $txt = $html;
        fwrite($myfile, $txt);
        fclose($myfile);
    }


    function getData($address,$key,$file_name)
    {

        if($key > 1 && ($key % 20) == 0)
            sleep(5);

        $original_input = $address = trim(preg_replace('/\s\s+/', ' ', $address));

        $address = str_replace(' ', '+', urlencode($address));
        
        $url = 'https://www.merinfo.se/search?who='.$address.'&where=';

        
        $result = get_web_page(trim($url));
        $html   = $result['content'];
        $dom    = str_get_html($html);
        
        $page_links   = [];
        $page_link    = '';
        $living_type  = '';

        if(gettype($dom) !== 'boolean'){

            $found = false;

            foreach($dom->find('.link-primary') as $element){

                $page_link = $element->href;
                
                if($page_link)
                    $found = true;

                break;

            }


            if($found){

                createLog($key,$original_input,$page_link,true);

                // name
                $f_name = $dom->find('.link-primary i', 0)->plaintext;
                $l_name = $dom->find('.link-primary', 0)->plaintext;
                

                if($f_name == ''){

                    $name_array = explode(" ", trim($l_name));

                    $f_name = $name_array[0];
                    $l_name = implode(" ",$name_array);
                
                }

                $l_name = str_replace($f_name, "", $l_name);

                // ssn
                $ssn = $dom->find('.col.pb-3.pl-3.pt-0.pr-0 p.my-1.mb-0', 0)->plaintext;
                
                // address
                $address_details = $dom->find('.col.pb-3.pl-3.pt-0.pr-0 .mb-0', 1)->plaintext;
                $pos = preg_split("/\r\n|\n|\r/", $address_details);
                $address = $pos[0] ?? '';
                $city_postal = $pos[1] ?? '';
                if($city_postal){
                    $city = preg_replace('/[0-9]+/', '', $city_postal);
                    $postal = filter_var($city_postal, FILTER_SANITIZE_NUMBER_INT);;
                }


                // Phone
                $phone = $dom->find('.phonenumber a', 0)->plaintext;

                // New requirment
                $gender = $dom->find('.float-md-right', 0);

                if (strpos($gender, 'kvinna') !== false)
                
                    $gender = 'kvinna';

                else if (strpos($gender, 'man') !== false)

                    $gender = 'man';

                else
                    
                    $gender = '';
                
            }

            else if(!$found){
                
                handleFailedAddresses($dom, $html, $key, $original_input);
                return;

            }

        }
        else{
            
            createLog($key,$original_input,'Proxy or Scraper not working');
            sleep(10);
            return;
        
        }

        // Store data
        
        if($page_link == ''){
            createLog($key,$original_input,'Link issue');

            // Enter not found data
        }
        else{

            if(1){
                $myfile = fopen('./uploads/'.$file_name.'.txt', "a") or die("Unable to open file!");
                $txt = trim($original_input)    . "\t" .
                       trim($f_name)            . "\t" . 
                       trim($l_name)            . "\t" . 
                       trim($ssn)               . "\t" . 
                       trim($address)           . "\t" . 
                       trim($postal)            . "\t" . 
                       trim($city)              . "\t" . 
                       trim($gender)            . "\t" . 
                       trim($phone);

                fwrite($myfile, $txt);
                fwrite($myfile, "\n");
                fclose($myfile);
            }

        }

    }

    function createLog($key,$address,$page_link, $address_found = false){
        
        $myfile = fopen('./logs/log.txt', "a") or die("Unable to open file!");

        $txt = $key . ' - ' . $address . ' - ' .  $page_link;

        fwrite($myfile, $txt);
        fwrite($myfile, "\n");
        fclose($myfile);

        // End Log

        if(!$address_found){

            $myfile  = fopen('./logs/failed.txt', "a") or die("Unable to open file!");

            fwrite($myfile, urldecode($address));
            fwrite($myfile, "\n");
            fclose($myfile);

            $myfile  = fopen('./logs/failed-log.txt', "a") or die("Unable to open file!");

            $address = $address . ',';
            fwrite($myfile, $address);
            fwrite($myfile, "\n");
            fclose($myfile);            

        }
    }

    function handleFailedAddresses($dom, $html, $key, $address){

        foreach($dom->find('.h2') as $element){
            
            if($element == '<h2 class="h2"> Ingen träff </h2>'){
                createLog($key,$address,'Address not found');
                return;
            }

        }

        $dom = new DomQuery($html);
        if($dom->find('h1') == '<h1 data-translate="turn_on_js" style="color:#bd2426;">Please turn JavaScript on and reload the page.</h1><h1><span data-translate="checking_browser">Checking your browser before accessing</span> merinfo.se.</h1>'){
            
            createLog($key,$address,'Javascript error');
            return;

        }
        else if($dom->find('a') == '<a rel="noopener noreferrer" href="https://www.cloudflare.com/5xx-error-landing/" target="_blank">Cloudflare</a>'){

            createLog($key,$address,'Cloudflare error');
            return;
            
        }
        else{

            createLog($key,$address,'Unknown Error');

        }

    }


    function runFailedNumbers($file_name){

        $failed_addresses = fopen("logs/failed.txt", "r") or die("Unable to open file!");

        $addresses = [];

        while (($line = fgets($failed_addresses)) !== false) {

            $addresses[] = $line;
            
        }

        // print_r($addresses);

        file_put_contents("logs/failed.txt", "");

        foreach(array_unique($addresses) as $key => $address){

            getData($address,$key,$file_name);

        }
    }


    if (1) {
        
        // Main File

        $file_name = "final";
        $file = fopen('uploads/'.$file_name.'.txt', "w");
        fclose($file);

        $file_addresses = fopen("source/input.txt", "r") or die("Unable to open file!");

        $addresses = [];

        while (($line = fgets($file_addresses)) !== false){
            if(trim($line))
                $addresses[] = $line;
        }
        
        foreach(array_unique($addresses) as $key => $address)
            getData($address, $key, $file_name);
        

    }