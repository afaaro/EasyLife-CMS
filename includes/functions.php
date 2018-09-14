<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

function list_tables() {
    global $db;

    $result = $db->query("SHOW TABLES");
    $tables = array();
    foreach($result->rows as $row) {
        foreach ($row as $key => $table) {
            $tables[] = $table;
        }
    }
    return $tables;
}

// Generate Clean Url
function clean_url($string = null, $length = 65, $separator = "-") {
    if (is_null($string)) {
        return "";
    }

    // Remove spaces from the beginning and from the end of the string
    $string = trim($string);

    // Lower case everything 
    // using mb_strtolower() function is important for non-Latin UTF-8 string | more info: http://goo.gl/QL2tzK
    $string = mb_strtolower($string, "UTF-8");
    
     // kill entities
    $string = preg_replace('/&.+?;/', '', $string);

    // Make alphanumeric (removes all other characters)
    // this makes the string safe especially when used as a part of a URL
    // this keeps latin characters and arabic charactrs as well
    $string = preg_replace("/[^a-z0-9_\s-ءاأإآؤئبتثجحخدذرزسشصضطظعغفقكلمنهويةى]/u", "", $string);

    // Remove multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", $separator, $string);

    // Convert whitespaces and underscore to the given separator
    $string = preg_replace("/[\s_]/", $separator, $string);

    // cut off to maximum length
    if ($length > -1 && strlen($string) > $length) {
        $string = substr($string, 0, $length);
    }

    return $string;
}

function dbquery_insert($table, $values=false, $method, $where=false){
    global $db, $config;

    if (is_array($where)) {
        $redirect = (array_key_exists("noredirect", $where)) ? "0":"1";
    } else {
        $redirect = "1";
    }
    if($method == "save"){
        $i = 1;
        $statement = '';
        foreach($values as $a=>$b) {
            $statement .= (count($values) == $i) ? " $a = '$b' ": " $a = '$b', ";
            $i++;
        }
        $result = $db->query("INSERT INTO $table SET $statement");
        return $db->insert_id();
    }elseif($method == "update"){
        $i = 1;
        $statement = '';
        foreach($values as $a=>$b) {
            $statement .= (count($values) == $i) ? " $a = '$b' ": " $a = '$b', ";
            $i++;
        }
        $inputdata = (preg_match('/WHERE/i', $where)) ? "WHERE $where" : $where;
        $result = $db->query("UPDATE $table SET $statement WHERE $inputdata");
        return $db->Affected_Rows();
    }elseif($method == "delete"){
        $inputdata = (preg_match('/WHERE/i', $where)) ? "WHERE $where" : $where;
        $result = $db->query("DELETE FROM $table WHERE $inputdata");
    }
}

/**
* Handles everything going in ClipBucket development mode
* @param : { string } { $query } { MySQL query for which to run process }
* @param : { string } { $query_type } { type of query, select, delete etc }
* @param : { integer } { $time } { time took to execute query }
* @clipbucket function
* @return : { array } { $__devmsgs } { array with all debugging data }
* @since : 27th May, 2016
* @author : Saqib Razzaq
*/

function devWitch($query, $query_type, $time) {
    global $__devmsgs;
    $memoryBefore = $__devmsgs['total_memory_used'];
    $memoryNow = memory_get_usage()/1048576;
    $memoryDif = $memoryNow - $memoryBefore;
    $__devmsgs[$query_type.'_queries'][$__devmsgs[$query_type]]['q'] = $query;
    $__devmsgs[$query_type.'_queries'][$__devmsgs[$query_type]]['timetook'] = $time;
    $__devmsgs['total_query_exec_time'] = $__devmsgs['total_query_exec_time'] + $time;
    $__devmsgs[$query_type.'_queries'][$__devmsgs[$query_type]]['memBefore'] = $memoryBefore;
    $__devmsgs[$query_type.'_queries'][$__devmsgs[$query_type]]['memAfter'] = $memoryNow;
    $__devmsgs[$query_type.'_queries'][$__devmsgs[$query_type]]['memUsed'] = $memoryDif;
    $queryDetails = $__devmsgs[$query_type.'_queries'][$__devmsgs[$query_type]];

    $expesiveQuery = $__devmsgs['expensive_query'];
    $cheapestQuery = $__devmsgs['cheapest_query'];
    
    $insert_qs = $__devmsgs['insert_queries'];
    $select_qs = $__devmsgs['select_queries'];
    $update_qs = $__devmsgs['update_queries'];
    $count_qs = $__devmsgs['delete_queries'];
    $execute_qs = $__devmsgs['execute_queries'];

    $count = 0;
    
    if (empty($expesiveQuery) || empty($cheapestQuery)) {
        $expesiveQuery = $queryDetails;
        $cheapestQuery = $queryDetails;
    } else {
        $memUsed = $queryDetails['memUsed'];
        if ($memUsed > $expesiveQuery['memUsed']) {
            $expesiveQuery = $queryDetails;
        }

        if ($memUsed < $cheapestQuery['memUsed']) {
            $cheapestQuery = $queryDetails;
        }
    }

    $__devmsgs['expensive_query'] = $expesiveQuery;
    $__devmsgs['cheapest_query'] = $cheapestQuery;
    $__devmsgs['total_memory_used'] = $memoryNow;
    $__devmsgs[$query_type] = $__devmsgs[$query_type] + 1;
    $__devmsgs['total_queries'] = $__devmsgs['total_queries'] + 1;

    return $__devmsgs;
}

