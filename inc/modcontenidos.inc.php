<?php


if(!defined("MODCONTENIDOSINC")){

    define("MODCONTENIDOSINC",true);

    function local_documento_compartido_con($id_documento) {
        //$sql = parametros("SELECT id_permiso, id_usuario_permitido, id_grupo_permitido FROM permisosdocumentos WHERE id_documento_compartido = %d", $id_documento);
        $sql= parametros("SELECT id_permiso, id_usuario_permitido, id_grupo_permitido, usuarios.nombreusuario, grupos.nombregrupo FROM permisosdocumentos "
            . " LEFT JOIN usuarios ON id_usuario_permitido = id_usuario left join grupos ON id_grupo_permitido = id_grupo WHERE id_documento_compartido = %d"
            . " ORDER BY nombregrupo,  nombreusuario",$id_documento);
        //d("busco permisos documentos".$sql);
        $res = query($sql);
        $compartido = array();
        $grppt = "";
        //$grppt = "<input type='image' src='img/verde/compartir-medio.png' title='Compartido con...' class='enlinea' onclick='return popup($id_documento,0)'/>";
        //$grppt = $grppt. "<div id=compartidocon$id_documento>";
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

            $grppt = $grppt . "<div class='etiqueta-grupo js-etiq " . $tipoclass . "' id=etiqueta" . $rowgroup["id_permiso"] . ">";
            $grppt = $grppt . "<input type='hidden' name= 'id_usrgrp' value=" . $id . " />";
            $grppt = $grppt . "<input type='hidden' name= 'tipo_usrgrp' value=" . $tipo . " />";
            $grppt = $grppt . "<input type='image' src='img/etiqueta-x.png' title='Eliminar' onclick='return no_compartido(" . $rowgroup["id_permiso"] . ",\"" . $nombre . "\")'/>";
            $grppt = $grppt . "<span>" . $nombre . "</span>";
            $grppt = $grppt . "</div>";
        }
        $grppt = $grppt . "</div>";
        return $grppt;
    }

    function getRandomDir(){
        $key = rand();

        $tiny = base_convert($key, 10, 36);

        $len = strlen($tiny);
        $out = array();
        $out[] = date("Y");

        for($t=0;$t<$len;$t++){
            $out []= $tiny[$t] ;
        }

        $dir = implode($out,"/") . "/";

        $salida = array("dirtxt"=>$dir, "dirs"=>$out);

        return $salida;
    }


    function createPath($path="upload/",$dirs){

        $dircreate = $path;
        foreach ($dirs as $dir){

            $dircreate .= $dir . "/";
            $cmd = "mkdir -p $dircreate ";
            error_log("cmd:" . $cmd);
            system($cmd);
        }

    }

}





