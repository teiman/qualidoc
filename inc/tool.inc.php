<?php





function getRowsNovedades($modo = "minilista") {
    global $cat_s;
    $id_categoria_s = sql($categoria);

    if ($modo == "minilista") {
        $lim = 4;
    } else {
        $lim = 15;
    }
    $idusrsesion = 0;
    $rows = array();
    //$sql = "SELECT * FROM contenidos WHERE eliminado=0 ORDER BY id_contenido DESC, nombre_contenido ASC LIMIT $lim";//TODO: num novedades parametrizable
    if (getSesionDato("eslogueado")) {
        $idusrsesion = getSesionDato("id_usuario");
        $sql = "(SELECT contenidos.nombre_contenido as nombrecontenido, fecha_creacion, contenidos.descripcion, usuarios.nombreusuario, contenidos.tipo, contenidos.id_contenido,"
                . "  categorias.nombre as nombrecategoria, id_documento_compartido FROM `permisosdocumentos` INNER JOIN grupos ON grupos.id_grupo = permisosdocumentos.id_grupo_permitido"
                . " INNER JOIN gruposelementos ON grupos.id_grupo = gruposelementos.id_grupo INNER JOIN contenidos ON"
                . " contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
                . " INNER JOIN categorias ON contenidos.id_categoria = categorias.id_categoria"
                . " INNER JOIN usuarios ON contenidos.id_propietario = usuarios.id_usuario"
                . " WHERE gruposelementos.id_usuario = %d AND contenidos.id_propietario <> %d"
                . " AND contenidos.eliminado = 0)"
                . " UNION (SELECT contenidos.nombre_contenido as nombrecontenido, fecha_creacion, contenidos.descripcion, usuarios.nombreusuario, contenidos.tipo, contenidos.id_contenido,"
                . "  categorias.nombre as nombrecategoria,id_documento_compartido FROM permisosdocumentos INNER JOIN contenidos ON"
                . " contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
                . " INNER JOIN categorias ON contenidos.id_categoria = categorias.id_categoria"
                . " INNER JOIN  usuarios ON contenidos.id_propietario = usuarios.id_usuario"
                . " WHERE id_usuario_permitido = %d AND contenidos.id_propietario <> %d"
                . " AND contenidos.eliminado = 0) ORDER BY fecha_creacion DESC LIMIT %d ";
        $sql = parametros($sql, $idusrsesion, $idusrsesion, $idusrsesion, $idusrsesion, $lim);
        $res = query($sql);
        while ($row = Row($res)) {
            //CSSDESCARGAR
            if ($row["tipo"] == "html")
                $row["cssdescargar"] = "ocultar";
            else
                $row["cssdescargar2"] = "ocultar";

            $rows[] = $row;
        }
    }
    return $rows;
}


function getMigasdePan($cat){

	$cat_s = sql(CleanID($cat));

	$rows = array($row);

	if(0){
		$row = queryrow("SELECT * FROM categorias WHERE id_categoria='$cat_s'");

		$id_padre = $row["id_padre"];

		if (!$id_padre)
			return array($row);		
	}
	
	$id_padre = $cat_s;


	while($row = queryrow("SELECT * FROM categorias WHERE id_categoria='$id_padre'")){

		array_unshift($rows,$row);

		$id_padre = $row["id_padre"];
		$id_categoria = $row["id_categoria"];

		if(!$id_categoria)
			break;
	}

	$row = queryrow("SELECT * FROM categorias WHERE id_categoria='0' and Eliminado='0' ");
	array_unshift($rows,$row);


	$rowsclean = array();
	foreach($rows as $key=>$datos){
		if($datos)
			$rowsclean[] =$datos;
	}
	
	return $rowsclean;
}




?>