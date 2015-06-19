<?php

include_once(__ROOT__ . "/class/contenido_checklist.class.php");

$page->setAttribute( 'listado', 'src', 'visor_checklist.html' );

$con = new contenido_checklist();
$con->Load($id_contenido_s);


$page->addVar("listado","nombre_checklist",$con->get("nombre_contenido"));
$page->addVar("listado","id_contenido",$con->get("id_contenido"));
$page->addVar("listado","id_listado",$con->get("id_listado"));

$id_listado = $con->get("id_listado");

$sql = parametros("SELECT * FROM listados_grupos WHERE id_listado=%d ORDER BY posicion ASC",$id_listado);


$lineas = array();

$res = query($sql);

while($row = Row($res)){
    $lineas[] = $row;
}


$page->addRows("listadogrupos",$lineas);




