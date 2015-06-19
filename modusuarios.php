<?php
define("CAPTURARTODOBUG", "true");

//require ("kint/Kint.class.php");

include("tool.php");
//include(__ROOT__ . "/inc/paginabasica.inc.php");
$modorenderizado = "listar";
$modo = $_POST["modo"];
//navegacion paginas
$campobusqueda = "usuarios.nombreusuario";
$modulobusqueda = "usuarios";
$sqlbusquedafin = "SELECT count(id_usuario) as max FROM usuarios ";
// 
if (!getSesionDato("eslogueado")) {
    header("Location: conexioncerrada.php");
    exit();
}
if (!getSesionDato("esadmin")) {
    header("Location: conexioncerrada.php");
    exit();
}

//sacamos los menus

$esadmin = "oculto";
if (getSesionDato("esadmin")) {
    $esadmin = "";
}



$page->addVar("listado", "esadmin", $esadmin);
$page->setAttribute('menu', 'src', 'menu.html');


$page->ajustaMigas($cat_s);


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

        $_SESSION["offset_usuarios"] = $max - $maxfilas;
        $modorenderizado = "listar";
        break;


    //
    case "modificar_usuario":
        $modorenderizado = "modificar";

        break;
    case "crear_usuario";
        $modorenderizado = "crear";
        break;

    case "eliminar_dato":
        $id = (int)$_POST["id_dato"];
        $sql = "UPDATE usuarios SET eliminado = 1 WHERE id_usuario = '" . sql($id) . "'";
        $res = query($sql);
        $modorenderizado = "listar";
        break;

    case "usuario_creado":
        $administrador = $_POST["administrador"]?1:0;

        $sql = "INSERT INTO usuarios(nombreusuario, direccioncorreo, empresa, administrador) VALUES ('" . sql($_POST["nombreusuario"]) . "','" . sql($_POST["direccioncorreo"]) . "','" . sql($_POST["empresa"]) . "'," . sql($administrador) . ")";

        // leemos la ultima inserción para leer el id y  realizar el has de la contraseña.
        $res = query($sql);
        $idultimo = (int)$UltimaInsercion;

        // encriptamos la clave
        $hash = md5($idultimo . "_TARARI_" . $_POST["password"]);
        $sql = "UPDATE usuarios SET password = '" . sql($hash) . "' WHERE id_usuario = '" . sql($idultimo) . "'";
        $res = query($sql);
        $modo = "listar";
        break;

    case "usuario_modificado":
        $administrador = ($_POST["administrador"])?1:0;

        $idusuario = (int)$_POST["idusuario"];
        $sql = "UPDATE usuarios SET  nombreusuario = '" . sql($_POST["nombre"]) . "', direccioncorreo = '" . sql($_POST["direccioncorreo"]) . "', empresa = '" . sql($_POST["empresa"]) . "', administrador = " . sql($administrador) . " WHERE id_usuario= '" . sql($idusuario) . "'";
        $res = query($sql);
        $modo = "listar";
        break;

    case "usuario_modificado_password":
        $hash = md5($_POST["id"] . "_TARARI_" . $_POST["password"]);
        $id = (int)$_POST["id"];
        $sql = "UPDATE usuarios SET password = '" . sql($hash) . "' WHERE id_usuario = '" . sql($id) . "'";
        $res = query($sql);
        $modo = "listar";
        break;

    case "gestion_usuario_grupo":
        $modorenderizado = "gestion_usuario_grupo";
        $idagrupo = $_POST["idusuario"];
        $nombreagrupo = $_POST["nombreusuario"];
        $page->addvar('listado', 'idusuario', $idagrupo);
        $page->addvar('listado', 'nombreusuario', $nombreagrupo);
        break;

    case "grupo_incluido_usuario":
        $modorenderizado = "gestion_usuario_grupo";
        $idagrupo = $_POST["idusuario"];
        $nombreagrupo = $_POST["nombre"];
        //eni
        $page->addvar('listado', 'idusuario', $idagrupo);
        $page->addvar('listado', 'nombreusuario', $nombreagrupo);
        break;
    case "eliminar_usuario_del_grupo":
        // eliminación desde el popup
        $sql = "DELETE FROM gruposelementos WHERE id_grupo =" . sql($_POST["idgrupo"]) . " AND id_usuario =" . sql($_POST["idusuario"]);
        queryrow($sql);
        exit();
        break;
    case "eliminar_usuario_del_grupo_listado":
        //eliminacion del listado
        $sql = "DELETE FROM gruposelementos WHERE id_gruposelementos =" . sql($_POST["idgruposelementosaborrar"]);
        queryrow($sql);
        exit();
        break;

    case "actualizar_grupo":
        $sql = "UPDATE grupos SET nombregrupo = '" . sql($_POST["nombregrupo"]) . "' WHERE id_grupo =" . sql($_POST["idgrupo"]);
        queryrow($sql);
        exit();
        break;

    case "crear_grupo":
        $nombregrupo = $_POST["nombregrupo"];
        $sql = "SELECT nombregrupo FROM grupos WHERE nombregrupo = '" . sql($nombregrupo) . "'";

        $row = queryrow($sql);
        if ($row) {
            $result = false;
        } else {
            $result = true;
            $sql = "INSERT INTO grupos(nombregrupo, eliminado) VALUES ('" . sql($nombregrupo) . "',0)";
            queryrow($sql);
        }
        echo json_encode($result);
        exit();
        break;

    case "borrar_grupo":
        $sql = "DELETE FROM grupos WHERE id_grupo =" . sql($_POST["idgrupo"] * 1);
        queryrow($sql);
        $sql = "DELETE FROM gruposelementos WHERE id_grupo =" . sql($_POST["idgrupo"] * 1);
        queryrow($sql);
        exit();
        break;


    case "agnadir_usuario_al_grupo":
        $grupo = $_POST["idgrupo"];
        $usu = $_POST["idusuario"];
        $sql = "INSERT INTO gruposelementos(id_usuario, id_grupo) VALUES (" . sql($usu * 1) . "," . sql($grupo * 1) . ")";

        queryrow($sql);
        exit();
        break;

    case "recargar_grupos_listados":

        $usu = $_POST["idusuariogrupos"];
        $sql = "SELECT gruposelementos.id_gruposelementos, grupos.nombregrupo, grupos.descripcion FROM `gruposelementos` LEFT JOIN grupos ON gruposelementos.id_grupo = grupos.id_grupo WHERE id_usuario = " . sql($usu) . " ORDER BY grupos.nombregrupo";
        $resgrp = query($sql);
        while ($rowgroup = Row($resgrp)) {
            $arrdatos[] = array("idgruposelementos" => $rowgroup["id_gruposelementos"], "nombregrupo" => $rowgroup["nombregrupo"]);

        }
        $grupos = array("lineas" => $arrdatos, "error" => 0);
        echo json_encode($grupos);
        exit();
        break;

    case "cargar_grupos":
        // añadirmos todos los grupos al combo
        $like = trim($_POST["filtro"]);
        if ($like <> "") {
            $like = " AND nombregrupo LIKE '%" . sql($like) . "%' ";
        }
        $sql = "SELECT id_grupo, nombregrupo  FROM grupos WHERE eliminado = 0 " . $like . " ORDER BY nombregrupo";
        $idagrupo = $_POST["idusuariogrupos"];
        $res = query($sql);
        $i = 0;
        $arrdatos = array();
        while ($row = Row($res)) {
            ++$i;
            if (($i % 2) == 0) {
                $pi = "odd";
            } else {
                $pi = "even";
            }
            $sql = "SELECT id_grupo FROM `gruposelementos` WHERE id_usuario = " . $idagrupo . " AND id_grupo =" . $row["id_grupo"];

            //$resquery= queryrow($sql);
            if (queryrow($sql)) {
                $valor = "checked";
            } else {
                $valor = "";
            }
            $arrdatos[] = array("idgrupo" => $row["id_grupo"], "nombregrupo" => $row["nombregrupo"], "perteneceagrupo" => $valor, "linea" => $pi);
        }
        $grupos = array("lineas" => $arrdatos, "error" => 0);
        echo json_encode($grupos);

        exit();
        break;


}

