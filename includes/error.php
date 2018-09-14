<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

class Error {
	static function Trigger($level,$message,$error="") {
		global $config,$db,$User;

		switch (MB::strtoupper($level)) {
			case "FATAL":
				self::MemErrorHandler(256,$message."<br /><em>$error</em>",false,false);
				break;
			case "WARNING":
				self::MemErrorHandler(512,$message."<br /><em>$error</em>",false,false);
				break;
			case "NOTICE":
				self::MemErrorHandler(1024,$message."<br /><em>$error</em>",false,false);
				break;
			default:
			case "USERERROR":
				echo "<div class='error_user'>\n<div><strong>{$message}</strong></div>";
				if ((iSUPERADMIN) && !empty($error)) { echo "<div><em>$error</em></div>"; }
				echo "</div>\n";
				//$log_user = Utils::GetComOption("error_handler","log_user",0);
				//if ($log_user) self::StoreLog("error_usererror",array("query" => 111, "errno" => 111, "error" => 111)); //TODO
				break;
			case "INFO":
				echo "<div class='error_info'>\n<div><strong>{$message}</strong></div>";
				if (!empty($error)) { echo "<div><em>$error</em></div>"; }
				echo "</div>\n";
				break;
			//Mini Info
			case "MINFO":
				echo "<div class='error_minfo'>\n<div>{$message}</div>";
				if (!empty($error)) { echo "<div><em>$error</em></div>"; }
				echo "</div>\n";
				break;
			case "DIALOG":
				echo "<div class='error_dialog'>\n<div><strong>{$message}</strong></div>";
				echo "<div><em>$error</em></div>";
				echo "</div>\n";
				break;
		}
	}

	static function MemErrorHandler($errno,$errstr,$errfile,$errline) {
		global $User,$db;

		if (error_reporting()==0) return; //Error supressed with @
		if ($errno==2048) return; //Ignore E_STRICT
		$log_sys = @Utils::GetComOption("error_handler","log_sys",1);
		
		$errnos = array(
			1		=> array('E_ERROR','Error'),
			2		=> array('E_WARNING','Warning'),
			4		=> array('E_PARSE','Parse'),
			8		=> array('E_NOTICE','Notice'),
			16		=> array('E_CORE_ERROR','Core Error'),
			32		=> array('E_CORE_WARNING','Core Warning'),
			64		=> array('E_COMPILE_ERROR','Compile Error'),
			128		=> array('E_COMPILE_WARNING','Compile Warning'),
			256		=> array('E_USER_ERROR','User Error'),
			512		=> array('E_USER_WARNING','User Warning'),
			1024	=> array('E_USER_NOTICE','User Notice'),
			2048	=> array('E_STRICT','Strict'),
			4096	=> array('E_RECOVERABLE_ERROR','Recoverable Error'),
			8192	=> array('E_DEPRECATED','Deprecated'),
			16384	=> array('E_USER_DEPRECATED','User Deprecated')
		);

		$errtrace = debug_backtrace();
		if (defined("_ERRHDL")) {
			//Full error display
			if (MB::stripos($errstr,"_fetch_assoc") || MB::stripos($errstr,"_num_rows") || MB::stripos($errstr,"ot a valid MySQL")) {
				//MySQL
				if ($User->IsAdmin() && error_reporting()<>0) {
					echo "<div class='error_sys'>\n";
						echo "<div><strong>MySQL</strong> (".$db->GetErrno().")</div>\n";
						echo "<div class='e_txt'>".$db->GetError()."</div>\n";
						echo "<div><strong>query:</strong> ".$errtrace[2]['args'][0]."</div>\n";
						echo "<div><strong>File:</strong> ".$errtrace[2]['file']."</div>\n";
						echo "<div><strong>Line:</strong> ".$errtrace[2]['line']."</div>\n";
					echo "</div>\n";
				}
				if ($log_sys) self::StoreLog("error_mysql","Message: ".$db->GetError()."<br />query: ".$errtrace[2]['args'][0]."<br />File: ".$errtrace[2]['file']."<br />Line: ".$errtrace[2]['line']);
			} else if (in_array($errno,array(256,512,1024))) {
				//User
				if ($User->IsAdmin() && error_reporting()<>0) {
					echo "<div class='error_sys'>\n";
						echo "<div><strong>".$errnos[$errno][1]."</strong> ($errno)</div>\n";
						echo "<div class='e_txt'>$errstr</div>\n";
						echo "<div><strong>File:</strong> ".$errtrace[1]['file']."</div>\n";
						echo "<div><strong>Line:</strong> ".$errtrace[1]['line']."</div>\n";
					echo "</div>\n";
				}
				if ($log_sys) self::StoreLog("error_user","Message: $errstr<br />File: ".$errtrace[1]['file']."<br />Line: ".$errtrace[1]['line']);
			} else if (MB::stripos($errstr,"ysql_real_escape_string")) {
				if ($User->IsAdmin() && error_reporting()<>0) {
					echo "<div class='error_sys'>\n";
						echo "<div><strong>".$errnos[$errno][1]."</strong> ($errno)</div>\n";
						echo "<div class='e_txt'>$errstr</div>\n";
						echo "<div><strong>File:</strong> ".$errtrace[2]['file']."</div>\n";
						echo "<div><strong>Line:</strong> ".$errtrace[2]['line']."</div>\n";
					echo "</div>\n";
				}
				if ($log_sys) self::StoreLog("error_user","Message: $errstr<br />File: ".$errtrace[2]['file']."<br />Line: ".$errtrace[2]['line']);
			} else {
				//Sys
				if ($User->IsAdmin() && error_reporting()<>0) {
					echo "<div class='error_sys'>\n";
						echo "<div><strong>".$errnos[$errno][1]."</strong> ($errno)</div>\n";
						echo "<div class='e_txt'>$errstr</div>\n";
						echo "<div><strong>File:</strong> $errfile</div>\n";
						echo "<div><strong>Line:</strong> $errline</div>\n";
					echo "</div>\n";
				}
				if ($log_sys) self::StoreLog("error_sys","Message: $errstr<br />File: $errfile<br />Line: $errline");
			}
		} else {
			//Simple error display
			echo "<div style='margin:2px;padding:2px;background-color:#FFDFDF;border:1px solid #FFA8A8;'>\n";
				echo "<div style='padding:4px;background-color:#FFF0F0;color:#990000;'>$errstr</div>\n";
			echo "</div>";
			
			if ($log_sys) self::StoreLog("error_sys","Message: $errstr<br />File: $errfile<br />Line: $errline");
		}
	}

