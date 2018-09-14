<?php
//Deny direct access
defined("_LOAD") or die("Access denied");


/**
 * language class
 * Reads translation files and parses translation strings
 */
class language
{

    /**
     * Holds all translatable lines
     *
     * @var array
     */
    private static $lines = [];

    public function __construct() {
        self::setActive();
    }

    /**
     * Translates a line of text
     *
     * @param string $key     line to translate
     * @param string $default (optional) fallback value
     * @param array  $args    (optional) variables to replace
     *
     * @return string translated line
     */
    public static function line($key, $default = '', $args = [])
    {
        $parts = explode('.', $key);

        if (count($parts) > 1) {
            $file = array_shift($parts);
            $line = array_shift($parts);
        }

        if (count($parts) == 1) {
            $file = 'global';
            $line = array_shift($parts);
        }

        if ( ! isset(static::$lines[$file])) {
            static::load($file);
        }

        if (isset(static::$lines[$file][$line])) {
            $text = static::$lines[$file][$line];
        } elseif ($default) {
            $text = $default;
        } else {
            $text = $key;
        }

        if (count($args)) {
            return call_user_func_array('sprintf', array_merge([$text], $args));
        }

        return $text;
    }

    /**
     * Loads a translation file
     *
     * @param string $file translation file filesystem name
     *
     * @return void
     */
    private static function load($file)
    {
        if (is_readable($lang = static::path($file))) {

            /** @noinspection PhpIncludeInspection */
            static::$lines[$file] = require $lang;
        }
    }

    /**
     * Resolves the path to a translation file
     *
     * @param string $file filename to resolve
     *
     * @return string resolved file path
     */
    private static function path($file) {
    	global $config;

    	$language = $config['language'];

        return BASEDIR . 'system/languages/' . $language . '/' . $file . '.php';
    }