function showDevWitch() {
    $file = BASEDIR.'/styles/global/devmode.html';
    Template($file, false);
}

// Debug
if(!function_exists("debug")){
    function debug($array) {
        echo "<pre>";
        echo htmlspecialchars(print_r($array, TRUE), ENT_QUOTES, 'utf-8');
        echo "</pre>";
    }
}

function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
}

function delete_folder($directory, $empty = false) { 
    if(substr($directory,-1) == "/") { 
        $directory = substr($directory,0,-1); 
    } 

    if(!file_exists($directory) || !is_dir($directory)) { 
        return false; 
    } elseif(!is_readable($directory)) { 
        return false; 
    } else { 
        $directoryHandle = opendir($directory); 
        
        while ($contents = readdir($directoryHandle)) { 
            if($contents != '.' && $contents != '..') { 
                $path = $directory . "/" . $contents; 
                
                if(is_dir($path)) { 
                    delete_folder($path); 
                } else { 
                    unlink($path); 
                } 
            } 
        } 
        
        closedir($directoryHandle); 

        if($empty == false) { 
            if(!rmdir($directory)) { 
                return false; 
            } 
        } 
        
        return true; 
    } 
}

function Hours2minutes($hours) {
    //Hours format: h:m
    $time = explode(":",$hours);
    $h = $time[0];
    $m = (isset($time[1])) ? $time[1] : 0;
    $m += ($h*60);
    return $m;
}

//Generic validation function
function validate($string,$regex='^[^<>]*$') {
    if ($regex=="url") { $regex = "^[http://]*[a-zA-Z0-9~\._-]*\.*[a-zA-Z0-9~\._-]*\.[A-Za-z]{2,4}/*[a-zA-Z0-9?\.+&@#/\=~_|-]*"; }
    return (preg_match("`".$regex."`is",$string)) ? true : false ;
}

// Clean URL Function, prevents entities in server globals
function cleanurl($url) {
    $bad_entities = array("&", "\"", "'", '\"', "\'", "<", ">", "(", ")", "*");
    $safe_entities = array("&amp;", "", "", "", "", "", "", "", "", "");
    $url = str_replace($bad_entities, $safe_entities, $url);
    return $url;
}

// Strip Input Function, prevents HTML in unwanted places
function stripinput($text) {
    if (!is_array($text)) {
        $text = stripslash(trim($text));
        $text = preg_replace("/(&amp;)+(?=\#([0-9]{2,3});)/i", "&", $text);
        $search = array("&", "\"", "'", "\\", '\"', "\'", "<", ">", "&nbsp;");
        $replace = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;", " ");
        $text = str_replace($search, $replace, $text);
    } else {
        foreach ($text as $key => $value) {
            $text[$key] = stripinput($value);
        }
    }
    return $text;
}

// Prevent any possible XSS attacks via $_GET.
function stripget($check_url) {
    $return = false;
    if (is_array($check_url)) {
        foreach ($check_url as $value) {
            if (stripget($value) == true) {
                return true;
            }
        }
    } else {
        $check_url = str_replace(array("\"", "\'"), array("", ""), urldecode($check_url));
        if (preg_match("/<[^<>]+>/i", $check_url)) {
            return true;
        }
    }
    return $return;
}

