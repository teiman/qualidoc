<?php


include("tool.php");
include_once(__ROOT__ . "/inc/modcontenidos.inc.php");
include_once(__ROOT__ . "/inc/gestion_permisos.inc.php");

if ( !getSesionDato("eslogueado") ){
	header("Location: conexioncerrada.php");
	exit();
}


$modo = $_REQUEST["modo"];
$autor = $_REQUEST["buscartexto"];
$titulo= $_REQUEST["buscartexto"];
$categoria= $_REQUEST["buscartexto"];
$descripcion=$_REQUEST["buscartexto"];
$buscar=$_REQUEST["buscartexto"];

$con = new contenido();



switch ($modo) {

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
            $con->set("nombre", $_REQUEST["nombre_contenido"]);
            $compartidos = json_decode($_REQUEST["compartido"], true);
            actualiza_permisos($compartidosm, $id_contenido);
            $con->Modificacion();

            //$articulo = $_REQUEST["articulo"];
            //$bytesescritos = $con->setContenido($articulo);
            //error_log("b:". $bytesescritos);
        }

        $modo = "listando";
        break;
}

if (!$modo)	 $modo = "listando";

$page    =    &new Pagina();

$page->Inicia( $template["modname"], "admin.html");


if ($modo == "editar") {

    $tipo_fichero = $con->get("tipo");
    //cargamos las categorias disponibles
    $combocategoriashoja = getComboCategoriasHoja();
    //
    switch ($tipo_fichero) {
        case "html":
            $page->setAttribute('listado', 'src', 'visor_inlineeditor_contenidos.html');
            $page->addvar('listado', 'combocategoriashoja', $combocategoriashoja);
            $contenido = $con->getContenido();
            $page->addVar('listado', 'articulo', $contenido);

            break;
        default:
            $page->setAttribute('listado', 'src', 'visor_uploader_contenidos.html');
            $page->addVar('listado', 'modouploader', 'subiendofichero');
            break;
    }

    $page->addArrayFromCursor("listado", $con, array("nombre", "id_categoria", "id_contenido", "descripcion", "nombre", "path_archivo", "tipo"));

    $page->addVar('listado', 'id_actual', $cat_s);
    $page->addVar('listado', 'id_contenido', $id_contenido);
    $page->addVar('listado', 'modo', "guardarcambios");
    $page->addVar('listado', 'enalta', 'normal');
    $page->addVar('listado', 'enmod', 'none');
}




