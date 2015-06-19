<?php


include("tool.php");


if ( !getSesionDato("eslogueado") ){
	header("Location: conexioncerrada.php");
	exit();
}


$page->setAttribute( 'listado', 'src', 'buscador.html' );


$query = $_REQUEST["query"];



/*
SELECT contenidos.id_contenido AS id_contenido, id_categoria, nombre, descripcion, path_archivo
FROM contenidos
WHERE contenidos.eliminado =0
AND MATCH (
nombre
)
AGAINST (
'texto a buscar' WITH QUERY EXPANSION
)
UNION

SELECT contenidos.id_contenido AS id_contenido, id_categoria, nombre, descripcion, path_archivo
FROM contenidos
WHERE contenidos.eliminado =0
AND MATCH (
descripcion
)
AGAINST (
'texto a buscar' WITH QUERY EXPANSION
)

UNION

SELECT contenidos.id_contenido as id_contenido, id_categoria, nombre, descripcion, path_archivo


FROM contenidos INNER JOIN indice ON contenidos.id_contenido = indice.id_contenido

WHERE  contenidos.eliminado=0 AND
MATCH(texto) AGAINST('texto a buscar' WITH QUERY EXPANSION)
*/


$query_s = sql(trim($query));

$sql = "SELECT contenidos.id_contenido AS id_contenido, id_categoria, nombre, descripcion, path_archivo
	FROM contenidos
	WHERE contenidos.eliminado =0
	AND MATCH (
	nombre
	)
	AGAINST (
	'$query_s'
	WITH QUERY EXPANSION
	)
	UNION
	SELECT contenidos.id_contenido AS id_contenido, id_categoria, nombre, descripcion, path_archivo
	FROM contenidos
	WHERE contenidos.eliminado =0
	AND MATCH (
	descripcion
	)
	AGAINST (
	'$query_s'
	WITH QUERY EXPANSION
	)
	UNION
	SELECT contenidos.id_contenido AS id_contenido, id_categoria, nombre, descripcion, path_archivo
	FROM contenidos
	INNER JOIN indice ON contenidos.id_contenido = indice.id_contenido
	WHERE contenidos.eliminado =0
	AND
	MATCH (
	texto
	)
	AGAINST (
	'$query_s'
	WITH QUERY EXPANSION
	)";



$res = query($sql);


if (!$FilasAfectadas){
	$sql = "SELECT contenidos.id_contenido AS id_contenido, id_categoria, nombre, descripcion, path_archivo
	FROM contenidos LEFT JOIN indice ON contenidos.id_contenido = indice.id_contenido
	WHERE  contenidos.nombre_contenido LIKE '%$query_s%' OR contenidos.descripcion LIKE '%$query_s%'
    OR  indice.texto LIKE '%$query_s%' GROUP BY contenidos.id_contenido";
	$res = query($sql);
}



$rows = array();
while( $row = Row($res) ){
	$rows[] = $row;
}



$page->addRows('listabusqueda', $rows );

//$page->addRows('listadonovedades',getRowsNovedades() );



$esadmin="oculto";
if (getSesionDato("esadmin")){
	$esadmin="";
} 
$page->addVar("listado","esadmin",$esadmin);
$page->setAttribute( 'menu', 'src', 'menu.html' );



$page->addVar("page","ocultamigas","ocultar");

$page->Volcar();


?>