// Strip file name
function stripfilename($filename) {
    $filename = strtolower(str_replace(" ", "_", $filename));
    $filename = preg_replace("/[^a-zA-Z0-9_-]/", "", $filename);
    $filename = preg_replace("/^\W/", "", $filename);
    $filename = preg_replace('/([_-])\1+/', '$1', $filename);
    if ($filename == "") { $filename = time(); }

    return $filename;
}

// Strip Slash Function, only stripslashes if magic_quotes_gpc is on
function stripslash($text) {
    if (QUOTES_GPC) { $text = stripslashes($text); }
    return $text;
}

// Add Slash Function, add correct number of slashes depending on quotes_gpc
function addslash($text) {
    if (!QUOTES_GPC) {
        $text = addslashes(addslashes($text));
    } else {
        $text = addslashes($text);
    }
    return $text;
}

// htmlentities is too agressive so we use this function
function phpentities($text) {
    $search = array("&", "\"", "'", "\\", "<", ">");
    $replace = array("&amp;", "&quot;", "&#39;", "&#92;", "&lt;", "&gt;");
    $text = str_replace($search, $replace, $text);
    return $text;
}

// Trim a line of text to a preferred length
function trimlink($text, $length) {
    $dec = array("&", "\"", "'", "\\", '\"', "\'", "<", ">");
    $enc = array("&amp;", "&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;", "&gt;");
    $text = str_replace($enc, $dec, $text);
    if (strlen($text) > $length) $text = substr($text, 0, ($length-3))."...";
    $text = str_replace($enc, $dec, $text);
    return $text;
}

// Validate numeric input
function isnum($value) {
    if (!is_array($value)) {
        return (preg_match("/^[0-9]+$/", $value));
    } else {
        return false;
    }
}

// Custom preg-match function
function preg_check($expression, $value) {
    if (!is_array($value)) {
        return preg_match($expression, $value);
    } else {
        return false;
    }
}


// Debug
if(!function_exists("debug")){
    function debug($array) {
        echo "<pre>";
        echo htmlspecialchars(print_r($array, TRUE), ENT_QUOTES, 'utf-8');
        echo "</pre>";
    }
}

function convertNumberToWord($num = false) {
    // https://stackoverflow.com/questions/11500088/php-express-number-in-words
    $num = str_replace(array(',', ' '), '' , trim($num));
    if(! $num) {
        return false;
    }
    $num = (int) $num;
    $words = array();
    $list1 = array('', 'first', 'second', 'third', 'forth', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
        'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
    );
    $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
    $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
        'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
        'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
    );
    $num_length = strlen($num);
    $levels = (int) (($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int) ($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
        $tens = (int) ($num_levels[$i] % 100);
        $singles = '';
        if ( $tens < 20 ) {
            $tens = ($tens ? '' . $list1[$tens] . '' : '' );
        } else {
            $tens = (int)($tens / 10);
            $tens = ' ' . $list2[$tens] . ' ';
            $singles = (int) ($num_levels[$i] % 10);
            $singles = ' ' . $list1[$singles] . ' ';
        }
        $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
    } //end for loop
    $commas = count($words);
    if ($commas > 1) {
        $commas = $commas - 1;
    }
    return implode(' ', $words);
}

//function to get the remote data
function url_get_contents ($url) {
    if (function_exists('curl_exec')){ 
        $conn = curl_init($url);
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        $url_get_contents_data = (curl_exec($conn));
        curl_close($conn);
    }elseif(function_exists('file_get_contents')){
        $url_get_contents_data = file_get_contents($url);
    }elseif(function_exists('fopen') && function_exists('stream_get_contents')){
        $handle = fopen ($url, "r");
        $url_get_contents_data = stream_get_contents($handle);
    }else{
        $url_get_contents_data = false;
    }
    return $url_get_contents_data;
}


function remove_attribute($attr, $html ) {
   $html = preg_replace( '/('.$attr.')="\d*"\s/', "", $html );
   return $html;
}

function get_first_image($html, $height = "auto") {
    global $config;
        
    //require_once(INFUSIONS.'media/includes/simple_html_dom.php');
    Main::loadLib('simple_html_dom');

    $post_html = str_get_html($html);

    $first_img = $post_html->find('img', 0);
    if($first_img !== null) {
        $image = "<figure class='__media'><div class='__thumb'><img alt='' src='".preg_replace('/uploads/', $config['siteurl'].'images/media/', $first_img->src)."' style='height:".$height."'></div></figure>";
    } else {
        $image = "<figure class='__media'><div class='__thumb'></div></figure>";
    }

    return $image;
}

