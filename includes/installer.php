<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Installer {
    public static $connection;

	public static function run() {

        // create database connection
        //static::connect($config_db);

	}

    /**
     * Connects to the database
     *
     * @param array $settings connection configuration data
     *
     * @return void
     * @throws \ErrorException
     */
    public static function connect($config_db) {
        static::$connection = new Database($config_db['host'], $config_db['user'], $config_db['pass'], $config_db['name']);
    }

    /**
     * Creates the database schema
     *
     * @param array $settings
     *
     * @return void
     */
    public static function schema($conn, $prefix) {
        $sql = Braces::compile(BASEDIR . 'inc/storage/database.distro', [
            'prefix'  => isset($prefix) ? $prefix : '',
            //'now'     => gmdate('Y-m-d H:i:s'),
        ]);

        $conn->query($sql);
    }

    /**
     * Creates the .htaccess file
     *
     * @param array $settings
     *
     * @return void
     */
    public static function rewrite($dir) {
        if (mod_rewrite() or (is_apache())) {
            $htaccess = Braces::compile(BASEDIR . 'includes/storage/htaccess.distro', [
                'base'  => (isset($dir) ? $dir : ""),
                'index' => 'index.php?cont=$1'
            ]);

            if (isset($htaccess) and is_writable($filepath = BASEDIR)) {
                file_put_contents($filepath . '.htaccess', $htaccess);
            }
        }
    }
}

/**
 * Checks whether the current web server is an Apache httpd
 *
 * @return bool
 */
function is_apache() {
    return stripos(PHP_SAPI, 'apache') !== false;
}

/**
 * Checks whether PHP is running as a CGI daemon
 *
 * @return bool
 */
function is_cgi() {
    return stripos(PHP_SAPI, 'cgi') !== false;
}

/**
 * Checks whether mod_rewrite is enabled
 *
 * @return bool
 */
function mod_rewrite() {
    if (is_apache() and function_exists('apache_get_modules')) {
        return in_array('mod_rewrite', apache_get_modules());
    }

    return getenv('HTTP_MOD_REWRITE') ? true : false;
}