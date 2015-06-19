<?php

//require ("kint/Kint.class.php");
include("tool.php");
include(__ROOT__ . "/inc/busquedas.inc.php");

if (!getSesionDato("eslogueado")) {
    header("Location: conexioncerrada.php");
    exit();
}
if (!getSesionDato("esadmin")) {
    header("Location: conexioncerrada.php");
    exit();
}


$modo = $_REQUEST["modo"];
$modorenderizado = "listar";

$cat = CleanID($_REQUEST["cat"]);

$cat_s = sql($cat);


switch ($modo) {
    case "modificar_carpeta":
        $modorenderizado = "modificar";
        $modo = "";

        break;
    case "crear_carpeta";
        $modorenderizado = "crear";
        $modo = "";
        break;
    case "carpeta_creada":
        $id_padre = $_POST["id_padre"];
        $nombrecategoria = $_POST["nombrecategoria"];
        $posicion = $_POST["posicion"];
        $compartidos = json_decode($_REQUEST["compartido"], true);
        $sql = parametros("INSERT INTO categorias (id_padre,nombre,posicion)VALUES (%d,'%s',%d)", $id_padre, $nombrecategoria, $posicion);
        query($sql);
        $id_categoria = $UltimaInsercion;
        forEach ($compartidos as $valores) {
            if ($valores["tipo_elemento"] == 0) {
                $idusr = $valores["id_elemento"];
                $idgrp = 0;
            } else {
                $idusr = 0;
                $idgrp = $valores["id_elemento"];
            }
            $sql = "INSERT INTO permisosdocumentos (id_documento_compartido, id_categoria_compartida, id_usuario_permitido, id_grupo_permitido) VALUES (0,%d,%d,%d)";
            $sql = parametros($sql, $id_categoria, $idusr, $idgrp);
            queryrow($sql);
        }
        $cat_s = $id_padre;
        break;

    case "carpeta_modificada":
        $id_padre = $_POST["id_padre"];
        $idcategoria = $_POST["id_categoria"];
        $nombrecategoria = $_POST["nombrecategoria"];
        $posicion = $_POST["posicion"];
        $compartidos = json_decode($_REQUEST["compartido"], true);
        $sql = "UPDATE categorias SET posicion=%d, nombre='%s' WHERE id_categoria = %d";
        $sql = parametros($sql, $posicion, $nombrecategoria, $idcategoria);
        queryrow($sql);

        // ahora añadimos los usuarios y grupos con los que se comparte
        $sql = parametros("DELETE FROM permisosdocumentos WHERE id_documento_compartido = 0 and id_categoria_compartida = %d", $idcategoria);
        queryrow($sql);
        forEach ($compartidos as $valores) {
            if ($valores["tipo_elemento"] == 0) {
                $idusr = $valores["id_elemento"];
                $idgrp = 0;
            } else {
                $idusr = 0;
                $idgrp = $valores["id_elemento"];
            }
            $sql = "INSERT INTO permisosdocumentos (id_documento_compartido, id_categoria_compartida, id_usuario_permitido, id_grupo_permitido) VALUES (0,%d,%d,%d)";
            $sql = parametros($sql, $idcategoria, $idusr, $idgrp);
            queryrow($sql);
        }
        $cat_s = $id_padre;
        break;
    case "eliminar_carpeta":
        function borradoRecursivo($id_categoria){
            $sql = parametros("SELECT id_categoria FROM categorias WHERE id_padre=%d",$id_categoria);

            $res = query($sql);
            while($row = Row($res)){
                borradoRecursivo($row["id_categoria"]);
            }

            $sql = parametros("UPDATE categorias SET eliminado=1 WHERE id_categoria=%d",$id_categoria);
            query($sql);
        }

        $id_categoria = $_REQUEST["id_categoria"];
        borradoRecursivo($id_categoria);

        $id_padre = $_REQUEST["id_padre"];
        $cat_s = $id_padre;
        break;
    case "nuevacategoria":
        $modorenderizado = "listar";
        $nombre_s = sql(trim($_REQUEST["nombre_x"]));
        $peso_s = sql(CleanID($_REQUEST["posicion_x"]));
        $id_padre_s = sql(CleanID($_REQUEST["id_categoria_actual"]));
        $sql = "INSERT INTO categorias (id_padre,nombre,posicion)VALUES ('$id_padre_s','$nombre_s','$peso_s')";
        query($sql);
        //reajustarHojas(); ahora esta en distinta panalla
        // actualizamos permisos
        break;

    case "guardarcambioscategorias":

        for ($t = 0; $t < 200; $t++) {
            if (!isset($_REQUEST["id_categoria_" . $t])) continue;
            $id_categoria_s = sql(CleanID($_REQUEST["id_categoria_" . $t]));
            if (!$id_categoria_s) continue;

            $nombre_s = sql($_REQUEST["nombre_" . $t]);
            if (!$nombre_s) continue;

            $posicion_s = sql(CleanID($_REQUEST["posicion_" . $t]));


            if ($nombre_s == "ELIMINADO") {
                query("UPDATE categorias SET eliminado=1 WHERE id_categoria='$id_categoria_s' ");
                continue;
            }

            //existe->cambiar, sino existe,
            global $FilasAfectadas;
            $sql = parametros("UPDATE categorias SET nombre='%s', posicion ='%s' WHERE id_categoria= %d", $nombre_s, $posicion_s, $id_categoria_s);
            $res = query($sql);


        }
        reajustarHojas();
        break;

}


