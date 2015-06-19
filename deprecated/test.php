<?php


include("tool.php");





$page    =    &new Pagina();

$page->Inicia( $template["modname"], "admin.html");


$page->setAttribute( 'listado', 'src', 'test.html' );


$page->Volcar();


?>