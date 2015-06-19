<?php

include("tool.php");


$modo = $_REQUEST["modo"];
$out = array("ok"=>true);

header("Content-type: application/json");

switch($modo){
    case "preguntasresponder_valores":
    {

        function configuraValores($row,$datospregunta){
            $id_modo_valoracion = $datospregunta["id_modo_valoracion"];

            $row["tipo"] = $id_modo_valoracion;

            switch($id_modo_valoracion){
                case TIPO_TEXTO:
                    $row["css_checkbox"] = "oculto";
                    $row["css_radio"] = "oculto";
                    break;
                case TIPO_EXCLUSIVO:
                    $row["css_checkbox"] = "oculto";
                    $row["css_texto"] = "oculto";
                    break;
                case TIPO_MULTIPLE:
                    $row["css_radio"] = "oculto";
                    $row["css_texto"] = "oculto";
                    break;
                default:
                    $row["css_fila"] = "oculto";
                    break;
            }

            return $row;
        }

        $id_pregunta = $_REQUEST["id_pregunta"];

        $datospregunta = queryrow(parametros("SELECT * FROM listados_preguntas WHERE id_pregunta=%d",$id_pregunta));

        $sql = parametros("SELECT * FROM listados_valores_posibles WHERE id_pregunta=%d ORDER BY id_valor_posible ASC",$id_pregunta);
        $arrdatos = array();

        $num = 0;
        $res = query($sql);
        while($row = Row($res)){
            $arrdatos[] = configuraValores($row,$datospregunta);
            $num++;
        }

        if(!$num){
            $arrdatos[] = array("css_fila"=>"",
                "id_pregunta"=>$datospregunta["id_pregunta"],
                "css_checkbox"=>"oculto",
                "css_radio"=>"oculto"
                );
        }

        $out["sql"] = $sql;

        $out["html"] = patgenerator::filadetemplate("preguntasresponder_valores.snip.html","listadovalores",$arrdatos);

        echo json_encode($out);
        break;
    }


    //modo:preguntasresponder id_grupo:1
    case "preguntasresponder":
    {
        $id_grupo = $_REQUEST["id_grupo"];

        $sql = parametros("SELECT * FROM listados_preguntas WHERE id_grupo=%d ORDER BY posicion ASC",$id_grupo);
        $arrdatos = array();

        $num = 0;
        $res = query($sql);
        while($row = Row($res)){
            $arrdatos[] = $row;
            $num++;
        }

        if(!$num)
            $arrdatos[] = array("css_fila"=>"oculto");

        $out["sql"] = $sql;
        $out["html"] = patgenerator::filadetemplate("preguntasresponder.snip.html","listadopreguntas",$arrdatos);

        echo json_encode($out);
        break;
    }

    //data: {modo:"geninterfacepregunta","id_pregunta":id_pregunta},
    case "geninterfacepregunta":
    {
        $id_pregunta = $_REQUEST["id_pregunta"];

        $sql = parametros("SELECT * FROM listados_valores_posibles WHERE id_pregunta=%d ORDER BY id_valor_posible ASC",$id_pregunta);
        $arrdatos = array();

        $num = 0;
        $res = query($sql);
        while($row = Row($res)){
            $arrdatos[] = $row;
            $num++;
        }

        if(!$num)
            $arrdatos[] = array("css_fila"=>"oculto");

        $out["html"] = patgenerator::filadetemplate("subsolapa_edicion_pregunta.html","listadogrupos",$arrdatos);

        echo json_encode($out);
        break;
    }

    case "preguntaseditar":
    {
        include_once(__ROOT__ . "/inc/busquedas.inc.php");
        include_once(__ROOT__ . "/inc/modchecklist.inc.php");

        $id_grupo = $_REQUEST["id_grupo"];


        $out["html"] = html_cargar_preguntas($id_grupo);

        echo json_encode($out);
        break;
    }

    case "editargrupo":
    {

        $id_grupo = $_REQUEST["id_grupo"];

        $row = queryrow( parametros("SELECT id_grupo, id_listado, grupo, posicion, visible FROM listados_grupos WHERE id_grupo = %d  ",$id_grupo));

        $arrdatos = array();
        $arrdatos[] = array("id_grupo" => $row["id_grupo"], /*"id_listado" => $id_listado,*/ "grupo" => $row["grupo"],
            "posicion" => $row["posicion"],  "visible" => $row["visible"]);


        $out["html"] = patgenerator::filadetemplate("mod_checklist_editar.html","listadogrupos",$arrdatos);

        echo json_encode($out);
        break;
    }
}