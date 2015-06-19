<?php

//define("CAPTURARTODOBUG", "true");

include("tool.php");
include(__ROOT__ ."/inc/autenticacion_usuario.inc.php");


$cat = CleanID($_REQUEST["cat"]);
$cat_s = sql($cat);

switch($modo){
    case "loguear":
	$login_s = trim($_REQUEST["login"]);
	$pass_s	 = trim($_REQUEST["password"]);
    if (existe_usuario($login_s,$pass_s)) {
        header("Location: cat.php?cat=0");
        exit();
    }
    // usuario inexistente sacamos error y punto
    $page->addvar('listado', 'loginerror', 'Usuario o contraseña incorrecta');
    $page->addVar('headbar', 'usuario_logueado', '');
}

$page->setAttribute( 'listado', 'src', 'login.html' );
$page->setAttribute('page','cat',$cat_s);

if ( getSesionDato("eslogueado") ){
    //$page->addRows('listadonovedades',getRowsNovedades() );
}


$esadmin="oculto";
if (getSesionDato("esadmin")){
	$esadmin="";
}

$page->addVar("listado","esadmin",$esadmin);      
$page->setAttribute( 'menu', 'src', 'menu.html' );

$page->Volcar();