	static function ClearLog() {
		global $db,$User;

		if (!$User->IsAdmin()) return false;
		$db->query("TRUNCATE ".PREFIX."log");
		return true;
	}

	static function StoreLog($label,$message) {
		global $db,$User;

		$label = MB::strtolower($label);
		$uniqueid = md5($label.$message);

		if (!@$db->query("SELECT uniqueid FROM ".PREFIX."log WHERE uniqueid='".$db->escape($uniqueid)."'")->row)
		@$db->query("INSERT INTO ".PREFIX."log (label,message,ip,time,uniqueid)
					 VALUES ('".$db->escape($label)."','".$db->escape(Io::Filter($message))."','".$db->escape(Utils::Ip2Num($User->Ip()))."',NOW(),'".$db->escape($uniqueid)."')");
	}

	static function GetLog($label=false) {
		global $db,$User;

		if (!$User->IsAdmin()) return false;
		$where = ($label) ? "WHERE label='".$db->escape($label)."' " : "" ;
		return $db->query("SELECT label,message,ip,time FROM ".PREFIX."log {$where}ORDER BY time DESC")->rows;
	}

    /**
     * Error handler
     * This will catch the php native error and treat it as a exception
     * which will provide a full back trace on all errors
     *
     * @param string $code
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @throws \ErrorException
     */
    public static function native($code, $message, $file, $line, $context) {
        if ($code & error_reporting()) {
            static::exception(new ErrorException($message, $code, 0, $file, $line));
        }
    }

    /**
     * Exception handler
     * This will log the exception and output the exception properties
     * formatted as html or a 500 response depending on your application config
     *
     * @param \Exception The uncaught exception
     *
     * @return void
     * @throws \ErrorException
     */
    public static function exception($e) {
        global $config;

        static::log($e);

        if ($config['report'] == 1) {
            // clear output buffer
            while (ob_get_level() > 1) {
                ob_end_clean();
            }

            echo '<html>
                <head>
                    <title>Uncaught Exception</title>
                    <style>
                        body{font-family:"Open Sans",arial,sans-serif;background:#FFF;color:#333;margin:2em}
                        code{background:#D1E751;border-radius:4px;padding:2px 6px}
                    </style>
                </head>
                <body>
                    <h1>Uncaught Exception</h1>
                    <p><code>' . $e->getMessage() . '</code></p>
                    <h3>Origin</h3>
                    <p><code>' . substr($e->getFile(), strlen(BASEDIR)) . ' on line ' . $e->getLine() . '</code></p>
                    <h3>Trace</h3>
                    <pre>' . $e->getTraceAsString() . '</pre>
                </body>
                </html>';
        } else {
            // issue a 500 response
            //Response::error(500, ['exception' => $e])->send();
            echo '<html>
                <head>
                    <title>Uncaught Exception</title>
                    <style>
                        body{font-family:"Open Sans",arial,sans-serif;background:#FFF;color:#333;margin:2em}
                        code{background:#D1E751;border-radius:4px;padding:2px 6px}
                    </style>
                </head>
                <body>
                    <h1>Uncaught Exception</h1>
                    <p><code>' . $e->getMessage() . '</code></p>
                    <h3>Origin</h3>
                    <p><code>' . substr($e->getFile(), strlen(BASEDIR)) . ' on line ' . $e->getLine() . '</code></p>
                    <h3>Trace</h3>
                    <pre>' . $e->getTraceAsString() . '</pre>
                </body>
                </html>';
        }

        exit(1);
    }

    /**
     * Exception logger
     * Log the exception depending on the application config
     *
     * @param object The exception
     */
    public static function log($e) {
        if (is_callable($logger = Config::error('log'))) {
            call_user_func($logger, $e);
        }
    }

    /**
     * Shutdown handler
     * This will catch errors that are generated at the shutdown level of execution
     *
     * @return void
     * @throws \ErrorException
     */
    public static function shutdown() {
        if ($error = error_get_last()) {

            /** @var string $message */
            /** @var string $type */
            /** @var string $file */
            /** @var int $line */
            extract($error);

            static::exception(new ErrorException($message, $type, 0, $file, $line));
        }
    }
}
