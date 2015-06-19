<?php

include("tool.php");


include("tool.php");
include_once(__ROOT__ . "/inc/busquedas.inc.php");
include_once(__ROOT__ . "/class/contenido_opendata.class.php");
include_once(__ROOT__ . "/inc/gestion_permisos.inc.php");

$idusuario = getSesionDato("id_usuario");

if (!getSesionDato("eslogueado")) {
    header("Location: conexioncerrada.php");
    exit();
}
if (!getSesionDato("esadmin")) {
    header("Location: conexioncerrada.php");
    exit();
}

$es_administrador = (getSesionDato("esadmin"));


//sacamos los menus

$modorenderizado = "";

$modo = $_REQUEST["modo"];



$con = new contenido_opendata();

switch ($modo) {
    case "crear";
        $idusuario = getSesionDato("id_usuario");

        $sql = "INSERT INTO canales_rss (titulo,url,eliminado ) VALUES ('Nuevo','',1)";
        query($sql);

        $id_rss = $UltimaInsercion;

        $con->set("nombre_contenido", "Nuevo");
        $con->set("tipo", "rss");
        $con->set("id_categoria", $id_categoria_default=0);
        $con->set("id_rss", $id_rss);

        $con->set("eliminado", 1);
        $con->getRandomArticle();

        $con->set("id_propietario", $idusuario);
        $con->set("fecha_creacion", date("d/m/y"));

        $con->Alta();

        $id_contenido = $con->get("id_contenido");
        header("Location: modopendata.php?cat=&modo=editar&id_contenido=".$id_contenido);
        exit();

        //$modorenderizado = "crear";
        break;
    case "editar":
        $id_contenido = $_REQUEST["id_contenido"];
        $con->Load($id_contenido);

        $modorenderizado = "editar";
        break;

    case "guardar":
    case "modificado":
        $id_contenido = $_REQUEST["id_contenido"];
        $descripcion = $_REQUEST["descripcion"];
        if ($con->Load($id_contenido)) {
            $url = trim($_POST["url"]);

            $sql = parametros("UPDATE canales_rss SET eliminado=0,url='%s' WHERE id_canal_rss=%d",$url,$con->get("id_rss"));
            query($sql);

            $con->set("descripcion", $descripcion);
            $con->set("eliminado",0);
            $con->set("nombre_contenido", $_REQUEST["listado"]);
            $con->set("id_categoria", $_REQUEST["categorias"]);
            $con->Modificacion();

            $compartidos = json_decode($_REQUEST["compartido"], true);
            actualizar_permisos($compartidos, $id_contenido);


        }
        header("Location: modcontenidos.php");
        break;
}

/* ------------------------------- RENDERIZADO ------------------------------ */

switch($modorenderizado){
    case "editar":

        $id_contenido = CleanID($_REQUEST["id_contenido"]);
        $page->addvar('listado', 'modo', 'guardar');
        break;



}


if ($es_administrador) {
    $usr = false;
} else {
    $usr = getSesionDato("id_usuario");
}

$combocategoriashoja = getComboCategoriasHoja($con->get("id_categoria"), $usr);

$page->addVar('listado', 'combocategoriashoja', $combocategoriashoja);



$page->addVar("listado","listado",$con->get("nombre_contenido"));
$page->addVar("listado","url",$con->getURL());
$page->addVar('listado', 'volver', "modcontenidos.php");

$page->addvar('listado', 'lista_permisos', local_documento_compartido_con($con->get("id_contenido")));


/*
<input type="hidden" name="id_categoria" value =""/>
    <input type="hidden" name="id_listado" value=""/>
    <input type="hidden" name="id_padre" value =""/>
    <input type="hidden" name="id_tipo" value=""/>
    <input type="hidden" name="id_contenido" value=""/>*/

$data = $con->export();

foreach($data as $key=>$value){
    $page->addVar("listado",$key,$value);
}


$lineas = array();

$page->setAttribute('listado', 'src', 'modopendata_vista.html');

$url = $con->getURL(); ///por ejemplo: http://news.yahoo.com/rss/tech

if ( $url ) {
    $rss = fetch_rss( $url );

    $titulo_remoto =  $rss->channel['title'];

    $lineas = array();

    $max = 100;
    $num = 0;
    foreach ($rss->items as $item) {
        if($num<$max)
            $lineas[] = $item;//link, title

        $num++;
    }

    $page->addRows('listadorss', $lineas);
} else{
    $page->addRows('listadorss', array(array("csslinea"=>oculto)));
}




$page->addVar('headbar', 'usuario_logueado', getSesionDato("nombreusuario"));

$page->Volcar();


