<?php


/**
 * DocClass: gestion documental
 */

include("tool.php");
include_once(__ROOT__ . "/inc/modcontenidos.inc.php");
include_once(__ROOT__ . "/inc/busquedas.inc.php");
include_once(__ROOT__ . "/inc/gestion_permisos.inc.php");

if (!getSesionDato("eslogueado")) {
    header("Location: conexioncerrada.php");
    exit();
}

$administrador = getSesionDato("esadmin");

$idusuario = getSesionDato("id_usuario");


$modo = $_REQUEST["modo"];
$listarsupervisor = isset($_POST["listarsupervisor"])?$_POST["listarsupervisor"]:"";

$cat = CleanID($_REQUEST["cat"]);
$cat_s = sql($cat);


$idcategoria = isset($_REQUEST["idcategoria"])?$_REQUEST["idcategoria"]:false;

$con = new contenido();

error_log("modo:$modo");

switch ($modo) {
    case "eliminar":

        $idaborrar = explode(",", $_POST["contenidos_borrar"]);
        foreach ($idaborrar as $valor) {
            $sql = "UPDATE contenidos SET eliminado=1 WHERE id_contenido='$valor' ";
            query($sql);
        }

        query($sql);

        reajustarHojas();
        $modo = "listando";
        break;

    case "movercategoria":
        //$maxPagina = 100;
        $newCategoria = CleanID($_REQUEST["categoriamover"]);
        $idacambiar = explode(",", $_POST["contenidos_mover"]);
        foreach ($idacambiar as $valor) {
            $sql = "UPDATE contenidos SET id_categoria='$newCategoria' WHERE id_contenido='$valor' ";
            query($sql);
        }

        $modo = "listando";
        break;

    case "editar":

        $id_contenido = CleanID($_REQUEST["id_contenido"]);
        $con->Load($id_contenido);

        break;

    case "guardarcambios":

        $id_contenido = $_REQUEST["id_contenido"];


        if ($con->Load($id_contenido)) {

            $descripcion = $_REQUEST["descripcion"];
            $con->set("descripcion", $descripcion);
            $con->set("id_categoria", $_POST["categorias"]);
            $con->set("nombre_contenido", $_REQUEST["nombre_contenido"]);

            $con->setContenido($_REQUEST["articulo"]);

            $compartidos = json_decode($_REQUEST["compartido"], true);
            actualizar_permisos($compartidos, $id_contenido);
            $con->Modificacion();
        }

        $modo = "listando";
        break;

    case "guardaraltaarticulo":
        $descripcion = $_REQUEST["descripcion"];
        $con->set("descripcion", $descripcion);
        $con->set("nombre_contenido", $_REQUEST["nombre_contenido"]);
        $con->set("tipo", "html");
        $con->set("id_categoria", $_REQUEST["categorias"]);
        $con->getRandomArticle();
        $id_propietario = $idusuario;
        $con->set("id_propietario", $id_propietario);
        $con->set("fecha_creacion", date("d/m/y"));
        $con->Alta();
        $id_documento = $con->get("id_contenido");


        $compartidos = json_decode($_REQUEST["compartido"], true);
        actualizar_permisos($compartidos, $id_documento);
        $modo = "altaarticulo";
        break;

    case "guardaraltafichero":
        
        $descripcion = $_REQUEST["descripcion"];

        $con->set("descripcion", $descripcion);
        $path = $_REQUEST["ficherosubido"];


        $baseDir = getParametro("basePath");
        $abspath = $baseDir .  $path;

        $existe = file_exists($abspath);
        $tam = filesize($abspath);

        if($existe && $tam){
            error_log("cambiando path archivo");
            $con->set("path_archivo", $path);
            $con->regularTipo($path);
        }

        $con->set("id_categoria", $_REQUEST["categorias"]);
        $con->set("nombre_contenido", $_REQUEST["nombre_contenido"]);

        $id_propietario = $idusuario;
        $con->set("id_propietario", $id_propietario);
        $con->set("fecha_creacion", date("d/m/y"));
        $con->Alta();
        $id_documento = $con->get("id_contenido");

        $compartidos = json_decode($_REQUEST["compartido"], true);
        actualizar_permisos($compartidos, $id_documento);

        $modo = "listando";

        break;

    case "altaarticulo":
        $page->addVar('listado', 'id_categoria', $idcategoria);
       
        break;

    case "altafichero":
        $page->addvar('listado', 'id_categoria', $idcategoria);
        
        break;

    case "subiendoficheronuevo": {
            include_once(__ROOT__."/class/qquploader.class.php");

            $id_contenido = $_REQUEST["id_contenido"];
            // list of valid extensions, ex. array("jpeg", "xml", "bmp")
            $allowedExtensions = array();
            // max file size in bytes
            $sizeLimit = 25 * 1024 * 1024;
            $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);


            $baseDir = getParametro("basePath");
            $dir = getRandomDir();
            createPath($baseDir, $dir["dirs"]);
            $usaDir = $baseDir . $dir["dirtxt"];
            $result = $uploader->handleUpload($usaDir, $dir["dirtxt"]);
            
            // to pass data through iframe you will need to encode all html tags
            //error_log("res:" . $result . ",uD:" . $usaDir . ",dir:" . var_export($dir, true));

            echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

            return;
        }
        break;
    case "subiendofichero": {
            include_once(__ROOT__ . "/class/qquploader.class.php");


            $id_contenido = $_REQUEST["id_contenido"];

            // list of valid extensions, ex. array("jpeg", "xml", "bmp")
            $allowedExtensions = array();
            // max file size in bytes
            $sizeLimit = 30 * 1024 * 1024;

            $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

            $baseDir = getParametro("basePath");

            $dir = getRandomDir();

            createPath($baseDir, $dir["dirs"]);

            $usaDir = $baseDir . $dir["dirtxt"];

            $result = $uploader->handleUpload($usaDir, $dir["dirtxt"]);

            error_log("res:" . var_export($result, true) . ",uD:" . $usaDir . ",dir:" . var_export($dir, true));


            // to pass data through iframe you will need to encode all html tags
            echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

            $con = new contenido();

            if ($con->Load($id_contenido)) {

                $pathguardar = $dir["dirtxt"] . $result["filename"];

                $baseDir = getParametro("basePath");
                $abspath = $baseDir .  $pathguardar;

                $existe = file_exists($abspath);
                $tam = filesize($abspath);

                if($existe && $tam){
                    error_log("fichero ($existe) existe, tam($tam)");
                    $con->set("path_archivo", $pathguardar);
                    $con->regularTipo($pathguardar);
                }

                $con->Modificacion();
            } else {
                die("no se pudo abrir para modificar ($id_contenido)");
            }


            die();
        }
                   
        break;
    case "lista_categorias":
        
        exit();
        break;
}

