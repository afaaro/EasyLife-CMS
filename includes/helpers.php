<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

function loadConfig($filename) {

    $file = BASEDIR."inc/system/config/".$filename.".php";
    if (file_exists($file)) {
        require($file);
    } else {
        trigger_error('Error: Could not load config ' . $filename . '!');
        exit();
    }
}


function get_request() {
    $request = ( isset($_SERVER['REQUEST_URI']) ) ? $_SERVER['REQUEST_URI'] : NULL;
    $request = ( isset($_SERVER['QUERY_STRING']) ) ? str_replace('?' .$_SERVER['QUERY_STRING'], '', $request) : $request;

    return ( isset($request) ) ? explode('/', $request) : array();
}

function get_request_arg($search, $type = 'INT') {
    $arg    = NULL;
    $query  = get_request();
    foreach ($query as $key => $value) {
        if ( $value == $search ) {
            if ( isset($query[$key+1]) ) {
                $arg = $query[$key+1];
            }
        }
    }

    return ( $type == 'INT' ) ? intval($arg) : $arg;
}

function asset($uri) {    
    return rtrim(root_path(), '/') . '/' . $uri;
}

function root_path() {
    if(!preg_match("/^(http:\/\/)/", $_SERVER['HTTP_HOST'])) {
        $server = "http://" . $_SERVER['HTTP_HOST'];
    } else {
        $server = $_SERVER['HTTP_HOST'];
    }
    if(!preg_match("/(\/)$/", $server)) $server = $server . '/';
    $path = explode('/', dirname(htmlentities($_SERVER['PHP_SELF'])));
    $path = $path[1];
    if(!preg_match("/(\/)$/", $path)) $path = $path . '/';
    return $server . $path;
}

function set_vars($file, $data) {

    if ( is_array($data) OR is_int($data) ) {
        
        //$file = totranslit($file, true, false);   
        $fp = fopen( BASEDIR . 'inc/storage/cache/' . $file . '.php', 'wb+' );
        fwrite( $fp, serialize( $data ) );
        fclose( $fp );
        
        @chmod( BASEDIR . 'inc/storage/cache/' . $file . '.php', 0666 );

    }

}

function get_vars($file) {
    //$file = totranslit($file, true, false);

    $data = @file_get_contents( BASEDIR . 'inc/storage/cache/' . $file . '.php' );

    if ( $data !== false ) {

        $data = unserialize( $data );
        if ( is_array($data) OR is_int($data) ) return $data;

    } 

    return false;

}

function bg_class($bg1='odd', $bg2='even'){
    global $i;
    return (($i++%2)!=0) ? $bg1 : $bg2;
}

/**
 * Redirect browser using header or script function
 *
 * @param            $location - Desintation URL
 * @param bool|FALSE $delay    - meta refresh delay
 * @param bool|FALSE $script   - true if you want to redirect via javascript
 *
 * @define STOP_REDIRECT to prevent redirection
 */
function redirect($location, $delay = FALSE, $script = FALSE) {
    if (isnum($delay)) {
        echo "<meta http-equiv='refresh' content='$delay; url=".str_replace("&amp;", "&", $location)."' />";
    } else {
        if ($script == FALSE) {
            header("Location: ".str_replace("&amp;", "&", $location));
            exit;
        } else {
            echo "<script type='text/javascript'>document.location.href='".str_replace("&amp;", "&", $location)."'</script>\n";
            exit;
        }
    }
}

function generate_key($length=10, $type='password') {
    $random = NULL;
    $chars  = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ( $type == 'password' ) {
        $chars .= '`~!@#$%^&*()_-+={}[]|\;":,<>/?';
    }

    $index  = 1;
    while ( $index <= $length ) {
        $max        = strlen($chars)-1;
        $num        = rand(0, $max);
        $tmp        = substr($chars, $num, 1);
        $random        .= $tmp;
        ++$index;
    }
    
    return $random;
}

/**
 * Generate a clean Request URI
 *
 * @param string    $request_addition    - 'page=1&amp;ref=2' or array('page' => 1, 'ref' => 2)
 * @param array     $filter_array        - array('aid','page', ref')
 * @param bool|TRUE $keep_filtered       - true to keep filter, false to remove filter from FUSION_REQUEST
 *                                       If remove is true, to remove everything and keep $requests_array and $request
 *                                       addition. If remove is false, to keep everything else except $requests_array
 *
 * @return string
 */
function clean_request($request_addition = '', array $filter_array = array(), $keep_filtered = TRUE) {

    $fusion_query = array();

    $url = ((array)parse_url($_SERVER['REQUEST_URI'])) + array(
            'path'  => '',
            'query' => ''
        );

    if ($url['query']) {
        parse_str($url['query'], $fusion_query); // this is original.
    }

    if ($keep_filtered) {
        $fusion_query = array_intersect_key($fusion_query, array_flip($filter_array));
    } else {
        $fusion_query = array_diff_key($fusion_query, array_flip($filter_array));
    }

    if ($request_addition) {

        $request_addition_array = array();

        if (is_array($request_addition)) {
            $fusion_query = $fusion_query + $request_addition;
        } else {
            parse_str($request_addition, $request_addition_array);
            $fusion_query = $fusion_query + $request_addition_array;
        }
    }

    $prefix = $fusion_query ? '?' : '';
    $query = $url['path'].$prefix.http_build_query($fusion_query, 'flags_', '&amp;');

    return (string)$query;
}


