<?php

include("tool.php");


	if ( !getSesionDato("eslogueado") ){
		die("documento no accesible: la sesion esta cerrada");
		exit();
	}



	function dumpfile($file,$type="data"){
		$filename = $file;

		$size = filesize($filename);


		if($type=="video") {
			header("Content-Type: video/x-flv");
			header("Pragma: public");
			header("Expires: 0");
			header("Content-Description: File Transfer");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: $size");
			header("Content-Disposition: attachment; filename=\"video.flv\"");
			header("x-tipo: video");
		} else 	if($type=="pdf") {
			header("Content-Type: application/pdf");
			header("Pragma: public");
			header("Expires: 0");
			header("Content-Description: File Transfer");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: $size");
			header("x-tipo: pdf");
		}
		else{
			header("Content-Type: application/octet-stream");
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=\"".basename($file)."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: $size");
			header("x-tipo: otro");
		}


		header("x-path: " . $file);
		$cmd = 'cat  "'. $filename . '"';
		error_log("cmd:$cmd ");
		passthru($cmd,$err);
		exit();
	}

//TODO: comprobar si esta logueado, si no esta logueado no puede acceder a los ficheros

$tipo = "datos";

$forcepdf = $_REQUEST["forcepdf"];

if ($forcepdf) $tipo = "pdf";

$forcevideo = $_REQUEST["forcevideo"];

if ($forcevideo) $tipo = "video";


$modo = $_REQUEST["modo"];


switch($modo){
	case "imagen":
		if ($forcepdf || $forcevideo) die();

		$path = $_REQUEST["path"];
		
		$baseDir = getParametro("basePath");
		
		$abspath = $baseDir .  $path;
		
		if(!file_exists($abspath)){
			header("Location: img/errorimage.gif?imagen+no+encontrada");
			exit();			
		}		
		break;

	default:
		$id_contenido = $_REQUEST["id_contenido"];

		$con = new contenido();

		$exito = $con->Load($id_contenido);

		if (!$exito) {
			error_log("e: fallo intentando cargar contenido($id_contenido)");
			die("fichero no encontrado");
		}
		$path = $con->get("path_archivo");
		break;
}


$baseDir = getParametro("basePath");

$filename = $baseDir . $path;


dumpfile($filename,$tipo);

