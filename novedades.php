<?php

include("tool.php");

if ( !getSesionDato("eslogueado") ){
	header("Location: conexioncerrada.php");
	exit();
}


$page->setAttribute( 'listado', 'src', 'novedades.html' );


$filas = getRowsNovedades("todas");


if(!$filas)
    $filas = array( array("css_fila"=>"oculto"));

$page->addRows('listadonovedades2', $filas );


$page->addRows('listadonovedades',getRowsNovedades() );



$esadmin="oculto";
if (getSesionDato("esadmin")){
	$esadmin="";
}


$page->addVar("listado","esadmin",$esadmin);
$page->setAttribute( 'menu', 'src', 'menu.html' );


$page->addVar("page","ocultamigas","ocultar");
$page->addVar('headbar', 'usuario_logueado', getSesionDato("nombreusuario"));
$page->Volcar();



