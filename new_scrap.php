<?php

error_reporting(0);

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
            // CURLOPT_PROXYUSERPWD   => 'lum-customer-hl_fa848026-zone-daniel_sahlin_zone:0xwx5ytxlfcc',
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


    function getData($name = '', $address = '', $file_name, $key, $postal, $city)
    {

        if($key > 1 && ($key % 100) == 0)
            sleep(10);

        $original_name = $name = trim(preg_replace('/\s\s+/', ' ', $name));
        $original_address = $address = trim(preg_replace('/\s\s+/', ' ', $address));

        $original_input = $original_name . ' , ' . $original_address . ' , ' . $postal . ' , ' . $city;
        $original_input = trim($original_input);

        $name = str_replace(' ', '+', urlencode($name));
        $address = str_replace(' ', '+', urlencode($address));
        
        $url = 'https://www.merinfo.se/search?who='.$name.'&where='.$address;

        $result = get_web_page(trim($url));
        $html   = $result['content'];
        $dom    = str_get_html($html);
        
        $page_links   = [];
        $page_link    = '';
        $living_type  = '';

        if(gettype($dom) !== 'boolean'){

            // checking for data exist
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
                $name = $dom->find('.link-primary', 0)->plaintext;
                
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
            
            }

            else if(!$found){
                
                if(!handleFailedAddresses($dom, $html, $key, $original_input)){

                    $myfile = fopen('./uploads/'.$file_name.'.txt', "a") or die("Unable to open file!");
                    
                    $txt =  trim($original_name)     . "\t" .
                            trim($original_address)   . "\t".
                            trim($postal)   . "\t". 
                            trim($city);

                    fwrite($myfile, $txt);
                    fwrite($myfile, "\n");
                    fclose($myfile);
                }
                else{

                    // $myfile = fopen('./logs/sample_html.txt', "a") or die("Unable to open file!");
                    // $txt = $html;
                    // fwrite($myfile, $txt);
                    // fwrite($myfile, "\n");
                    // fclose($myfile);
                    
                }

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
                $txt =  trim($original_name)     . "\t" .
                        trim($original_address)     . "\t" . 
                        trim($postal)   . "\t". 
                        trim($city)     . "\t". 
                        trim($ssn)      . "\t". 
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
                createLog($key,$address,'Address not found', true);
                return false;
            }

        }

        $dom = new DomQuery($html);
        if($dom->find('h1') == '<h1 data-translate="turn_on_js" style="color:#bd2426;">Please turn JavaScript on and reload the page.</h1><h1><span data-translate="checking_browser">Checking your browser before accessing</span> merinfo.se.</h1>'){
            
            createLog($key,$address,'Javascript error');
            return true;

        }
        else if($dom->find('a') == '<a rel="noopener noreferrer" href="https://www.cloudflare.com/5xx-error-landing/" target="_blank">Cloudflare</a>'){

            createLog($key,$address,'Cloudflare error');
            return true;
            
        }
        else{

            createLog($key,$address,'Unknown Error');
            sleep(60);
            return true;

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
        

        // File 1

        $file_name = "final";
        $file = fopen('uploads/'.$file_name.'.txt', "w");
        fclose($file);

        // $file_addresses = fopen("source/input.txt", "r") or die("Unable to open file!");
        $file_addresses = fopen("source/input.txt", "r") or die("Unable to open file!");

        $addresses = [];

        $i = 0;
        while (($line = fgets($file_addresses)) !== false) {
            $input = explode(',', $line);
            getData($input[0], $input[1], $file_name, $i, $input[2], $input[3]);
            $i++;
        }


        // foreach(array_unique($addresses) as $key => $address){
        //     getData($address, $key, $file_name);
        // }

    }