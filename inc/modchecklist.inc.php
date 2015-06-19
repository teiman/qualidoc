<?php



/**
 * @param $id_documento int
 * @return string html
 */
function documento_compartido_con($id_documento) {
    $sql = parametros("SELECT id_permiso, id_usuario_permitido, id_grupo_permitido, usuarios.nombreusuario, grupos.nombregrupo FROM permisosdocumentos "
        . " LEFT JOIN usuarios ON id_usuario_permitido = id_usuario left join grupos ON id_grupo_permitido = id_grupo WHERE id_documento_compartido = %d"
        . " ORDER BY nombregrupo,  nombreusuario", $id_documento);

    error_log($sql);

    $res = query($sql);
    $compartido = array();
    $grppt = "<!-- modchecklist.inc.php:documento_compartido_con -->";

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
        $id_permiso = $rowgroup["id_permiso"];

        $compartido[] = array("id_permiso" => $rowgroup["id_permiso"], "tipo" => $tipo, "id" => $id, "accion" => "");

        $grppt = $grppt . "<div class='etiqueta-grupo js-etiq " . $tipoclass . "' id='etiqueta" . $id_permiso . "'>";
        $grppt = $grppt . "<input type='hidden' name= 'id_usrgrp' value=" . $id . " />";
        $grppt = $grppt . "<input type='hidden' name= 'tipo_usrgrp' value=" . $tipo . " />";
        $grppt = $grppt . "<input type='image' src='img/etiqueta-x.png' data-id_permiso='".$id_permiso."'  title='Eliminar' onclick='return no_compartido(" . $id_permiso . ",\"" . html($nombre) . "\")'/>";
        $grppt = $grppt . "<span>" . html($nombre) . "</span>";
        $grppt = $grppt . "</div>";
    }

    $grppt = $grppt . "</div><!-- /modchecklist.inc.php:documento_compartido_con -->";
    return $grppt;
}



/**
 * renderiza una pregunta
 * @param $id_pregunta int
 * @param $tipo int
 * @return string html
 */
function html_valores_pregunta($id_pregunta, $tipo) {
    if ($tipo == TIPO_TEXTO) {
        $valores = "<div>(Comentario)</div>";
        return $valores;
    }

    /*
    if($tipo == TIPO_EXCLUSIVO){
        $limit = " LIMIT 2 ";
    }*/


    // valores de la pregunta
    $sql = parametros("SELECT id_pregunta, id_valor_posible, texto, valor FROM listados_valores_posibles WHERE id_pregunta = %d ORDER BY id_valor_posible ASC", $id_pregunta);
    $resv = query($sql);

    $valores = "<div>";

    while ($row = Row($resv)) {

        if ($tipo == 2) {
            $tipoclass = "js-usuario";
        } else {
            $tipoclass = "js-grupo";
        }

        $valores = $valores . "<div class='etiqueta-grupo js-etiq " . $tipoclass . "' id='" . $row["id_valor_posible"] . "'>";
        $valores = $valores . "<input type='hidden' name= 'id_pregunta' value='" . $row["id_valor_posible"] . "' />";
        $valores = $valores . "<input type='hidden' name= 'tipo_usrgrp' value='" . $tipo . "' />";
        //$valores = $valores . "<input type='image' src='img/etiqueta-x.png' title='Eliminar' onclick='return eliminar_valor_respuesta(" . $row["id_valor_posible"] .")'/>";
        $valores = $valores . "<span>" . html($row["texto"]) . "</span>";
        $valores = $valores . "</div>";
    }
    $valores = $valores . "</div>";
    return $valores;
}


/**
 * renderiza preguntas de un grupo
 * @param $id_grupo int
 * @return string html
 */
function html_cargar_preguntas($id_grupo) {
// cargamos preguntas del grupo

    $respuestas = "<table>";
    $sql = parametros("SELECT id_pregunta, id_grupo, id_modo_valoracion, posicion, pregunta FROM listados_preguntas WHERE id_grupo = %d ORDER BY posicion", $id_grupo);
    $res = query($sql);
    $x = 0;

    while ($row = Row($res)) {
        $x++;

        if (($x % 2) == 0) {
            $pi = "odd";
        } else {
            $pi = "even";
        }

        $id_pregunta = $row["id_pregunta"];
        $tipo = $row["id_modo_valoracion"];


        $respuestas .= "<tr class='blkpregunta".$id_pregunta."'>"
            . "<td width='100' class='colbtn'>"
            . "<input id='btn_editar_".$id_pregunta."' type='image' src='img/verde/editar1.png' title='Editar pregunta' data-tipo='".$row["id_modo_valoracion"]."' onclick='return editar_pregunta(" .$row["id_pregunta"]. ")' />"
            . "<input type='image' src='img/basura1.png' title='Eliminar pregunta' onclick='return Listas.borrarPregunta_ui(" .$row["id_pregunta"]. "," .$id_grupo. ")'/>"
            . "</td>"
            . "<td><span id='spanpregunta" . $id_pregunta . "'>" . $row["pregunta"] . "</span><input type='text' class='oculto pregunta-larga' id='textpregunta" . $id_pregunta . "' value='" . html($row["pregunta"]) . "'></td>"
            . "<td><span id='spanposicionpregunta" . $id_pregunta . "'>" . $row["posicion"] . "</span><input type='text' class='oculto pospreguntagrp".$id_grupo."' id='textposicionpregunta" . $id_pregunta . "' value='" . $row["posicion"] . "'></td>"
            . "<td>" . html_valores_pregunta($id_pregunta, $tipo) . "</td>"
            . "</tr>"
            // adjunto 1 lineas para edicion;
            . "<tr  class='oculto' id='linea_valores" . $id_pregunta . "'>"
            . "<td colspan='4'><div id='div_linea_valores" . $id_pregunta . "'></div></td></tr>";
    }

    $respuestas = $respuestas . "</table>";
    return $respuestas;
}