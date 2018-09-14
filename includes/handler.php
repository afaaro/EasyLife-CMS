<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Handler {
    /**
     * Additional tags to the html head
     *
     * @var string
     */
    public static $pageHeadTags = "";
    /**
     * Additional contents to the footer
     *
     * @var string
     */
    public static $pageFooterTags = "";
    /**
     * Additional javascripts
     *
     * @var string
     */
    public static $jqueryTags = "";
    /**
     * The title in the "title" tag
     *
     * @var string
     */
    public static $pageTitle = "";
    /**
     * Associative array of meta tags
     *
     * @var string[]
     */
    private static $pageMeta = array();
    /**
     * PHP code to execute using eval replace anything in the output
     *
     * @var callback[]
     */
    private static $outputHandlers = array();

    /**
     * Set the new title of the page
     *
     * @param string $title
     */
    public static function setTitle($title = "") {
        self::$pageTitle = $title;
    }

    /**
     * Append something to the title of the page
     *
     * @param string $addition
     */
    public static function addToTitle($addition = "") {
        define(_SITETITLE_SEPARATOR, '|');
        self::$pageTitle .= preg_replace("/"._SITETITLE_SEPARATOR."/", ' ', $addition, 1);
    }

    /**
     * Set a meta tag by name
     *
     * @param string $name
     * @param string $content
     */
    public static function setMeta($name, $content = "") {
        self::$pageMeta[$name] = $content;
    }

    /**
     * Append something to a meta tag
     *
     * @param string $name
     * @param string $addition
     */
    public static function addToMeta($name, $addition = "") {
        global $config;

        if (empty(self::$pageMeta)) {
            self::$pageMeta = array(
                "description" => $config['description'],
                "keywords" => $config['keywords']
            );
        }
        if (isset(self::$pageMeta[$name])) {
            self::$pageMeta[$name] .= ",".$addition;
        }
    }

    /**
     * Add content to the html head
     *
     * @param string $tag
     */
    public static function addToHead($tag = "") {
        if (!\stristr(self::$pageHeadTags, $tag)) {
            self::$pageHeadTags .= $tag."\n";
        }
    }

    /**
     * Add content to the footer
     *
     * @param string $tag
     */
    public static function addToFooter($tag = "") {
        if (!stristr(self::$pageFooterTags, $tag)) {
            self::$pageFooterTags .= $tag."\n";
        }
    }

    /**
     * Replace something in the output using regexp
     *
     * @param string $target Regexp pattern without delimiters
     * @param string $replace The new content
     * @param string $modifiers Regexp modifiers
     */
    public static function replaceInOutput($target, $replace, $modifiers = "") {
        self::$outputHandlers[] = function ($output) use ($target, $replace, $modifiers) {
            return preg_replace('^'.preg_quote($target, "^").'^'.$modifiers, $replace, $output);
        };
    }

    /**
     * Add a new output handler function
     *
     * @param callback $callback The name of a function or other callable object
     */
    public static function addHandler($callback) {
        if (is_callable($callback)) {
            self::$outputHandlers[] = $callback;
        }
    }

    /**
     * Add javascript source code to the output
     *
     * @param string $tag
     */
    public static function addToJQuery($tag = "") {
        self::$jqueryTags .= $tag;
    }

    /**
     * Get Current Page Title
     */
    public static function getTitle() {
        if (!empty(self::$pageTitle)) {
            return self::$pageTitle;
        }

        return "";
    }


    /**
     * Execute the output handlers
     *
     * @global array $locale
     * @param string $output
     * @return string
     */
    public static function handleOutput($output) {
        global $config;

        if(!empty(self::$pageFooterTags)){
            $output = preg_replace("#</body>#", self::$pageFooterTags."</body>", $output, 1);
        }
        
        if (!empty(self::$pageHeadTags)) {
            $output = preg_replace("#</head>#", self::$pageHeadTags."</head>", $output, 1);
        }

        if (self::$pageTitle != $config['site_name']) {
            $output = preg_replace("#<title>.*</title>#i",
                                   "<title>".self::$pageTitle.(self::$pageTitle ? ' | ' : '').$config['site_name']."</title>",
                                   $output, 1);
        }

        if (!empty(self::$pageMeta)) {
            foreach (self::$pageMeta as $name => $content) {
                $output = preg_replace("#<meta (http-equiv|name)='$name' content='.*' />#i", "<meta \\1='".$name."' content='".$content."' />",
                                       $output, 1);
            }
        }

        foreach (self::$outputHandlers as $handler) {
            $output = $handler($output);
        }
        return $output;
    }
}

/**
 * Set the new title of the page
 *
 * @param string $title
 */
function set_title($title = "") {
    Handler::setTitle($title);
}

/**
 * Append something to the title of the page
 *
 * @param string $addition
 */
function add_to_title($addition = "") {
    Handler::addToTitle($addition);
    $st = Ram::Get('site_name');
    $st[] = $addition;
    Ram::Set('site_name',$st);
}

/**
 * Set a meta tag by name
 *
 * @param string $name
 * @param string $content
 */
function set_meta($name, $content = "") {
    Handler::setMeta($name, $content);
}

/**
 * Append something to a meta tag
 *
 * @param string $name
 * @param string $addition
 */
function add_to_meta($name, $addition = "") {
    Handler::addToMeta($name, $addition);
}

/**
 * Add content to the html head
 *
 * @param string $tag
 */
function add_to_head($tag = "") {
    Handler::addToHead($tag);
}

/**
 * Add content to the footer
 *
 * @param string $tag
 */
function add_to_footer($tag = "") {
    Handler::addToFooter($tag);
}

/**
 * Replace something in the output using regexp
 *
 * @param string $target Regexp pattern without delimiters
 * @param string $replace The new content
 * @param string $modifiers Regexp modifiers
 */
function replace_in_output($target, $replace, $modifiers = "") {
    Handler::replaceInOutput($target, $replace, $modifiers);
}

/**
 * Add a new output handler function
 *
 * @param callback $callback The name of a function or other callable object
 */
function add_handler($callback) {
    Handler::addHandler($callback);
}

/**
 * Execute the output handlers
 *
 * @param string $output
 * @return string
 */
function handle_output($output) {
    return Handler::handleOutput($output);
}

/**
 * Add javascript source code to the output
 *
 * @param string $tag
 */
function add_to_jquery($tag = "") {
    Handler::addToJQuery($tag);
}