if (!function_exists('colorbox')) {
    function colorbox($img_path, $img_title, $responsive = TRUE, $class = '') {
        if (!defined('COLORBOX')) {
            define('COLORBOX', TRUE);
            add_to_head("<link rel='stylesheet' href='".BASEDIR."assets/jquery/colorbox/colorbox.css' type='text/css' media='screen' />");
            add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/colorbox/jquery.colorbox.js'></script>");
            add_to_jquery("$('a[rel^=\"colorbox\"]').colorbox({ current: '',width:'80%',height:'80%'});");
        }
        $class = ($class ? " $class" : '');
        if ($responsive) {
            $class = " class='img-responsive $class";
        } else {
            $class = (!empty($class) ? " class='$class'" : '');
        }

        return "<a target='_blank' href='$img_path' title='$img_title' rel='colorbox'><img src='$img_path'".$class."alt='$img_title'/></a>";
    }
}

if (!function_exists('gallery')) {
    function gallery($img_path, $img_title, $identifier = 'gallery') {
        add_to_head("<link rel='stylesheet' href='".BASEDIR."assets/jquery/unitegallery/css/unite-gallery.css' type='text/css' media='screen' />");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/themes/default/ug-theme-default.js'></script>");
        add_to_head("<link rel='stylesheet' href='".BASEDIR."assets/jquery/unitegallery/themes/default/ug-theme-default.css' type='text/css' media='screen' />");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/themes/video/ug-theme-video.js'></script>");

        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-common-libraries.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-functions.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-thumbsgeneral.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-thumbsstrip.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-touchthumbs.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-panelsbase.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-strippanel.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-gridpanel.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-thumbsgrid.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-tiles.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-tiledesign.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-avia.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-slider.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-sliderassets.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-touchslider.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-zoomslider.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-video.js'></script>");        
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-gallery.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-lightbox.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-carousel.js'></script>");
        add_to_head("<script type='text/javascript' src='".BASEDIR."assets/jquery/unitegallery/js/ug-api.js'></script>");

        add_to_footer("<script type='text/javascript'>jQuery(document).ready(function(){ jQuery('#".$identifier."').unitegallery(); });</script>");
        
        echo "<img alt='$img_title' src='$img_path' data-image='$img_path' data-description='$img_title'>";
    }
}

function youtubeID($data) {
    // IF 11 CHARS
    if( strlen($data) == 11 ){
        return $data;
    }
    
    preg_match( "/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/", $data, $matches);
    return isset($matches[2]) ? $matches[2] : false;
}


/**
 * Unserialize value only if it was serialized.
 *
 * @since 2.0.0
 *
 * @param string $original Maybe unserialized original, if is needed.
 * @return mixed Unserialized data can be any type.
 */
function maybe_unserialize( $original ) {
    if ( is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
        return @unserialize( $original );
    return $original;
}

/**
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 *
 * @since 2.0.5
 *
 * @param string $data   Value to check to see if was serialized.
 * @param bool   $strict Optional. Whether to be strict about the end of the string. Default true.
 * @return bool False if not serialized and true if it was.
 */
function is_serialized( $data, $strict = true ) {
    // if it isn't a string, it isn't serialized.
    if ( ! is_string( $data ) ) {
        return false;
    }
    $data = trim( $data );
    if ( 'N;' == $data ) {
        return true;
    }
    if ( strlen( $data ) < 4 ) {
        return false;
    }
    if ( ':' !== $data[1] ) {
        return false;
    }
    if ( $strict ) {
        $lastc = substr( $data, -1 );
        if ( ';' !== $lastc && '}' !== $lastc ) {
            return false;
        }
    } else {
        $semicolon = strpos( $data, ';' );
        $brace     = strpos( $data, '}' );
        // Either ; or } must exist.
        if ( false === $semicolon && false === $brace )
            return false;
        // But neither must be in the first X characters.
        if ( false !== $semicolon && $semicolon < 3 )
            return false;
        if ( false !== $brace && $brace < 4 )
            return false;
    }
    $token = $data[0];
    switch ( $token ) {
        case 's' :
            if ( $strict ) {
                if ( '"' !== substr( $data, -2, 1 ) ) {
                    return false;
                }
            } elseif ( false === strpos( $data, '"' ) ) {
                return false;
            }
            // or else fall through
        case 'a' :
        case 'O' :
            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b' :
        case 'i' :
        case 'd' :
            $end = $strict ? '$' : '';
            return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
    }
    return false;
}

/**
 * Check whether serialized data is of string type.
 *
 * @since 2.0.5
 *
 * @param string $data Serialized data.
 * @return bool False if not a serialized string, true if it is.
 */
function is_serialized_string( $data ) {
    // if it isn't a string, it isn't a serialized string.
    if ( ! is_string( $data ) ) {
        return false;
    }
    $data = trim( $data );
    if ( strlen( $data ) < 4 ) {
        return false;
    } elseif ( ':' !== $data[1] ) {
        return false;
    } elseif ( ';' !== substr( $data, -1 ) ) {
        return false;
    } elseif ( $data[0] !== 's' ) {
        return false;
    } elseif ( '"' !== substr( $data, -2, 1 ) ) {
        return false;
    } else {
        return true;
    }
}

/**
 * Serialize data, if needed.
 *
 * @since 2.0.5
 *
 * @param string|array|object $data Data that might be serialized.
 * @return mixed A scalar data
 */
function maybe_serialize( $data ) {
    if ( is_array( $data ) || is_object( $data ) )
        return serialize( $data );

    // Double serialization is required for backward compatibility.
    // See https://core.trac.wordpress.org/ticket/12930
    // Also the world will end. See WP 3.6.1.
    if ( is_serialized( $data, false ) )
        return serialize( $data );

    return $data;
}