<?php

/**
 * CheckListCreator:  permite crear checklist
 */

include("tool.php");
include_once(__ROOT__ . "/inc/busquedas.inc.php");
include_once(__ROOT__ . "/inc/modchecklist.inc.php");
include_once(__ROOT__ . "/inc/gestion_permisos.inc.php");


$idusuario = getSesionDato("id_usuario");
$es_administrador = getSesionDato("esadmin");

if (!getSesionDato("eslogueado")) {
    header("Location: conexioncerrada.php");
    exit();
}
if (!getSesionDato("esadmin")) {
    header("Location: conexioncerrada.php");
    exit();
}


//sacamos los menus

$modorenderizado = "";

$modo = $_REQUEST["modo"];


$esadmin = "oculto";
if (getSesionDato("esadmin")) {
    $esadmin = "";
}


$con = new contenido();

switch ($modo) {
    // navegacion paginas
    // los cuatro modos siguientes se utilizan para la busqueda y pagina anterior,siguientes,inicio,fin

    case "filtrar-elemento":
        $filtranombre_s = sql($_REQUEST["filtrar-elemento"]);
        $extracondicion = " AND (" . $campobusqueda . "  LIKE '%$filtranombre_s%') ";
        $modorenderizado = "listar";
        $_SESSION["offset_" . $modulobusqueda] = 0;
        break;

    case "change-list-size":
        $listsize = $_REQUEST["list-size"];
        if ($listsize) {
            $_SESSION[$template["modname"] . "_list_size"] = $listsize;
        }
        $modorenderizado = "listar";
        break;

    case "navto":
        $min = intval($_REQUEST["offset"]);
        $_SESSION["offset_" . $modulobusqueda] = $min;
        $modorenderizado = "listar";
        break;

    case "navlast":
        $sql = $sqlbusquedafin;
        $row = queryrow($sql);
        $max = $row["max"];
        $_SESSION["offset_".$template["modname"]] = $max - $maxfilas;
        $modorenderizado = "listar";
        break;

    case "editar":
        $id_contenido = CleanID($_REQUEST["id_contenido"]);
        $modorenderizado = "modificar";
        break;

    case "crear";
        $idusuario = getSesionDato("id_usuario");

        $id_padre = 0;
        $id_tipo = 0;
        $id_categoria_default = 0;

        $sql = "INSERT INTO listados (listado,descripcion,id_padre,id_tipo,eliminado,id_categoria_destino )
                VALUES ('Nuevo','','" . sql($id_padre) . "','" . sql($id_tipo) . "',1,".$id_categoria_default.")";

        // leemos la ultima inserción para leer el id
        $res = query($sql);
        $id_listado = $UltimaInsercion;


        $con->set("nombre_contenido", "Nuevo");
        $con->set("tipo", "chklst");
        $con->set("id_categoria", $id_categoria_default);
        $con->set("id_listado", $id_listado);
        $con->set("eliminado", 1);
        $con->getRandomArticle();

        $con->set("id_propietario", $idusuario);
        $con->set("fecha_creacion", date("d/m/y"));

        $con->Alta();
        $id_contenido = $con->get("id_contenido");


        header("Location: modchecklist.php?cat=&modo=editar&id_contenido=".$id_contenido);
        exit();

        //$modorenderizado = "crear";
        break;

    case "alta":
        $sql = "INSERT INTO listados (listado,descripcion,id_padre,id_tipo )
                VALUES ('" . sql($_POST["listado"]) . "','" . sql($_POST["descripcion"]) . "','" . sql($_POST["id_padre"]) . "','" . sql($_POST["id_tipo"]) . "')";
        // leemos la ultima inserción para leer el id 
        $res = query($sql);

        // creamos el documento para utilizarlo como el resto dentro de la base de datos documental

        $descripcion = $_REQUEST["descripcion"];
        $con->set("descripcion", $descripcion);
        $con->set("nombre_contenido", $_REQUEST["listado"]);
        $con->set("tipo", "chklst");
        $con->set("id_categoria", $_REQUEST["categorias"]);
        $con->set("id_listado", $UltimaInsercion);
        $con->getRandomArticle();
        $id_propietario = $idusuario;
        $con->set("id_propietario", $id_propietario);
        $con->set("fecha_creacion", date("d/m/y"));

        $con->Alta();
        $id_documento = $con->get("id_contenido");

        //vamos a guardar los permisos
        $compartidos = json_decode($_REQUEST["compartido"], true);

        actualizar_permisos($compartidos, $id_documento);
        break;


    case "modificado":
        $id_contenido = $_REQUEST["id_contenido"];
        $descripcion = $_REQUEST["descripcion"];

        if ($con->Load($id_contenido)) {
            $sql = "UPDATE listados SET eliminado=0"
                .", listado = '" . sql($_POST["listado"]) . "'"
                .", umbral_conformidad = '" . sql($_POST["umbral_conformidad"]) . "'"
                .", descripcion = '" . sql($_POST["descripcion"]) . "'"
                .", id_categoria_destino='".sql($_POST["categorias_res"])."'"
                ." WHERE id_listado='".$con->get("id_listado")."'  ";

            $res = query($sql);
            $con->set("descripcion", $descripcion);
            $con->set("eliminado",0);

            if($_POST["categorias"])
                $con->set("id_categoria",$_POST["categorias"]);

            $con->set("nombre_contenido", $_REQUEST["listado"]);
            $con->Modificacion();

            $id_contenido = $con->get("id_contenido");

            //vamos a guardar los permisos
            $compartidos = json_decode($_REQUEST["compartido"], true);
            actualizar_permisos($compartidos, $id_contenido);
        }

        header("Location: modcontenidos.php");
        break;

    case "datos_pregunta":
        $id_pregunta = $_POST["id_pregunta"];
        $tipo = $_POST["tipo"];

        $datospregunta = queryrow(parametros("SELECT * FROM listados_preguntas WHERE id_pregunta=%d",$id_pregunta));
        $id_grupo = $datospregunta["id_grupo"];
        $id_modo_valoracion = $datospregunta["id_modo_valoracion"];


        /*
        $es_modo_exclusivo = ($id_modo_valoracion == TIPO_EXCLUSIVO);

        if($es_modo_exclusivo){
            $limit = " LIMIT 2 ";
        }*/

        $sql = parametros("SELECT listados_valores_posibles.id_valor_posible, listados_valores_posibles.id_pregunta,"
            . " listados_valores_posibles.texto, listados_valores_posibles.valor FROM listados_valores_posibles"
            . " WHERE listados_valores_posibles.id_pregunta = %d ORDER BY id_valor_posible ASC $limit", $id_pregunta);

        $res = query($sql);
        $i = 0;
        if ($tipo == 1) {
            $opcion_multiple = "oculto";
            $opcion_texto = "";
        } else {
            $opcion_multiple = "";
            $opcion_texto = "oculto";
        }
        $arrdatos = array();


        while ($row = Row($res)) {

            $i++;
            if (($i % 2) == 0) {
                $pi = "odd";
            } else {
                $pi = "even";
            }

            //$exclusiva =($i<3)?"js-exclusiva":"js-no-exclusiva";

            $arrdatos[] = array("id_pregunta" => $row["id_pregunta"], "id_valor_posible" => $row["id_valor_posible"],
                    "texto" => $row["texto"],
                    "valor" => $row["valor"],
                    "linea" => $pi);
        }

        $num_lineas = $i;

        if(!$num_lineas){
            $arrdatos[] = array("css_fila"=>"oculto");
        }



        $exito = true;
        $select_texto = "";
        $select_exclusiva = "";
        $select_multiple = "";

        switch ($tipo) {
            case 1:
                $select_texto = "selected='selected'";
                break;
            case 2:
                $select_exclusiva = "selected='selected'";
                break;
            case 3:
                $select_multiple = "selected='selected'";
                break;

        }

        $page2 = new Pagina();
        $page2->setRoot('templates');
        $page2->readTemplatesFromInput('subsolapa_edicion_pregunta.html');

        //if($es_modo_exclusivo and $num_lineas>=2){
        //    $page2->addvar('edicion_pregunta', 'css_botonmas',"oculto");
        //}

        $page2->addvar('edicion_pregunta', 'id_grupo', $id_grupo);

        $page2->addvar('edicion_pregunta', 'select_texto', $select_texto);
        $page2->addvar('edicion_pregunta', 'select_exclusiva', $select_exclusiva);
        $page2->addvar('edicion_pregunta', 'select_multiple', $select_multiple);

        $page2->addvar('edicion_pregunta', 'id_pregunta', $id_pregunta);
        $page2->addvar('edicion_pregunta', 'opcion_multiple', $opcion_multiple);
        $page2->addvar('edicion_pregunta', 'opcion_texto', $opcion_texto);


        $page2->addRows('listado_valores', $arrdatos);
        $pagetext = trim($page2->getParsedTemplate());


        $valid_utf8 = (@iconv('UTF-8', 'UTF-8', $pagetext) === $pagetext);

        if ($valid_utf8) {
            $data = array("html" => ($pagetext), "ok" => $exito,"dato"=>$arrdatos);
            $out = json_encode($data, JSON_HEX_APOS | JSON_HEX_QUOT);
        } else {
            $data = array("html" => utf8_encode($pagetext), "ok" => $exito,"dato"=>$arrdatos);
            $out = json_encode($data);
        }

        echo $out;
        exit();
        break;

}


