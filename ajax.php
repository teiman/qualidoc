<?php


include("tool.php");
include_once(__ROOT__ . "/class/patErrorManager.php");
include_once(__ROOT__ . "/class/patTemplate.php");


$modo = $_REQUEST["modo"];

$out = array("ok"=>true);//necesario dando ok a true, el resto del codigo lo da por hecho

switch($modo){

    case "guardar_checklist_relleno":
    {

        include_once(__ROOT__ . "/class/contenido.class.php");
        /*data: {
        modo:"guardar_checklist_relleno",
                    id_listado: Pagina.id_listado,
                    json_data: JSON.stringify(Pagina.datos_enviar),
                    html_checklist: Pagina.html_enviar
                },*/

        $con = new contenido();

        $id_listado = $_REQUEST["id_listado"];
        $datoslistado = queryrow(parametros("SELECT * FROM listados WHERE id_listado=%d",$id_listado));

        $nombre_contenido = $datoslistado["listado"];

        $id_contenido = $_REQUEST["id_contenido"];
        $datoscontenido = queryrow(parametros("SELECT * FROM contenidos WHERE id_contenido=%d",$id_contenido));

        $id_categoria_guardar = $datoslistado["id_categoria_destino"];

        $con->set("nombre_contenido", "Completado: ".$nombre_contenido );
        $con->set("descripcion","<p>Checklist ". html($nombre_contenido) . " rellenado por ". html(getSesionDato("nombreusuario"))."<p>");
        $con->set("tipo", "html");
        $con->set("id_categoria", $id_categoria_guardar);
        $con->getRandomArticle();

        $idusuario = getSesionDato("id_usuario");
        $con->set("id_propietario", $idusuario);
        $con->set("id_listado",$id_listado);
        $con->set("fecha_creacion", date("d/m/y"));
        $con->Alta();

        $id_contenido = $con->get("id_contenido");


        $out["id_contenido"] = $id_contenido;

        $datos = $con->guardarResultado($_REQUEST["json_data"],$_REQUEST["puntos"]);


        $extra = "<h2>". html($nombre_contenido) . "</h2>";

        if($datos["es_conforme"]){
            $extra = "<p class='resultados puntuacion-conforme'>Este checklist es conforme con un total de: ".($datos["puntos"]) . " puntos</p>";
        } else {
            $extra = "<p class='resultados puntuacion-no-conforme'>Este checklist no es conforme, obtuvo puntuación insuficiente: " . ($datos["puntos"]).  " puntos<p>";
        }

        $extra =  $extra . "<hr class='separador-conformidad'/>";

        $con->setContenido($extra . $_REQUEST["html_checklist"] . "<p class='autor-rellenado'>Rellenado por ".html(getSesionDato("nombreusuario"))."</p>");

        echo json_encode($out);
        break;
    }

    //data: {modo:"guardarcambiosgrupo","id_grupo":id_grupo,"texto":texto,"posicion":posicion},
    case "guardarcambiosgrupo":
    {
        $id_grupo = $_REQUEST["id_grupo"];
        $texto = $_REQUEST["texto"];
        $posicion = $_REQUEST["posicion"];

        $sql = parametros("UPDATE listados_grupos SET grupo='%s',posicion=%d WHERE id_grupo=%d",$texto,$posicion,$id_grupo);
        query($sql);

        echo json_encode($out);
        break;
    }


    //data: {modo:"nuevovalor","id_pregunta":id_pregunta,"valor":valortext,"posicion":newposicion},
    case "nuevovalor":
    {
        $id_pregunta = $_REQUEST["id_pregunta"];
        $texto = $_REQUEST["texto"];
        $valor = $_REQUEST["valor"];

        $sql = parametros("INSERT INTO listados_valores_posibles(id_pregunta,texto,valor) VALUES(%d,'%s','%s')",$id_pregunta,$texto,$valor);
        query($sql);
        $id_valor_posible = $UltimaInsercion;
        $out["id_valor_posible"] = $id_valor_posible;


        $row = queryrow(parametros("SELECT *FROM listados_valores_posibles WHERE id_valor_posible = %d",$id_valor_posible));

        $out["html"] = patgenerator::filadetemplate2("subsolapa_edicion_pregunta.html","listado_valores",array($row));

        echo json_encode($out);
        break;
    }

    //data: {modo:"guardarcambiospregunta","id_pregunta":id_pregunta,"pregunta":textpregunta,"posicion":textposicionpregunta},
    case "guardarcambiospregunta":
    {
        $id_pregunta = $_REQUEST["id_pregunta"];
        $pregunta = $_REQUEST["pregunta"];
        $posicion = $_REQUEST["posicion"]*1;
        $tipopregunta = $_REQUEST["tipopregunta"]*1;

        $sql = parametros("UPDATE listados_preguntas SET pregunta='%s', posicion=%d, id_modo_valoracion=%d WHERE id_pregunta=%d",$pregunta,$posicion,$tipopregunta,$id_pregunta);
        query($sql);

        $out["sql"] = $sql;
        $out["id_pregunta"] = $id_pregunta;


        //jsondatos:[{"texto":"e1","valor":"","idpos":"valor6"},{"texto":"e2","valor":"0","idpos":"valor7"},{"texto":"e3","valor":"0","idpos":"valor8"},{"texto":"e4","valor":"0","idpos":"valor9"},{"texto":"e5","valor":"0","idpos":"valor10"}]

        $valores = json_decode($_REQUEST["jsondatos"],$arraryasociativo=true);

        $num = 0;

        query(parametros("DELETE FROM listados_valores_posibles WHERE id_pregunta=%d",$id_pregunta));

        foreach($valores as $item){
            $num++;
            query(parametros("INSERT INTO listados_valores_posibles (id_pregunta,texto,valor) VALUES (%d,'%s',%d)",$id_pregunta,$item["texto"],$item["valor"]));
        }

        $out["lineas_actualizadas"] = $num;

        echo json_encode($out);
        break;
    }

    //data: {modo:"eliminarpregunta","id_pregunta":id_pregunta},
    case "eliminarpregunta":
    {
        $id_pregunta = $_REQUEST["id_pregunta"];

        $sql = parametros("DELETE FROM listados_preguntas WHERE id_pregunta=%d",$id_pregunta);
        query($sql);

        echo json_encode($out);
        break;
    }

    //data: {modo:"crearpregunta","id_listado":Listas.id_listado,"nombre":nombre,"posicion":posicion,"id_grupo":id_grupo},

    case "crearpregunta":
    {
        $id_grupo = $_REQUEST["id_grupo"];
        $posicion = $_REQUEST["posicion"];
        $id_modo_valoracion = 1;
        $pregunta = $_REQUEST["nombre"];

        $sql = parametros("INSERT listados_preguntas (pregunta,id_grupo,posicion,obligatorio,id_modo_valoracion) "
            ." VALUES ('%s',%d,%d,1,%d) ",$pregunta,$id_grupo,$posicion,$id_modo_valoracion);
        query($sql);
        $id_pregunta = $UltimaInsercion;

        $out["id_pregunta"] = $id_pregunta;
        echo json_encode($out);
        break;
    }


    case "eliminargrupo":
    {
        $id_grupo = $_REQUEST["id_grupo"];

        $sql = parametros("DELETE FROM listados_grupos WHERE id_grupo=%d",$id_grupo);
        query($sql);

        $sql = parametros("DELETE FROM listados_preguntas WHERE id_grupo=%d",$id_grupo);
        query($sql);

        echo json_encode($out);
        break;
    }

    case "creargrupo":
    {
        $nombre = $_REQUEST["nombre"];
        $id_listado = $_REQUEST["id_listado"];
        $posicion = $_REQUEST["posicion"];

        $sql = parametros("INSERT listados_grupos (grupo,id_listado,posicion) VALUES ('%s',%d,%d) ",$nombre,$id_listado,$posicion);
        query($sql);

        $id_grupo = $UltimaInsercion;
        $out["id_grupo"] = $id_grupo;

        echo json_encode($out);
        break;
    }
}