    public static function getList() {
        $lang = [];
        $handle = opendir(BASEDIR . 'system/languages/');
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..') {
                $file = BASEDIR . 'system/languages/'.$entry;
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (is_file($file) == true && $ext == 'php') {
                    $lang[] = $entry;
                }
            }
        }
        closedir($handle);

        return $lang;
    }

    public static function optDropdown($var = '')
    {
        $langs = self::getList();
        $opt = '';
        foreach ($langs as $lang) {
            $file = explode('.', $lang);
            if ($var == $file[0]) {
                $sel = 'SELECTED';
            } else {
                $sel = '';
            }
            $opt .= "<option {$sel}>{$file[0]}</option>";
        }

        return $opt;
    }

    public static function getDefaultLang() {
        global $config;

        $def = $config['multilang_default'];
        $lang = json_decode($config['multilang_country'], true);
        $deflang = $lang[$def];

        return $deflang;
    }

    public static function existParam($param, $post_id) {
        global $db;

        $post_id = intval($post_id);
        $result = $db->query("SELECT * FROM ".PREFIX."post_param WHERE `post_id` = '{$post_id}' AND `param` = '{$param}' LIMIT 1");
        if ($result->num_rows) {
            return true;
        } else {
            return false;
        }
    }

    public static function addParam($param, $value, $post_id) {
        global $db;

        $post_id = Io::Output($post_id, 'int');
        $param = Io::Output($param);
        $value = Io::Output($value);

        $query = $db->query("INSERT INTO ".PREFIX."post_param SET post_id='{$post_id}', param='{$param}', value='{$value}'");
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public static function editParam($param, $value, $post_id) {
        global $db;

        $post_id = Io::Output($post_id, 'int');
        $param = Io::Output($param);
        $value = Io::Output($value);

        $query = $db->query("UPDATE ".PREFIX."post_param SET value='{$value}' WHERE post_id='{$post_id}' AND param='{$param}'");
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public static function getParam($param, $post_id) {
        global $db;

        $post_id = intval($post_id);
        $result = $db->query("SELECT * FROM ".PREFIX."post_param WHERE `post_id` = '{$post_id}' AND `param` = '{$param}' LIMIT 1");
        if ($result->num_rows) {
            return $result->row['value'];
        } else {
            return '';
        }
    }

    public static function getLangParam($lang, $item) {
        $item = intval($item);
        if (static::existParam('multilang', $item)===true) {
            $langparam = static::getParam('multilang', $item);
            $multilang = json_decode($langparam, true);
            foreach ($multilang as $key => $value) {
                // print_r($value);
                $keys = array_keys($value);
                
                if ($keys[0] == $lang) {
                    $lang = $multilang[$key][$lang];

                    return $lang;
                }
            }
        }
    }

    public static function setActive($lang = '') {
        global $config;

        $lg = $config['multilang_country'];
        $lg = json_decode($lg, true);

        if (isset($_GET['lang']) && $_GET['lang'] != '' && $lang == '') {
            $getLang = $_GET['lang'];
            if (key_exists($getLang, $lg)) {
                Session::Write('lang', $getLang);
            } else {
                Session::Wipe('lang');
            }
        } elseif ($lang != '') {
            if (key_exists($lang, $lg)) {
                Session::Write('lang', $lang);
            } else {
                Session::Wipe('lang');
            }
        }
    }


    public static function isActive() {
        global $config;

        if ($config['multilang_enable'] === 'on') {
            $langs = Session::Read('lang');
            if ($langs != '') {
                $lang = Session::Read('lang');
            } else {
                $lang = Io::GetVar("GET", "lang");
            }
        } else {
            $lang = '';
        }

        return $lang;
    }

    public static function flagList() {
        $lang = json_decode($config['multilang_country'], true);
        $multilang_enable = $config['multilang_enable'];
        // print_r($lang);
        $html = '';
        if (!empty($lang) && $multilang_enable == 'on') {
            $html = '<ul class="nav nav-pills flaglist">';
            foreach ($lang as $key => $value) {
                $flag = strtolower($value['flag']);
                $html .= '
                <li class=""><a href="'.Url::flag($key)."\" class=\"flag-icon flag-icon-{$flag}\"></a></li>
                ";
            }
            $html .= '</ul>';
            //Hooks::attach('footer_load_lib', array('Language', 'flagLib'));
        }

        return $html;
    }

    public static function flagLib() {
        echo '<link href="'.Url::link('assets/css/flag-icon.min.css').'" rel="stylesheet">';
    }


    public static function optCountry($val = '') {
        $countries = self::countryList();
        $opt = '';
        foreach ($countries as $key => $value) {
            ($key == $val) ? $sel = 'SELECTED' : $sel = '';
            $opt .= "<option value=\"{$key}\" title=\"".htmlspecialchars($value)."\" {$sel}>".htmlspecialchars($value).'</option>';
        }

        return $opt;
    }

    public static function countryList() {
        $countries = array('AF' => 'Afghanistan',
                        'AX' => 'Åland Islands',
                        'AL' => 'Albania',
                        'DZ' => 'Algeria',
                        'AS' => 'American Samoa',
                        'AD' => 'Andorra',
                        'AO' => 'Angola',
                        'AI' => 'Anguilla',
                        'AQ' => 'Antarctica',
                        'AG' => 'Antigua and Barbuda',
                        'AR' => 'Argentina',
                        'AM' => 'Armenia',
                        'AW' => 'Aruba',
                        'AU' => 'Australia',
                        'AT' => 'Austria',
                        'AZ' => 'Azerbaijan',
                        'BS' => 'Bahamas',
                        'BH' => 'Bahrain',
                        'BD' => 'Bangladesh',
                        'BB' => 'Barbados',
                        'BY' => 'Belarus',
                        'BE' => 'Belgium',
                        'BZ' => 'Belize',
                        'BJ' => 'Benin',
                        'BM' => 'Bermuda',
                        'BT' => 'Bhutan',
                        'BO' => 'Bolivia',
                        'BA' => 'Bosnia and Herzegovina',
                        'BW' => 'Botswana',
                        'BV' => 'Bouvet Island',
                        'BR' => 'Brazil',
                        'IO' => 'British Indian Ocean Territory',
                        'BN' => 'Brunei Darussalam',
                        'BG' => 'Bulgaria',
                        'BF' => 'Burkina Faso',
                        'BI' => 'Burundi',
                        'KH' => 'Cambodia',
                        'CM' => 'Cameroon',
                        'CA' => 'Canada',
                        'CV' => 'Cape Verde',
                        'KY' => 'Cayman Islands',
                        'CF' => 'Central African Republic',
                        'TD' => 'Chad',
                        'CL' => 'Chile',
                        'CN' => 'China',
                        'CX' => 'Christmas Island',
                        'CC' => 'Cocos (Keeling) Islands',
                        'CO' => 'Colombia',
                        'KM' => 'Comoros',
                        'CG' => 'Congo',
                        'CD' => 'Congo, The Democratic Republic of The',
                        'CK' => 'Cook Islands',
                        'CR' => 'Costa Rica',
                        'CI' => "Cote D'ivoire",
                        'HR' => 'Croatia',
                        'CU' => 'Cuba',
                        'CY' => 'Cyprus',
                        'CZ' => 'Czech Republic',
                        'DK' => 'Denmark',
                        'DJ' => 'Djibouti',
                        'DM' => 'Dominica',
                        'DO' => 'Dominican Republic',
                        'EC' => 'Ecuador',
                        'EG' => 'Egypt',
                        'SV' => 'El Salvador',
                        'GQ' => 'Equatorial Guinea',
                        'ER' => 'Eritrea',
                        'EE' => 'Estonia',
                        'ET' => 'Ethiopia',
                        'FK' => 'Falkland Islands (Malvinas)',
                        'FO' => 'Faroe Islands',
                        'FJ' => 'Fiji',
                        'FI' => 'Finland',
                        'FR' => 'France',
                        'GF' => 'French Guiana',
                        'PF' => 'French Polynesia',
                        'TF' => 'French Southern Territories',
                        'GA' => 'Gabon',
                        'GM' => 'Gambia',
                        'GE' => 'Georgia',
                        'DE' => 'Germany',
                        'GH' => 'Ghana',
                        'GI' => 'Gibraltar',
                        'GR' => 'Greece',
                        'GL' => 'Greenland',
                        'GD' => 'Grenada',
                        'GP' => 'Guadeloupe',
                        'GU' => 'Guam',
                        'GT' => 'Guatemala',
                        'GG' => 'Guernsey',
                        'GN' => 'Guinea',
                        'GW' => 'Guinea-bissau',
                        'GY' => 'Guyana',
                        'HT' => 'Haiti',
                        'HM' => 'Heard Island and Mcdonald Islands',
                        'VA' => 'Holy See (Vatican City State)',
                        'HN' => 'Honduras',
                        'HK' => 'Hong Kong',
                        'HU' => 'Hungary',
                        'IS' => 'Iceland',
                        'IN' => 'India',
                        'ID' => 'Indonesia',
                        'IR' => 'Iran, Islamic Republic of',
                        'IQ' => 'Iraq',
                        'IE' => 'Ireland',
                        'IM' => 'Isle of Man',
                        'IL' => 'Israel',
                        'IT' => 'Italy',
                        'JM' => 'Jamaica',
                        'JP' => 'Japan',
                        'JE' => 'Jersey',
                        'JO' => 'Jordan',
                        'KZ' => 'Kazakhstan',
                        'KE' => 'Kenya',
                        'KI' => 'Kiribati',
                        'KP' => "Korea, Democratic People's Republic of",
                        'KR' => 'Korea, Republic of',
                        'KW' => 'Kuwait',
                        'KG' => 'Kyrgyzstan',
                        'LA' => "Lao People's Democratic Republic",
                        'LV' => 'Latvia',
                        'LB' => 'Lebanon',
                        'LS' => 'Lesotho',
                        'LR' => 'Liberia',
                        'LY' => 'Libyan Arab Jamahiriya',
                        'LI' => 'Liechtenstein',
                        'LT' => 'Lithuania',
                        'LU' => 'Luxembourg',
                        'MO' => 'Macao',
                        'MK' => 'Macedonia, The Former Yugoslav Republic of',
                        'MG' => 'Madagascar',
                        'MW' => 'Malawi',
                        'MY' => 'Malaysia',
                        'MV' => 'Maldives',
                        'ML' => 'Mali',
                        'MT' => 'Malta',
                        'MH' => 'Marshall Islands',
                        'MQ' => 'Martinique',
                        'MR' => 'Mauritania',
                        'MU' => 'Mauritius',
                        'YT' => 'Mayotte',
                        'MX' => 'Mexico',
                        'FM' => 'Micronesia, Federated States of',
                        'MD' => 'Moldova, Republic of',
                        'MC' => 'Monaco',
                        'MN' => 'Mongolia',
                        'ME' => 'Montenegro',
                        'MS' => 'Montserrat',
                        'MA' => 'Morocco',
                        'MZ' => 'Mozambique',
                        'MM' => 'Myanmar',
                        'NA' => 'Namibia',
                        'NR' => 'Nauru',
                        'NP' => 'Nepal',
                        'NL' => 'Netherlands',
                        'AN' => 'Netherlands Antilles',
                        'NC' => 'New Caledonia',
                        'NZ' => 'New Zealand',
                        'NI' => 'Nicaragua',
                        'NE' => 'Niger',
                        'NG' => 'Nigeria',
                        'NU' => 'Niue',
                        'NF' => 'Norfolk Island',
                        'MP' => 'Northern Mariana Islands',
                        'NO' => 'Norway',
                        'OM' => 'Oman',
                        'PK' => 'Pakistan',
                        'PW' => 'Palau',
                        'PS' => 'Palestinian Territory, Occupied',
                        'PA' => 'Panama',
                        'PG' => 'Papua New Guinea',
                        'PY' => 'Paraguay',
                        'PE' => 'Peru',
                        'PH' => 'Philippines',
                        'PN' => 'Pitcairn',
                        'PL' => 'Poland',
                        'PT' => 'Portugal',
                        'PR' => 'Puerto Rico',
                        'QA' => 'Qatar',
                        'RE' => 'Reunion',
                        'RO' => 'Romania',
                        'RU' => 'Russian Federation',
                        'RW' => 'Rwanda',
                        'SH' => 'Saint Helena',
                        'KN' => 'Saint Kitts and Nevis',
                        'LC' => 'Saint Lucia',
                        'PM' => 'Saint Pierre and Miquelon',
                        'VC' => 'Saint Vincent and The Grenadines',
                        'WS' => 'Samoa',
                        'SM' => 'San Marino',
                        'ST' => 'Sao Tome and Principe',
                        'SA' => 'Saudi Arabia',
                        'SN' => 'Senegal',
                        'RS' => 'Serbia',
                        'SC' => 'Seychelles',
                        'SL' => 'Sierra Leone',
                        'SG' => 'Singapore',
                        'SK' => 'Slovakia',
                        'SI' => 'Slovenia',
                        'SB' => 'Solomon Islands',
                        'SO' => 'Somalia',
                        'ZA' => 'South Africa',
                        'GS' => 'South Georgia and The South Sandwich Islands',
                        'ES' => 'Spain',
                        'LK' => 'Sri Lanka',
                        'SD' => 'Sudan',
                        'SR' => 'Suriname',
                        'SJ' => 'Svalbard and Jan Mayen',
                        'SZ' => 'Swaziland',
                        'SE' => 'Sweden',
                        'CH' => 'Switzerland',
                        'SY' => 'Syrian Arab Republic',
                        'TW' => 'Taiwan, Province of China',
                        'TJ' => 'Tajikistan',
                        'TZ' => 'Tanzania, United Republic of',
                        'TH' => 'Thailand',
                        'TL' => 'Timor-leste',
                        'TG' => 'Togo',
                        'TK' => 'Tokelau',
                        'TO' => 'Tonga',
                        'TT' => 'Trinidad and Tobago',
                        'TN' => 'Tunisia',
                        'TR' => 'Turkey',
                        'TM' => 'Turkmenistan',
                        'TC' => 'Turks and Caicos Islands',
                        'TV' => 'Tuvalu',
                        'UG' => 'Uganda',
                        'UA' => 'Ukraine',
                        'AE' => 'United Arab Emirates',
                        'GB' => 'United Kingdom',
                        'US' => 'United States',
                        'UM' => 'United States Minor Outlying Islands',
                        'UY' => 'Uruguay',
                        'UZ' => 'Uzbekistan',
                        'VU' => 'Vanuatu',
                        'VE' => 'Venezuela',
                        'VN' => 'Viet Nam',
                        'VG' => 'Virgin Islands, British',
                        'VI' => 'Virgin Islands, U.S.',
                        'WF' => 'Wallis and Futuna',
                        'EH' => 'Western Sahara',
                        'YE' => 'Yemen',
                        'ZM' => 'Zambia',
                        'ZW' => 'Zimbabwe', );

        return $countries;
    }
}
