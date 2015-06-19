<?php




/*
$segundosValida = 15;//cuantos segundos es valida una url

$dia = date("Ymd");
$num = intval((date("His"))/$segundosValida);
$dia .= $num;
*/

$dia  = date("YmdH");

$codigo = hash("crc32",$dia . "_SALT_");


$server = "http://dl41.dinaserver.com/aplicaciones/portal/receptor.php?c=" ;

$server = "receptor.php?c=" ;


$it = intval($_REQUEST["it"])+1;

if ($it>10){
			
	header("Location: portalcaido.php?it=".$it);
	exit();	
}



$url = $server . $codigo . "&it=" . $it;

header("Location: " . $url);



?>