/**
 *  We need to set DOCUMENT_ROOT in cases where /~username URLs are being used.
 *  In a default WordPress install this should result in the same value as ABSPATH
 *  but ABSPATH and all WP functions are not accessible in the current scope.
 *
 *  This code should work in 99% of cases.
 *  https://github.com/mindsharelabs/mthumb/blob/master/mthumb-config.example.php
 * @param int $levels
 *
 * @return bool|string
 */
function find_root($levels = 9) {
    $dir_name = dirname(__FILE__).'/';
    for($i = 0; $i <= $levels; $i++) {
        $path = realpath($dir_name.str_repeat('../', $i));
        if(file_exists($path.'/config.php')) {
            return $path;
        }
    }
    return FALSE;
}

function is_spider($ua = ''){
    // List: http://www.useragentstring.com/pages/Crawlerlist/

    if(empty($ua)){
        if(!isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT']) || is_null($_SERVER['HTTP_USER_AGENT'])){
            return TRUE;
        }
        $ua=$_SERVER['HTTP_USER_AGENT'];
    }
       
    $ua_bot_regex =   'googlebot|googlebot-image|mediapartners-google|adsbot-google|msnbot|msnbot-media|bingbot|yahoo|yahoo! slurp|yahoo! slurp china|'
                    . 'yahooseeker|yahooseeker-testing|yandexbot|yandeximages|yandexmetrika|baidu transcoder|baiduspider|bloglines subscriber|'
                    . 'charlotte|dotbot|linkwalker|sogou spider|sosoimagespider|'
                    . 'sosospider|speedy spider|yeti|yodaobot|yodaobot-image|youdaobot|008|abachobot|accoona-ai-agent|addsugarspiderbot|anyapexbot|'
                    . 'arachmo|b-l-i-t-z-b-o-t|becomebot|beslistbot|billybobbot|bimbot|blitzbot|boitho.com-dc|boitho.com-robot|btbot|catchbot|cerberian drtrs|converacrawler|cosmos|covario ids|'
                    . 'dataparksearch|diamondbot|discobot|earthcom.info|emeraldshield.com webbot|envolk[its]spider|esperanzabot|exabot|'
                    . 'fast enterprise crawler|fast-webcrawler|fdse robot|findlinks|furlbot|fyberspider|g2crawler|gaisbot|galaxybot|'
                    . 'geniebot|gigabot|girafabot|gurujibot|happyfunbot|hl_ftien_spider|holmes|htdig|iaskspider|ia_archiver|iccrawler|ichiro|igdespyder|'
                    . 'irlbot|issuecrawler|jaxified bot|jyxobot|koepabot|l.webis|lapozzbot|larbin|ldspider|lexxebot|'
                    . 'linguee bot|lmspider|lwp-trivial|mabontland|magpie-crawler|mj12bot|mlbot|mnogosearch|mogimogi|mojeekbot|moreoverbot|morning paper|msrbot|'
                    . 'mvaclient|mxbot|netresearchserver|netseer crawler|newsgator|ng-search|nicebot|noxtrumbot|nusearch spider|nutchcvs|nymesis|'
                    . 'obot|oegp|omgilibot|omniexplorer_bot|oozbot|orbiter|pagebiteshyperbot|peew|polybot|pompos|postpost|psbot|pycurl|qseero|radian6|rampybot|'
                    . 'rufusbot|sandcrawler|sbider|scoutjet|scrubby|searchsight|seekbot|semanticdiscovery|sensis web crawler|seochat::bot|seznambot|shim-crawler|shopwiki|'
                    . 'shoula robot|silk|sitebot|snappy|sogou spider|sqworm|stackrambler|suggybot|surveybot|synoobot|teoma|terrawizbot|thesubot|thumbnail.cz robot|'
                    . 'tineye|truwogps|turnitinbot|tweetedtimes bot|twengabot|updated|urlfilebot|vagabondo|voilabot|vortex|voyager|vyu2|webcollage|websquash.com|wf84|'
                    . 'wofindeich robot|womlpefactory|xaldon_webspider|yacy|yasaklibot|yooglifetchagent|zao|zealbot|zspider|zyborg';

    $ua_bot_regex = strtolower($ua_bot_regex);
    
    //$ua = ($ua == '') ? strtolower($_SERVER['HTTP_USER_AGENT']) : strtolower($ua); 

    return preg_match("/$ua_bot_regex/", $ua);
}

