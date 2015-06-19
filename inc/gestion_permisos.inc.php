<?php


/**
 * @param $compartidos
 * @param $id_documento int
 */
function actualizar_permisos($compartidos, $id_documento) {
    // ahora añadimos los usuarios y grupos con los que se comparte
    //$compar = var_export($compartidos, true);

    $sql = parametros("DELETE FROM permisosdocumentos WHERE id_documento_compartido = %d and id_categoria_compartida = 0", $id_documento);

    query($sql);

    foreach ($compartidos as $valores) {
        if ($valores["tipo_elemento"] == 0) {
            $idusr = $valores["id_elemento"];
            $idgrp = 0;
        } else {
            $idusr = 0;
            $idgrp = $valores["id_elemento"];
        }

        $sql = "INSERT INTO permisosdocumentos (id_documento_compartido, id_categoria_compartida, id_usuario_permitido, id_grupo_permitido) VALUES (%d,0,%d,%d)";
        $sql = parametros($sql, $id_documento, $idusr, $idgrp);

        //error_log("actualizar_permisos:".$sql);
        query($sql);
    }

}