if (!$modo)
    $modo = "listando";

$page = new Pagina();

$page->Inicia($template["modname"], "admin.html");




/* ----------------  FIXING...  ------------------
 * 
 *
 */

reajustarHojas();

if (0) {
    //correcciones pruebas
    query("UPDATE contenidos SET tipo='datos' WHERE tipo='' ");
    query("UPDATE contenidos SET tipo='texto' WHERE tipo='txt' ");
    query("UPDATE contenidos SET tipo='word' WHERE tipo='doc' ");
}

/*
 *
 *  ----------------  --------  ------------------ */


if ($modo == "editar") {
    if ($administrador){
       $usr=false;
    }else {
       $usr=$idusuario ;
    }
    $tipo_fichero = $con->get("tipo");

    //cargamos las categorias disponibles
    $combocategoriashoja = getComboCategoriasHoja($con->get("id_categoria"), $usr);
    $page->addVar('listado', 'volver', 'modcontenidos.php');

    switch ($tipo_fichero) {
        case "html":
            $page->addVar('listado', 'accion', "EDITAR DOCUMENTO");
            $page->setAttribute('listado', 'src', 'visor_inlineeditor_contenidos.html');
            $page->addvar('listado', 'combocategoriashoja', $combocategoriashoja);
            $contenido = $con->getContenido();

            $page->addVar('listado', 'articulo', $contenido);
            //articulo
            // vamos a cargar con quien esta compartido el documento
            $page->addvar('listado', 'lista_permisos', local_documento_compartido_con($con->get("id_contenido")));
            break;

        default:
            $page->addVar('listado', 'accion', "REVISAR ARCHIVOS");
            $page->setAttribute('listado', 'src', 'visor_uploader_contenidos.html');
            $page->addVar('listado', 'modouploader', 'subiendofichero');
            $page->addvar('listado', 'combocategoriashoja', $combocategoriashoja);
            $contenido = $con->getContenido();
            $page->addVar('listado', 'articulo', $contenido);

            $page->addvar('listado', 'lista_permisos', local_documento_compartido_con($con->get("id_contenido")));
            break;
    }

    $page->addArrayFromCursor('listado', $con, array("nombre_contenido", "id_categoria", "id_contenido", "descripcion", "path_archivo", "tipo"));

    $page->addVar('listado', 'id_actual', $cat_s);
    $page->addVar('listado', 'id_contenido', $id_contenido);
    $page->addVar('listado', 'modo', "guardarcambios");

    $page->addVar('listado', 'enalta', 'normal');
    $page->addVar('listado', 'enmod', 'none');
}


if ($modo == "altaarticulo") {
    $catdefecto = $_REQUEST["idcategoria"];
    if ($administrador){
       $usr=false;
    }else {
       $usr=$idusuario ;
    }
    
    $combocategoriashoja = getComboCategoriasHoja($catdefecto, $usr);
    $page->addVar('listado', 'accion', "CREAR DOCUMENTO");
    $page->addVar('listado', 'combocategoriashoja', $combocategoriashoja);
    $page->setAttribute('listado', 'src', 'visor_inlineeditor_contenidos.html');

    $page->addVar('listado', 'id_actual', $cat_s);
    $page->addVar('listado', 'id_contenido', "-1");
    $page->addVar('listado', 'modo', "guardaraltaarticulo");

    $page->addVar('listado', 'enalta', 'none');
    $page->addVar('listado', 'enmod', 'normal');
    $page->addVar('listado', 'volver', 'cat.php');
}

