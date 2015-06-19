<?php



function genCategoriasSubCategoria($padrecat) {
    global $usresadmin;
    global $iduser;
    $text = "";
    if ($usresadmin) {
        $sql = "SELECT id_categoria, posicion, nombre, eshoja  FROM categorias WHERE id_padre = %d and eliminado='0' ORDER BY posicion asc, nombre ASC "; //categoria actual
        $sql = parametros($sql, $padrecat);
    } else {
        $sql = "(SELECT categorias.nombre, categorias.eshoja, categorias.posicion, id_categoria_compartida as id_categoria FROM permisosdocumentos INNER JOIN grupos ON grupos.id_grupo = permisosdocumentos.id_grupo_permitido"
            . " INNER JOIN gruposelementos ON grupos.id_grupo = gruposelementos.id_grupo INNER JOIN categorias ON id_categoria_compartida = categorias.id_categoria "
            . " WHERE id_usuario = %d AND categorias.id_padre =%s"
            . " ) UNION (SELECT categorias.nombre, categorias.eshoja, categorias.posicion, id_categoria_compartida as id_categoria FROM permisosdocumentos INNER JOIN categorias ON id_categoria_compartida = categorias.id_categoria"
            . " WHERE id_usuario_permitido = %d AND categorias.id_padre =%s) ORDER BY posicion, nombre";
        $sql = parametros($sql, $iduser, $padrecat, $iduser, $padrecat);
    }

    $res = query($sql);

    $text.= "<ul class='listacategorias'>";
    while ($row = Row($res)) {

        $nombre = $row["nombre"];
        $id = $row["id_categoria"];

        $text.= "<li class='categoria'><a class='subcategoria' href='cat.php?cat=$id'>" . html($nombre) . "</a></li>";
    }
    $text.= "</ul>";
    return $text;
}

function genSubCategoria($padrecat, $eshoja = false) {

    global $cat_s;

    if (!$eshoja)
        return genCategoriasSubCategoria($padrecat);


    $id_categoria_s = sql($padrecat);
    $idusrsesion = getSesionDato("id_usuario");
    $text = "";

    $sql = "SELECT contenidos.descripcion, usuarios.nombreusuario as nombre_usuario, contenidos.tipo, contenidos.id_contenido,"
        . " contenidos.fecha_creacion, contenidos.path_archivo, id_documento_compartido FROM permisosdocumentos"
        . " INNER JOIN grupos ON grupos.id_grupo = permisosdocumentos.id_grupo_permitido"
        . " INNER JOIN gruposelementos ON grupos.id_grupo = gruposelementos.id_grupo"
        . " INNER JOIN contenidos ON contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
        . " INNER JOIN usuarios ON contenidos.id_propietario = usuarios.id_usuario"
        . " WHERE (gruposelementos.id_usuario = %d OR contenidos.id_propietario = %d) AND contenidos.id_categoria = %d"
        . " AND contenidos.eliminado = 0"
        . " UNION SELECT contenidos.descripcion, usuarios.nombreusuario as nombre_usuario, contenidos.tipo, contenidos.id_contenido,"
        . " contenidos.fecha_creacion, contenidos.path_archivo, id_documento_compartido FROM permisosdocumentos"
        . " INNER JOIN contenidos ON contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
        . " INNER JOIN  usuarios ON contenidos.id_propietario = usuarios.id_usuario"
        . " WHERE (id_usuario_permitido = %d OR contenidos.id_propietario = %d) AND contenidos.id_categoria= %d"
        . " AND contenidos.eliminado = 0";

    $sql = parametros($sql, $idusrsesion, $idusrsesion, $id_categoria_s, $idusrsesion, $idusrsesion, $id_categoria_s);
    error_log($sql);

    //$sql = parametros($sql,$idusrsesion, $id_categoria_s, $idusrsesion, $id_categoria_s);
    $res = query($sql);


    $cuantos_s = mysql_num_rows($res); //intval($row["cuantos"]);

    if (!$cuantos_s) {
        $text = "<ul class='listacategorias'>" . "<li class='categoria sinficheros'>No contiene ficheros</li>" . "</ul>";
        return $text;
    }

    $text = "<ul class='listacategorias'>" . "<li class='categoria conficheros'><a  class='subcategoria' href='cat.php?cat=$id_categoria_s&amp;modo=listaficheros'>Entra aquí ($cuantos_s)</a></li>" . "</ul>";

    return $text;
}