if ($modo == "listando") {

    $page->setAttribute('listado', 'src', 'mod_filtrodocs.html');

    $page->addVar('listado', 'id_actual', $cat_s);
    //establecemos el filtro
   /*
    if ($autor != "" && $autor !="Autor"){
        $filtro = $filtro. " AND( usuarios.nombreusuario like '%$autor%'" ;
    }    
    if ($titulo != "" && $titulo !="Título"){
       $filtro = $filtro. " OR contenidos.nombre_contenido like '%$titulo%'" ;
    }
    if ($categoria !="" && $categoria!="Carpeta"){
       $filtro = $filtro. " OR categorias.nombre like '%$categoria%'" ;  
       $filtro_categoria= " AND categorias.nombre like '%".sql($categoria)."%'";  
    }
    if ($descripcion!="" && $descripcion!="Descripción"){
       $filtro = $filtro. " OR contenidos.descripcion like '%$descripcion%')" ;
    }    
   */
    
    if ($buscar != "" && $autor !="Buscar"){
        $filtro = $filtro. " AND( usuarios.nombreusuario like '%".sql($autor)."%'" ;
        $filtro = $filtro. " OR contenidos.nombre_contenido like '%".sql($titulo)."%'" ;
        $filtro = $filtro. " OR categorias.nombre like '%".sql($categoria)."%'" ;  
        $filtro = $filtro. " OR contenidos.descripcion like '%".sql($descripcion)."%')" ;
        $filtro_categoria= " AND categorias.nombre like '%".sql($categoria)."%'"; 
    }    
    
    /* ----------------  LISTANDO  ------------------
     *
     */
    $idusrsesion = getSesionDato("id_usuario");
// cargamos categorias
    $sql= "SELECT distinct categorias.nombre, categorias.id_categoria FROM permisosdocumentos" 
         . " INNER JOIN grupos on grupos.id_grupo = id_grupo_permitido"
         . " INNER JOIN gruposelementos on gruposelementos.id_grupo = grupos.id_grupo"
         . " INNER JOIN categorias on id_categoria_compartida = id_categoria"
         . " WHERE id_usuario = ".sql($idusrsesion)." AND id_categoria_compartida <> 0".$filtro_categoria." ORDER BY categorias.nombre";
    
    $res = query($sql);

    $tt = 0;

    while ($row = Row($res)) {

        $row["ext"] = "";
        $row["tt"] = "";
        $row["nombrecontenido"] = "class='subcategoria' href='cat.php?cat=".$row["id_categoria"]."&amp;modo=listaficheros'>".$row["nombre"];
        $row["tipodoc"] ="<p></p>";
        $row["tipo_linea"]= "src='img/verde/etiqueta.png'";
        $rows[] = $row;
    }


// cargamos documentos
    $sql = "(SELECT contenidos.nombre_contenido , usuarios.nombreusuario, contenidos.tipo, contenidos.id_contenido,"
            . " contenidos.fecha_creacion, categorias.nombre as nombrecategoria, contenidos.id_categoria, id_documento_compartido"
            . " FROM permisosdocumentos INNER JOIN grupos ON grupos.id_grupo = permisosdocumentos.id_grupo_permitido"
            . " INNER JOIN gruposelementos ON grupos.id_grupo = gruposelementos.id_grupo INNER JOIN contenidos ON"
            . " contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
            . " INNER JOIN categorias ON contenidos.id_categoria = categorias.id_categoria"
            . " INNER JOIN usuarios ON contenidos.id_propietario = usuarios.id_usuario"
            . " WHERE gruposelementos.id_usuario = ".sql($idusrsesion)
            . " AND contenidos.eliminado = 0 ".$filtro.")"
            . " UNION (SELECT contenidos.nombre_contenido, usuarios.nombreusuario, contenidos.tipo, contenidos.id_contenido,"
            . " contenidos.fecha_creacion, categorias.nombre as nombrecategoria, contenidos.id_categoria, id_documento_compartido FROM permisosdocumentos INNER JOIN contenidos ON"
            . " contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
            . " INNER JOIN categorias ON contenidos.id_categoria = categorias.id_categoria"
            . " INNER JOIN  usuarios ON contenidos.id_propietario = usuarios.id_usuario"
            . " WHERE id_usuario_permitido = ".sql($idusrsesion)
            . " AND contenidos.eliminado = 0 ".$filtro.") ORDER BY fecha_creacion DESC";
       
    $res = query($sql);

    $tt = 0;

    while ($row = Row($res)) {
        $row["ext"] = getExtension($row["path_archivo"]);
        $row["tt"] = $tt++;
        $idcategoria = $row["id_categoria"];
        $row["nombrecontenido"]= "title='' href='modvisor.php?id_contenido=".$row["id_contenido"]."&cat=".$idcategoria."&submodo=volvercontenidos'> ".$row["nombre_contenido"];
        $row["tipodoc"]= "<p class='doc-".$row["tipo"]."'>Documento ".$row["tipo"]."</p>";
         $row["tipo_linea"]= "src='img/verde/archivo.png'";
        $rows[] = $row;
    }
    $page->addRows('listadocumentos', $rows);

    if (!$tt) {
        $page->addVar("listado", "mensajevacio", "<div class='mensajeaviso bocadillo-aviso' style='margin-top:32px'>Sin resultados. <a href='modcontenidos.php?cat=$cat_s'>Ver todos</a></div>");
        $page->addVar("listado", "cssocultarlistado", "oculto");
    }

    $combocategoriashoja = getComboCategoriasHoja();

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



$esadmin="oculto";
if (getSesionDato("esadmin")){
    $esadmin="";
} 
$page->addVar("listado","esadmin",$esadmin);
$page->setAttribute( 'menu', 'src', 'menu.html' );
//}

//$page->addRows('listadonovedades',getRowsNovedades() );
$page->addVar('headbar', 'usuario_logueado', getSesionDato("nombreusuario"));
$page->Volcar();


