<?php


/**
 * @param $id_usuario int
 * @return string text
 */
function nombre_usuario($id_usuario) {
    $nombre = "*** DESCONOCIDO ***";
    $sql = parametros("SELECT nombreusuario FROM usuarios WHERE id_usuario = %d", $id_usuario);
    $res = queryrow($sql);
    if ($res) {
        $nombre = $res["nombreusuario"];
    }
    return $nombre;
}


/**
 * @param $id_grupo int
 * @return string text
 */
function nombre_grupo($id_grupo) {
    $nombre = "*** DESCONOCIDO ***";
    $sql = parametros("SELECT nombregrupo FROM grupos WHERE id_grupo = %d", $id_grupo);
    $res = queryrow($sql);
    if ($res) {
        $nombre = $res["nombregrupo"];
    }
    return $nombre;
}