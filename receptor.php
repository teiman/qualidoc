<?php

include("tool.php");

$dia  = date("YmdH");
$codigo = hash("crc32",$dia . "_SALT_");

$it = intval($_REQUEST["it"])+1;


$c = $_REQUEST["c"];

if ( $c == $codigo){
	setSesionDato("eslogueado",1);

	header("Location: cat.php?cat=0");
	exit();

} else {
	//echo "c[$c] != codigo[$codigo]";
	sleep(rand()%10);
	header("Location: puente.php?it=".$it);
}





?>