if ($modo == "altafichero") {
    $catdefecto = $_REQUEST["idcategoria"];
    if ($administrador){
       $usr=false;
    }else {
       $usr=$idusuario ;
    }
    $combocategoriashoja = getComboCategoriasHoja($catdefecto, $usr);
    $page->addVar('listado', 'combocategoriashoja', $combocategoriashoja);
    $page->setAttribute('listado', 'src', 'visor_uploader_contenidos.html');
    $page->addVar('listado', 'id_actual', $cat_s);
    $page->addVar('listado', 'id_contenido', "-1");
    $page->addVar('listado', 'modo', "guardaraltafichero");
    $page->addVar('listado', 'volver', 'cat.php');

    //$page->addVar();
    //subiendofichero
    $page->addVar('listado', 'modouploader', 'subiendoficheronuevo');

    $page->addVar('listado', 'enalta', 'none');
    $page->addVar('listado', 'enmod', 'normal');
}


if ($modo == "listando") {

    $page->setAttribute('listado', 'src', 'edicion_contenidos.html');

    $page->addVar('listado', 'id_actual', $cat_s);

    $filtro = $_REQUEST["tipofiltro"];

    if ($filtro == "tipofichero") {
        $tipo_s = sql($_REQUEST["tipodefichero"]);

        $extraTipo = " AND contenidos.tipo = '$tipo_s' ";
    } else if ($filtro == "categoria") {
        $id_categoria_s = sql(CleanID($_REQUEST["filtrocategoria"]));

        $extraCategoria = " AND contenidos.id_categoria = '$id_categoria_s'  ";
    }


    /* ----------------  LISTANDO  ------------------
     *
     */
    $idusrsesion = $idusuario;
    $extrausr = " AND contenidos.id_propietario = '$idusrsesion'  ";
    if ($administrador) {
        if ($listarsupervisor == "SI") {
            $checkedadmin = "checked";
        } else {
            $checkedadmin = "";
        }
        $page->addVar('listado', 'checkedadmin', $checkedadmin);

        $cladmin = "";
        if ($listarsupervisor == "SI") {
            $extrausr = "";
        }

    } else {
        $cladmin = "oculto";
    }

    $page->addVar('listado', 'esadmin', $cladmin);

   
    $sql = "SELECT *, contenidos.nombre_contenido as nombrecontenido, categorias.nombre as nombrecategoria, contenidos.id_listado FROM contenidos " .
            "  JOIN categorias ON contenidos.id_categoria = categorias.id_categoria " .
            " WHERE contenidos.eliminado=0 " . $extraTipo . $extraCategoria . $extrausr . " ORDER BY contenidos.id_contenido DESC LIMIT 100";

    echo "<!-- sql: $sql -->";

    $res = query($sql);

    $tt = 0;

    while ($row = Row($res)) {
        $row["ext"] = getExtension($row["path_archivo"]);
        $row["tt"] = $tt++;

        $modulo = "modcontenidos";
        switch($row["tipo"]){
            case "chklst":
                $modulo = "modchecklist";
                break;
            case "rss":
                $modulo = "modopendata";
                break;
        }


        //Hack: no se pueden "editar" resultados de checklist como si fueran html normales.
        if($row["tipo"]=="html" and $row["id_listado"]){
            $row["css_editar"] = "oculto";
        }

        $row["misc"] = $row["tipo"]. "_" . $row["id_listado"];

        $row["modulo"] = $modulo;

        $rows[] = $row;
    }

    $page->addRows('listadocumentos', $rows);


    if (!$tt) {
        $page->addVar("listado", "mensajevacio", "<div class='mensajeaviso bocadillo-aviso' style='margin-top:32px'>Sin resultados. <a href='modcontenidos.php?cat=$cat_s'>Ver todos</a></div>");
        $page->addVar("listado", "cssocultarlistado", "oculto");
    }

    $combocategoriashoja = getComboCategoriasHoja(0,$usr);
    $combocategoriashoja = str_replace("\n", '\n',$combocategoriashoja);
    $page->addVar('listado', 'combocategoriashoja', $combocategoriashoja);


    $tiposdeficheros = "<option value='html'>Documento HTML</option" .
            "<option value='pdf'>Documento PDF</option" .
            "<option value='word'>Documento de word (.doc)</option" .
            "<option value='video'>Videos</option" .
            "<option value='datos'>Otros</option";

    $page->addVar('listado', 'tiposdeficheros', $tiposdeficheros);
}

/*
 *
 *  ----------------  --------  ------------------ */



$esadmin = "oculto";
if ($administrador) {
    $esadmin = "";
}

$page->addVar("listado", "esadmin", $esadmin);
$page->setAttribute('menu', 'src', 'menu.html');

$page->addVar('headbar', 'usuario_logueado', getSesionDato("nombreusuario"));

$page->Volcar();

