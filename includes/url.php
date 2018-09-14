<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

/**
 * URL class.
 */
class Url {
	public static $current;
	/** @var string */
	private static $url;
	/** @var Controller[] */
	private static $rewrite = array();

	/**
	 * Constructor.
	 *
	 * @param string $url
	 * @param string $ssl Unused
	 */
	public function __construct($url, $ssl = '') {
		static::$url = $url;
	}

	/**
	 *
	 *
	 * @param Controller $rewrite
	 *
	 * @return void
	 */
	public static function addRewrite($rewrite) {
		static::$rewrite[] = $rewrite;
	}

	/**
	 *
	 *
	 * @param string          $route
	 * @param string|string[] $args
	 *
	 * @return string
	 */
	public static function link($route, $args = '') {
		global $aidlink;

		$url = static::$url . 'index.php?cont=' . (string)$route;

		if ($args) {
			if (is_array($args)) {
				$url .= '&amp;' . http_build_query($args);
			} else {
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
		}

		foreach (static::$rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		return $url;
	}


	/**
	 *
	 *
	 * @param string          $route
	 * @param string|string[] $args
	 *
	 * @return string
	 */
	public static function page($route, $args = '') {
		global $aidlink;

		$url = static::$url . 'index.php?cont=' . (string)$route;

		if ($args) {
			if (is_array($args)) {
				$url .= '&amp;' . http_build_query($args);
			} else {
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
		}

		foreach (static::$rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		return $url;
	}
}