function documento_compartido_con($id_documento) {

    //$sql = parametros("SELECT id_permiso, id_usuario_permitido, id_grupo_permitido FROM permisosdocumentos WHERE id_documento_compartido = %d", $id_documento);
    $sql= parametros("SELECT id_permiso, id_usuario_permitido, id_grupo_permitido, usuarios.nombreusuario, grupos.nombregrupo FROM permisosdocumentos "
        . " LEFT JOIN usuarios ON id_usuario_permitido = id_usuario left join grupos ON id_grupo_permitido = id_grupo WHERE id_documento_compartido = %d ORDER BY nombregrupo,  nombreusuario ",$id_documento);

    //d("busco permisos documentos".$sql);

    $res = query($sql);
    $compartido = array();

    $grppt = "<!-- documento_compartido_con/cat.php -->";
    $grppt .= "<input type='image' src='img/verde/compartir-medio.png' title='Compartido con...' class='enlinea' onclick='return popup($id_documento,0)'/>";
    $grppt .= "<div id=compartidocon$id_documento>";
    while ($rowgroup = Row($res)) {
        if ($rowgroup["id_usuario_permitido"]) {
            $tipo = "0";
            $tipoclass = "js-usuario";
            $id = $rowgroup["id_usuario_permitido"];
            $nombre = nombre_usuario($id);
        } else {
            $tipo = "1";
            $tipoclass = "js-grupo";
            $id = $rowgroup["id_grupo_permitido"];
            $nombre = nombre_grupo($id);
        }
        $compartido[] = array("id_permiso" => $rowgroup["id_permiso"], "tipo" => $tipo, "id" => $id, "accion" => "");
        //

        $grppt = $grppt . "<div class='etiqueta-grupo js-etiq$id_documento " . $tipoclass . "' id=etiqueta" . $rowgroup["id_permiso"] . ">";
        $grppt = $grppt . "<input type='hidden' name= 'id_usrgrp' value=" . $id . " />";
        $grppt = $grppt . "<input type='hidden' name= 'tipo_usrgrp' value=" . $tipo . " />";
        $grppt = $grppt . "<input type='image' src='img/etiqueta-x.png' title='Eliminar' onclick='return no_compartido(" . $rowgroup["id_permiso"] . ",\"" . $nombre . "\")'/>";
        $grppt = $grppt . "<span>" . $nombre . "</span>";
        $grppt = $grppt . "</div>";
    }
    $grppt = $grppt . "</div><!-- documento_compartido_con/cat.php -->";
    return $grppt;
}

function getListaFicheros($categoria) {
    global $cat_s;

    $text = "";

    $id_categoria_s = sql($categoria);

    $sql = "SELECT * FROM contenidos WHERE id_categoria ='$id_categoria_s' AND eliminado=0 ORDER BY id_contenido DESC, nombre_contenido ASC ";

    $res = query($sql);

    $text.= "<ul class='listacategorias'>";
    while ($row = Row($res)) {
        $nombre = $row["nombre_contenido"];
        $id = $row["id_contenido"];
        $tipo = "Documento de " . $row["tipo"];
        $propietario = "Propietario" . $row["nombre_propietario"] . " Fecha:" . $row["fecha_creacion"];
        $text.= "<li class='propietario'><a>html(" . $propietario . ")</a></li>";
        $text.= "<li class='categoria'><a title='" . html($tipo) . "' class='subcategoria' href='modvisor.php?id_contenido=$id&amp;cat=$cat_s'>" . html($nombre) . "</a></li>";
    }
    $text.= "</ul>";
    return $text;
}

function getRowsFicheros($categoria) {
    global $cat_s;
    $id_categoria_s = $categoria;
    $administrador = getSesionDato("esadmin");
    $usractivo = getSesionDato("id_usuario");

    if ($administrador ){
        $sql = parametros("SELECT contenidos.nombre_contenido, contenidos.descripcion, contenidos.tipo, contenidos.id_contenido, usuarios.nombreusuario as nombre_usuario,"
            . " contenidos.fecha_creacion, contenidos.path_archivo,  contenidos.id_propietario FROM contenidos"
            . " INNER JOIN usuarios ON contenidos.id_propietario = usuarios.id_usuario"
            . " WHERE id_categoria =%d AND contenidos.eliminado=0 ORDER BY id_contenido DESC ",$id_categoria_s);

    }else{
        $sql = "(SELECT contenidos.nombre_contenido, contenidos.descripcion, usuarios.nombreusuario as nombre_usuario, contenidos.tipo, contenidos.id_contenido,"
            . " contenidos.fecha_creacion, contenidos.path_archivo, id_documento_compartido, contenidos.id_propietario FROM permisosdocumentos"
            . " INNER JOIN grupos ON grupos.id_grupo = permisosdocumentos.id_grupo_permitido"
            . " INNER JOIN gruposelementos ON grupos.id_grupo = gruposelementos.id_grupo"
            . " INNER JOIN contenidos ON contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
            . " INNER JOIN usuarios ON contenidos.id_propietario = usuarios.id_usuario"
            . " WHERE (gruposelementos.id_usuario = %d OR contenidos.id_propietario = %d) AND contenidos.id_categoria = %d"
            . " AND contenidos.eliminado = 0)"
            . " UNION (SELECT contenidos.nombre_contenido, contenidos.descripcion, usuarios.nombreusuario as nombre_usuario, contenidos.tipo, contenidos.id_contenido,"
            . " contenidos.fecha_creacion, contenidos.path_archivo, id_documento_compartido, contenidos.id_propietario FROM permisosdocumentos"
            . " INNER JOIN contenidos ON contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
            . " INNER JOIN  usuarios ON contenidos.id_propietario = usuarios.id_usuario"
            . " WHERE (id_usuario_permitido = %d OR contenidos.id_propietario = %d) AND contenidos.id_categoria= %d"
            . " AND contenidos.eliminado = 0)ORDER BY fecha_creacion DESC";
        $sql = parametros($sql, $usractivo, $usractivo, $id_categoria_s, $usractivo, $usractivo, $id_categoria_s);
    }

    $res = query($sql);
    $rows = array();
    while ($row = Row($res)) {
        //CSSDESCARGAR
        if ($row["tipo"] == "html") {
            $row["cssdescargar"] = "ocultar";
        } else {
            $row["cssdescargar2"] = "ocultar";
        }
        if ($administrador || $row["id_propietario"] == $usractivo) {
            $row["compartidocon"] = documento_compartido_con($row["id_contenido"]);
        } else {
            $row["compartidocon"] = "<div class='etiqueta-grupo js-etiq oculto'></div>";
        }
        //$row["nombre_usuario"] = nombre_usuario($row["id_propietario"]);
        $rows[] = $row;
    }
    return $rows;
}