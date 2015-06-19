<?php

/**
 * Includo central
 *
 * Este include llama a los includes basicos que el resto de modulos esperan esten siempre presente
 * y ajusta algunos valores por defecto que requieren todas las paginas y la gestion de sesiones
 * @package ecomm-core
 */

$lang = "es";

if( 0 ){
	ini_set("session.gc_maxlifetime",    "86400");
	ini_alter("session.cookie_lifetime", "86400" );


	$expire = 60*60*23;
	ini_set("session.gc_maxlifetime", $expire);

	if (empty($_COOKIE['PHPSESSID'])) {
		session_set_cookie_params($expire);
		session_start();
	} else {
		session_start();
		setcookie("PHPSESSID", session_id(), time() + $expire);
	}
} else {
		//Si no hay sesion, la creamos.

		if (!defined("NO_SESSION")) {
			if (session_id() == "") session_start();
		}
}

$modo = (isset($_REQUEST["modo"])?$_REQUEST["modo"]:false);


function d($texto){
    error_log("d:".var_export($texto,true));
}

if(function_exists("get_magic_quotes_gpc")){
	if (get_magic_quotes_gpc()) {
		function stripslashes_profundo($valor)    {
			$valor = is_array($valor) ?
						array_map('stripslashes_profundo', $valor) :
						stripslashes($valor);
			return $valor;
		}

		$_POST = array_map('stripslashes_profundo', $_POST);
		$_GET = array_map('stripslashes_profundo', $_GET);
		$_COOKIE = array_map('stripslashes_profundo', $_COOKIE);
		$_REQUEST = array_map('stripslashes_profundo', $_REQUEST);
	}
}


if(!function_exists("_")){
	function _($text){
		return $text;
	}
}

function iconv2($cosa,$cosa,$cadena){//fakear un iconv
	return $cadena;
}


$SEPARADOR = DIRECTORY_SEPARATOR;
define('__ROOT__', (dirname(__FILE__)));


include_once("config/config.php");
include_once("inc/debug.inc.php");
include_once("inc/clean.inc.php");

include_once("inc/db.inc.php");	


include_once("inc/xul.inc.php");
include_once("inc/html.inc.php");
include_once("inc/supersesion.inc.php");
include_once("inc/combos.inc.php");
include_once("inc/indexador.inc.php");

include_once("inc/tool.inc.php");
include_once("inc/auth.inc.php");

include_once("class/json.class.php");//comunicacion
include_once("class/cursor.class.php");
include_once("class/contenido.class.php");

include_once("class/config.class.php");


define("TIPO_TEXTO",1);
define("TIPO_EXCLUSIVO",2);
define("TIPO_MULTIPLE",3);


$template = array();

$script = basename($_SERVER['SCRIPT_NAME']);
$script = substr($script, 0, -4);


$template["modname"] = $script;


include_once(__ROOT__ . "/class/pagina.class.php");
include_once(__ROOT__ . "/class/patgenerator.class.php");

$lang = "es";

$page    =    &new Pagina();
$page->Inicia($template["modname"] );


$tituloSitio = "QualidDoc";



function valido8($data) {
    $valid_utf8 = (@iconv('UTF-8', 'UTF-8', $data) === $data);

    if (!$valid_utf8)
        $data = utf8_encode($data);

    return $data;
}



define("MAGPIE_CACHE_DIR",__ROOT__ . "/cache/");
define('MAGPIE_OUTPUT_ENCODING',"UTF-8");
define('MAGPIE_CACHE_ON', false);
define('MAGPIE_DEBUG',true);
//define('MAGPIE_INPUT_ENCODING', "ISO-8859-15");

define("CAT_PORTADA",0);

if (defined("CAPTURARTODOBUG")) {

        function myErrorHandler_Tool($errno, $errstr, $errfile, $errline) {
            global $corriendoGeneral;

            if (!(error_reporting() & $errno)) {
                // This error code is not included in error_reporting
                return;
            }

            switch ($errno) {
                case E_USER_ERROR:
                    echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
                    echo "  Fatal error on line $errline in file $errfile";
                    echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                    echo "Aborting...(limpiando procs)<br />\n";

                    exit(1);
                    break;

                case E_USER_WARNING:
                    echo "<b>WARNING</b> [$errno] $errstr<br />\n";
                    break;

                case E_USER_NOTICE:
                    echo "<b>NOTICE</b> [$errno] $errstr<br />\n";
                    break;

                default:
                    echo "Unknown error type: [$errno] $errstr<br />\n";
                    break;
            }

            /* Don't execute PHP internal error handler */
            return true;
        }

        // set to the user defined error handler
        $old_error_handler = set_error_handler("myErrorHandler_Tool");

        function handleShutdown_Tool() {
            global $corriendoGeneral, $selected_moduleº;
            $error = error_get_last();
            if ($error !== NULL) {
                $info = "[SHUTDOWN] file:" . $error['file'] . " | ln:" . $error['line'] . " | msg:" . $error['message'];
                echo $info . "<br>\n";
                echo "Aborting...(limpiando procs)<br />\n";
                if (function_exists("abortarRunGateway"))
                    abortarRunGateway();
            }
        }

        register_shutdown_function('handleShutdown_Tool');
    }


