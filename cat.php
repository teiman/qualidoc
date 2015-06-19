<?php

include("tool.php");
include(__ROOT__ . "/inc/cat.inc.php");
include(__ROOT__ . "/inc/busquedas.inc.php");

if (!getSesionDato("eslogueado")) {
    header("Location: conexioncerrada.php");
    exit();
}


if (getSesionDato("esadmin")) {
    $usresadmin = true;
} else {
    $usresadmin = false;
}

$iduser = getSesionDato("id_usuario");

$modo = $_REQUEST["modo"];

$cat = CleanID($_REQUEST["cat"]);
$cat_s = sql($cat);


if ($modo == "eliminar_compartido_con") {
    $id = $_POST["id_gruposelementosaborrar"];
    $sql = parametros("DELETE FROM permisosdocumentos WHERE id_permiso =%d", $id);
    queryrow($sql);
    exit();
}


if (!$modo) {
    $modo = 'listacategorias';
}

if ($modo == "listacategorias") {
    if ($usresadmin) {
        $sql = "SELECT nombre, eshoja, posicion, id_categoria FROM categorias WHERE id_padre = %d and eliminado='0' ORDER BY posicion asc, nombre ASC "; //categoria actual
        $sql = parametros($sql, $cat_s);
    } else {
        $sql = "(SELECT categorias.nombre, categorias.eshoja, categorias.posicion, id_categoria_compartida as id_categoria FROM permisosdocumentos INNER JOIN grupos ON grupos.id_grupo = permisosdocumentos.id_grupo_permitido"
                . " INNER JOIN gruposelementos ON grupos.id_grupo = gruposelementos.id_grupo INNER JOIN categorias ON id_categoria_compartida = categorias.id_categoria "
                . " WHERE id_usuario = %d AND categorias.id_padre =%s"
                . " ) UNION (SELECT categorias.nombre, categorias.eshoja, categorias.posicion, id_categoria_compartida as id_categoria FROM permisosdocumentos INNER JOIN categorias ON id_categoria_compartida = categorias.id_categoria"
                . " WHERE id_usuario_permitido = %d AND categorias.id_padre =%s) ORDER BY posicion, nombre";
        $sql = parametros($sql, $iduser, $cat_s, $iduser, $cat_s);
    }
    $res = query($sql);
    $t = 0;

    while ($row = Row($res)) {

        $t++;
        $name = $row["nombre"];
        $text = "<h2 class='nombrecategoria'>" . html($name) . "</h2>";
        if ($row["eshoja"]) {
            $text .= "<input type='image' class='agregar-documento' src='img/verde/crear-doc.png' alt='crear un documento' title='Crear documento' onclick='alta_articulo(" . $row["id_categoria"] . ")'>";
            $text .= "<input type='image' class='agregar-documento' src='img/verde/subir-doc.png' alt='subir un archivo' title='Subir archivo'  onclick='alta_fichero(" . $row["id_categoria"] . ")'>";
            $text .= "<input type='hidden' id='idcategoria'" . $row["idcategoria"] . " value='" . $row["idcategoria"] . "'>";
        }
        $text .= genSubCategoria($row["id_categoria"], $row["eshoja"]);

        $page->addVar("page", "c$t", $text);
    }

    if (!$t) {
        $sql = "SELECT count(*) as cuantos FROM contenidos WHERE id_categoria ='$cat_s' AND eliminado=0  ORDER BY nombre_contenido ASC ";

        $row = queryrow($sql);
        $cuantos_s = intval($row["cuantos"]);

        //$page->addVar("page","c$t",$text );

        if ($cuantos_s > 0) {
            $modo = "listaficheros";
            //die "modo:". $modo;
        } else {
            $page->addVar("page", "c1", "<div class='mensajeaviso bocadillo-aviso' style='margin-top:32px'>Esta carpeta está vacía. <a href='javascript:history.go(-1)'>Volver</a></div>");
        }
    }
}

if ($modo == "listaficheros") {
    $page->setAttribute('listado', 'src', 'listadoficheros.html');

    $rows = getRowsFicheros($cat_s);
    $page->addRows('listadoficheros', $rows);

    $row = queryrow(parametros("SELECT * FROM categorias WHERE id_categoria =%d and eliminado=0 ",$cat_s));
    $page->addVar("listado", "nombre", $row["nombre"]);

    $page->addVar("listado", "id_categoria", $cat_s);

    $data = queryrow(parametros("SELECT count(*) as cuantos FROM contenidos  WHERE id_categoria=%d and eliminado=0 and tipo='chklst'  "  ,$cat_s));

    if(!$data["cuantos"]){
        $page->addVar("listado","websumarize_css","oculto");
    }
}






$esadmin = "oculto";
if ($usresadmin) {
    $esadmin = "";
}

$page->addVar("listado", "esadmin", $esadmin);
$page->setAttribute('menu', 'src', 'menu.html');


$page->ajustaMigas($cat_s);



$page->addVar('headbar', 'usuario_logueado', getSesionDato("nombreusuario"));
$page->Volcar();