/* $sql = "SELECT id_categoria FROM categorias WHERE id_padre='$cat_s' and eliminado=0 ORDER BY posicion ASC ";

$res = query($sql);

$t = 10;
while($row=Row($res)){
	$id_categoria = $row["id_categoria"];
		
	$sql = "UPDATE categorias SET posicion = '$t' WHERE id_categoria='$id_categoria'";
	query($sql);
	$t += 10;
}

/* ---- */

$page = new Pagina();
$page->Inicia($template["modname"], "admin.html");


switch ($modorenderizado) {
    case "listar":
        $page->setAttribute('listado', 'src', 'edicion_categorias.html');
        $sql = "SELECT * FROM categorias WHERE id_padre ='$cat_s' and eliminado=0 ORDER BY posicion ASC "; //categoria actual
        $res = query($sql);
        $t = 0;
        $rows = array();

        $t = 0;
        while ($row = Row($res)) {
            if (!$row["id_categoria"]) continue;
            $row["tt"] = $t++;
            $rows[] = $row;
        }

        if (!$t) {
            $rows[] = array("CSSOCULTAR" => "ocultar");
            $page->addVar('listado', 'mensajeaviso', "No hay subcarpetas");
            $page->addVar('listado', 'cssocultarvacio', "ocultar");
            $page->addvar('listado', 'hay_elementos', 'ocultar');
        } else {
            $page->addVar('listado', 'mensajecss', "ocultar");
        }

        $page->addRows('listacategorias', $rows);
        if ($cat_s) {
            $row = queryrow("SELECT * FROM categorias WHERE id_categoria='$cat_s'");
            $page->addVar('listado', 'nombrecategoriaactual', $row["nombre"]);
            $page->addVar('listado', 'idpadrecategoriaactual', $row["id_padre"]);

        } else {

            $page->addVar('listado', 'nombrecategoriaactual', "Portada");
        }
        $page->addVar('listado', 'id_actual', $cat_s);

        if (!$cat_s) {
            //Se ocultan cosas que en el listado troncal no tienen interes
            $page->addVar('listado', 'cssocultartroncal', "ocultar");
        }

        $esadmin = "oculto";
        if (getSesionDato("esadmin")) {
            $esadmin = "";
        }
        $page->addVar("listado", "esadmin", $esadmin);
        $page->setAttribute('menu', 'src', 'menu.html');
        //$page->addRows('listadonovedades',getRowsNovedades() );
        break;


    case "modificar":
        $id_padre = $_POST["id_padre"];
        $page->addvar('listado', 'id_padre', $id_padre);
        // leemos registro y pasamos los datos

        $dato = (int)($_POST["id_categoria_editar"]);

        $sql = parametros("SELECT id_categoria, nombre, posicion FROM categorias WHERE id_categoria = %d", $dato);
        $row = queryrow($sql);

        $page->setAttribute('listado', 'src', 'mod_carpetas_edicion.html');
        $page->addvar('listado', 'accion', 'Modificar carpeta');
        $page->addvar('listado', 'modo', 'carpeta_modificada');
        $page->addvar('listado', 'id_categoria', $row["id_categoria"]);
        $page->addvar('listado', 'nombre_carpeta', $row["nombre"]);
        $page->addvar('listado', 'posicion', $row["posicion"]);

        //$page->addRows('list_grupos', $arrdatos);


        $sql = parametros("SELECT id_permiso, id_usuario_permitido, id_grupo_permitido, usuarios.nombreusuario, grupos.nombregrupo FROM permisosdocumentos "
            . " LEFT JOIN usuarios ON id_usuario_permitido = id_usuario left join grupos ON id_grupo_permitido = id_grupo WHERE id_categoria_compartida = %d ORDER BY nombregrupo,  nombreusuario", $dato);
        d("busco permisos documentos" . $sql);
        $res = query($sql);
        $compartido = array();

        $grppt = "<!-- modcat.php:modificar-->";
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
            $compartido[] = array("id_permiso" => $row["id_permiso"], "tipo" => $tipo, "id" => $id, "accion" => "");
            //
            $grppt = $grppt . "<div class='etiqueta-grupo js-etiq " . $tipoclass . "' id=etiqueta" . $id . "tipo" . $tipo . ">";
            $grppt = $grppt . "<input type='hidden' name= 'id_usrgrp' value=" . $id . " />";
            $grppt = $grppt . "<input type='hidden' name= 'tipo_usrgrp' value=" . $tipo . " />";
            $grppt = $grppt . "<input type='image' src='img/etiqueta-x.png' title='Eliminar' onclick='return no_compartido($id,$tipo,\"" . $nombre . "\")'/>";
            $grppt = $grppt . "<span>" . $nombre . "</span>";
            $grppt = $grppt . "</div><!-- /modcat.php:modificar-->";
        }

        $page->addvar('listado', 'lista_permisos', $grppt);
        $lcjson = json_encode($compartido);
        $page->addvar('listado', 'lista_compartido', $lcjson);

        break;
    case "crear":
        // pasamos datos en blanco
        $id_padre = $_POST["cat"];
        $page->setAttribute('listado', 'src', 'mod_carpetas_edicion.html');
        $page->addvar('listado', 'nombre_carpeta', '');
        $page->addvar('listado', 'id_padre', $id_padre);
        /* ------- Sugerimos un nuevo peso para las altas -------- */
        $sql = "SELECT max(posicion) as peso FROM categorias WHERE id_padre='$cat_s' ";
        $row = queryrow($sql);
        $page->addvar('listado', 'id_categoria', 0);
        $page->addvar('listado', 'posicion', $row["peso"] + 10);
        $page->addvar('listado', 'accion', 'Alta de carpeta');
        $page->addvar('listado', 'modo', 'carpeta_creada');
        // grupos vacios //
        $grppt = "";
        $grppt = "<div class=grupo-etiquetas id=html_permisos_categoria0>";
        $grppt = $grppt . "</div>";

        $page->addvar('listado', 'lista_permisos', $grppt);
        $lcjson = json_encode($compartido);
        $page->addvar('listado', 'lista_compartido', $lcjson);
        break;


}

$page->addVar('headbar', 'usuario_logueado', getSesionDato("nombreusuario"));
$page->Volcar();
