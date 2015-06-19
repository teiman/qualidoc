<?php


die("Obsoleto");

include("tool.php");


$con = new contenido();

$con->set("path_archivo","/home/oscar/www/qualidoc/ejemplos/prueba.pdf");
$con->set("path_archivo","/home/oscar/www/qualidoc/ejemplos/p.ps");
$con->set("path_archivo","/home/oscar/www/qualidoc/ejemplos/prueba.doc");
$con->set("path_archivo","/home/oscar/www/qualidoc/ejemplos/ejemplo2.html");

$con->Alta();

$id_contenido = $con->get("id_contenido");


$con->Indexar();


$sql = "SELECT * FROM indice WHERE id_contenido='$id_contenido'";
$row = queryrow($sql);


echo "<xmp>($id_contenido)";
echo var_dump($row);
echo "</xmp>";




?>