switch ($modorenderizado) {
    case "listar":
        $sql = "SELECT id_usuario, nombreusuario, direccioncorreo, administrador, empresa  FROM usuarios WHERE eliminado = 0";
        $res = query($sql);
        $i = 0;

        $arrdatos = array();
        while ($row = Row($res)) {
            ++$i;
            if (($i % 2) == 0) {
                $pi = "odd";
            } else {
                $pi = "even";
            }
            if ($row["administrador"]) {
                $administrador = "js-admin";
            } else {
                $administrador = "";
            }
            //// vamos a sacar las etiquetas de los grupos a los que pertenece el usuario

            $sql = "SELECT gruposelementos.id_gruposelementos, grupos.nombregrupo, grupos.descripcion FROM `gruposelementos` LEFT JOIN grupos ON gruposelementos.id_grupo = grupos.id_grupo WHERE id_usuario = " . sql($row["id_usuario"]) . " ORDER BY grupos.nombregrupo";

            $resgrp = query($sql);
            $grppt = "";
            $grppt = "<div class=grupo-etiquetas id=htmlusuariogrupo" . $row["id_usuario"] . ">";
            while ($rowgroup = Row($resgrp)) {
                $grppt = $grppt . "<div class=etiqueta-grupo id=etiqueta" . $rowgroup["id_gruposelementos"] . ">";
                $grppt = $grppt . "<input type='image' src='img/etiqueta-x.png' title='Eliminar Grupo' onclick='eliminar_del_grupo(" . $rowgroup["id_gruposelementos"] . ",\"" . $rowgroup["nombregrupo"] . "\")'/>";
                $grppt = $grppt . "<span>" . $rowgroup["nombregrupo"] . "</span>";
                $grppt = $grppt . "</div>";

            }
            $grppt = $grppt . "</div>";

            $arrdatos[] = array("idusuario" => $row["id_usuario"], "nombreusuario" => $row["nombreusuario"], "DireccionCorreo" => $row["direccioncorreo"], "Empresa" => $row["empresa"], "linea" => $pi, "administrador" => $administrador, "grupos" => $grppt);

        }
        $page->setAttribute('listado', 'src', 'mod_usuarios_listado.html');
        $page->addRows('list_usuarios', $arrdatos);
        break;

    case "modificar":
        // leemos registro y pasamos los datos
        $dato = (int)($_POST["idusuario"]);
        $sql = "SELECT id_usuario, nombreusuario, direccioncorreo, empresa, password, administrador FROM usuarios WHERE id_usuario = '" . sql($dato) . "'";
        $row = queryrow($sql);

        $page->setAttribute('listado', 'src', 'mod_usuarios_modificar.html');
        $page->addvar('listado', 'idusuario', $row["id_usuario"]);
        $idusuario = $row["id_usuario"];
        $page->addvar('listado', 'nombre', $row["nombreusuario"]);
        $page->addvar('listado', 'password', $row["password"]);
        $page->addvar('listado', 'direccioncorreo', $row["direccioncorreo"]);
        $page->addvar('listado', 'empresa', $row["empresa"]);

        if ($row["administrador"]) {
            $administrador = "checked";
        } else {
            $administrador = "";
        }

        $page->addvar('listado', 'administrador', $administrador);
        $page->setAttribute('listado', 'src', 'mod_usuarios_modificar.html');
        break;


    case "crear":
        // pasamos datos en blanco
        $page->setAttribute('listado', 'src', 'mod_usuarios_crear.html');
        $page->addRows('list_usuarios', $arrdatos);
        break;

}

$page->addVar('headbar', 'usuario_logueado', getSesionDato("nombreusuario"));
$page->Volcar();
//$page->dump();