/**
* Convert a timestap into timeago format
* @param time
* @return timeago
*/
function get_timeago($ptime){
    $estimate_time = time() - $ptime;

    if( $estimate_time < 1 ){
        return 'less than 1 second ago';
    }

    $condition = array( 
                12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
    );

    foreach( $condition as $secs => $str ){
        $d = $estimate_time / $secs;

        if( $d >= 1 ){
            $r = round( $d );
            return $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
        }
    }
}


/**
* Share Buttons
* @author KBRmedia
* @since  1.0
*/
function share($url, $title, $site = array()){
    global $settings;
    $html  = "";
    if(empty($site) || in_array("watch",$site)){
        $html .="<a class='fa fa-television blinker' href='".$settings['siteurl']."pages/watch-live-stream' target='_blank' rel='0'><span></span></a>";
    }
    if(empty($site) || in_array("facebook",$site)){
        $html .="<a class='fa fa-facebook' href='http://www.facebook.com/sharer.php?u=".$url."' target='_blank' rel='0' class='popup_share'><span> ".getFacebooks($url)."</span></a>";
    }
    if(empty($site) || in_array("twitter",$site)){
        $html .="<a class='fa fa-twitter' href='http://twitter.com/share?url=".$url."&text=$title' target='_blank' rel='0' class='popup_share'><span></span></a>";
    }
    if(empty($site) || in_array("google",$site)){
        $html .="<a class='fa fa-google' href='https://plus.google.com/share?url=".$url."' target='_blank' rel='0' class='popup_share'><span> ".get_plusones($url)."</span></a>";
    }
    if(empty($site) || in_array("digg",$site)){
        $html .="<a class='fa fa-digg' href='http://www.digg.com/submit?url=".$url."' target='_blank' rel='0' class='popup_share'><span></span></a>";
    }
    if(empty($site) || in_array("reddit",$site)){
        $html .="<a class='fa fa-reddit' href='http://reddit.com/submit?url=".$url."&title=$title' target='_blank' rel='0' class='popup_share'><span></span></a>";
    }
    if(empty($site) || in_array("linkedin",$site)){
        $html .="<a class='fa fa-linkedin' href='http://www.linkedin.com/shareArticle?mini=true&url=".$url."' target='_blank' rel='0' class='popup_share'><span></span></a>";
    }
    if(empty($site) || in_array("stumbleupon",$site)){
        $html .="<a class='fa fa-stumbleupon' href='http://www.stumbleupon.com/submit?url=".$url."&title=$title' target='_blank' rel='0' class='popup_share'><span></span></a>";
    }
    $html .= "";
    return $html;
}

function watched_now($limit=15){
    global $config;
    
    $result = dbquery("SELECT * FROM ".DB_PREFIX."media_node GROUP BY viewed ORDER BY viewed LIMIT $limit");
    echo "<div class='watched-now auto'>";
    echo "<h3 style='padding:2px 13px;'>Viewed Now</h3>";
    echo "<div class='carousel'>";
    echo "<ul class='owl-carousel'>";
    while($row = dbarray($result)){
        $date = date("Y-m-d G:i:s", $row['lastvisit']);
        $image = $post->load_image($row['image']);
        if($config['seourl']==1){
            $url = RewriteUrl($config['site_url']."media.php?cont=topics&amp;cat=".$categories->get_url($row['tid'])."&amp;cat=".$row['id']);
        }else{
            $url = $config['site_url']."media.php?cont=topics&amp;cat=".$categories->get_url($row['tid'])."&amp;cat=".$row['id'];
        }
        echo "<div><a href='$url' title='".$row['title']."'>$image</a></div>";
    }
    echo "</ul></div>";
    echo "</div>";
    
    add_to_head("<link rel='stylesheet' href='".$config['site_url']."infusions/media/include/js/owl.carousel/owl.carousel.css' type='text/css' />");
    add_to_footer("<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.10.0/jquery.min.js' /></script>");
    add_to_footer("<script type='text/javascript' src='".$config['site_url']."infusions/media/include/js/owl.carousel/owl.carousel.min.js' /></script>");
    
    add("<script>
        $(document).ready(function(){  
            $('.owl-carousel').owlCarousel({
                loop:true,
                margin:5,
                nav:false,
                autoplay:true,
                autoplayTimeout:1000,
                autoplayHoverPause:true,
                responsive:{
                    0:{
                        items:1
                    },
                    600:{
                        items:4
                    },
                    1000:{
                        items:4
                    }
                }
            })
        });
    </script>", "custom");      
}