/* ---------------------  RENDERIZADO -------------------------- */

switch ($modorenderizado) {
    case "modificar":
        $page->setAttribute('listado', 'src', 'mod_checklist_editar.html');

        // leemos registro y pasamos los datos
        $id_contenido = CleanID($_REQUEST["id_contenido"]);


        $sql = parametros("SELECT listados.id_listado, listado, listados.descripcion, id_padre, id_tipo, listados.umbral_conformidad, "
            . " contenidos.id_categoria, contenidos.id_contenido, listados.id_categoria_destino FROM listados"
            . " LEFT JOIN contenidos ON listados.id_listado= contenidos.id_listado"
            . " WHERE contenidos.id_contenido = %d", $id_contenido);

        $row = queryrow($sql);

        error_log("sql:$sql");

        $page->addvar('listado', 'accion', 'MODIFICAR CHECKLIST');

        $page->addvar('listado', 'id_listado', $row["id_listado"]);
        $page->addvar('listado', 'modo', 'modificado');

        $page->addvar('listado', 'listado', $row["listado"]);
        $page->addvar('listado', 'descripcion', $row["descripcion"]);
        $page->addvar('listado', 'id_padre', $row["id_padre"]);
        $page->addvar('listado', 'id_tipo', $row["id_tipo"]);
        $page->addvar('listado', 'id_contenido', $row["id_contenido"]);
        $page->addvar('listado', 'id_categoria', $row["id_categoria"]);

        $page->addvar('listado', 'umbral_conformidad', $row["umbral_conformidad"]);


        $id_categoria = $row["id_categoria"];
        $id_listado = $row["id_listado"];
        $id_categoria_destino = $row["id_categoria_destino"];

        // cargamos grupos
        $sql = parametros("SELECT id_grupo, id_listado, grupo, posicion, visible FROM listados_grupos WHERE id_listado = %d ORDER BY posicion", $id_listado);
        $res = query($sql);
        $i = 0;

        $arrdatos = array();
        while ($row = Row($res)) {
            $i++;
            if ( !($i % 2)) {
                $pi = "odd";
            } else {
                $pi = "even";
            }
            $id_grupo = $row["id_grupo"];

            $arrdatos[] = array("id_grupo" => $row["id_grupo"], "id_listado" => $id_listado, "grupo" => $row["grupo"],
                "posicion" => $row["posicion"], "linea" => $pi, "visible" => $row["visible"]);
        }

        if($i)
            $page->addRows('listadogrupos', $arrdatos);
        else
            $page->addRows('listadogrupos', array(array("cssgrupo"=>"oculto")));

        $catdefecto = $_REQUEST["idcategoria"];

        $usr = (getSesionDato("esadmin"))?0:getSesionDato("id_usuario");

        $combocategoriashoja = getComboCategoriasHoja($id_categoria, $usr);
        $combocategoriashoja_res = getComboCategoriasHoja($id_categoria_destino, $usr);

        $page->addVar('listado', 'combocategoriashoja', $combocategoriashoja);
        $page->addVar('listado', 'combocategoriashoja_res', $combocategoriashoja_res);
        $page->addvar('listado', 'lista_permisos', documento_compartido_con($id_contenido));
        break;


    case "crear":
        // pasamos datos en blanco

        $page->setAttribute('listado', 'src', 'mod_checklist_editar.html');
        $page->addvar('listado', 'accion', 'ALTA CHECKLIST');
        $page->addvar('listado', 'modo', 'alta');
        $page->addvar('listado', 'id_padre', '0');
        $page->addvar('listado', 'id_tipo', '0');


        $arrdatos = array(); // añadimos un array en blanco
        $arrdatos[] = array("id_pregunta" => "0", "id_grupo" => "0", "id_modovaloracion" => "0",
            "posicion" => "0", "pregunta" => $pi, "comentario" => "", "accion" => "",
            "fecha_alaerta" => "", obligatorio => "0");

        $page->addRows('listadopreguntas', $arrdatos);

        // cargamos las categorias
        $catdefecto = $_REQUEST["idcategoria"];


        $usr = (getSesionDato("esadmin"))?0:getSesionDato("id_usuario");

        $combocategoriashoja = getComboCategoriasHoja($catdefecto, $usr);
        $page->addVar('listado', 'combocategoriashoja', $combocategoriashoja);

        break;
}

$page->addVar('headbar', 'usuario_logueado', getSesionDato("nombreusuario"));

$page->Volcar();

