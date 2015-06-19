<?php
define("CAPTURARTODOBUG", "true");

include("tool.php");
//include(__ROOT__ . "/inc/paginabasica.inc.php");

$esadmin="oculto";
if (getSesionDato("esadmin")){
	$esadmin="";
} 
$page->addVar("listado","esadmin",$esadmin);
$page->setAttribute( 'menu', 'src', 'menu.html' );


$page->ajustaMigas($cat_s);

$modorenderizado= "listar";
$modo = $_POST["modo"];

switch ($modo) {
    case "modificar_grupo":
       $modorenderizado = "modificar";
       break;
    case "crear_grupo";
       $modorenderizado= "crear";
       break;
    case "eliminar_dato":
       $id = (int)$_POST["id"];
       $sql = "UPDATE grupos SET eliminado = 1 WHERE id = '" . sql($id). "'";
       $res = query($sql);
       break;
  
    case "grupo_creado":
       $sql = "INSERT INTO grupos(nombre, descripcion) VALUES ('" . sql($_POST["nombre"]) . "','".sql($_POST["descripcion"])."')";
       $res = query($sql); 
       // leemos la ultima inserción para leer el id y  realizar el has de la contraseña.
       $modo = "listar";
       break;
    
    case "grupo_modificado":
       $id= (int)$_POST["id"];
       $sql = "UPDATE grupos SET  nombre = '" .sql($_POST["nombre"]) . "', descripcion = '".sql($_POST["descripcion"]) ."' WHERE id = '". sql($id)."'";
       $res = query($sql);
       $modo = "listar";
       break;
   
}

switch ($modorenderizado) {
    case "listar":
       $sql = "SELECT id, nombre, descripcion  FROM grupos WHERE eliminado = 0";
       $res = query($sql);
       $i=0;
       $arrdatos = array();
       while ($row = Row($res)) {
            ++$i;
            if (($i % 2 )== 0){
                $pi = "odd";
            }
            else { 
                $pi = "even";
            }
            $arrdatos[] = array("id" => $row["id"], "nombre" => $row["nombre"], "descripcion" => $row["descripcion"] , "linea" => $pi );
        }
       $page->setAttribute( 'listado', 'src', 'mod_grupos_listado.html' );
       $page->addRows('list_grupos', $arrdatos);
       break;
    
    case "modificar":
       // leemos registro y pasamos los datos
       $dato = (int)($_POST["id"]);
       $sql = "SELECT id, nombre, descripcion FROM grupos WHERE id = '".sql($dato)."'" ;
       $res = query($sql); 
       $row= Row($res);
       $page->setAttribute( 'listado', 'src', 'mod_grupos_modificar.html' );
       $page->addvar('listado', 'id', $row["id"]);
       $page->addvar('listado', 'nombre', $row["nombre"]);
       $page->addvar('listado', 'descripcion', $row["descripcion"]);
       break;
        
    case "crear":
       // pasamos datos en blanco
       $page->setAttribute( 'listado', 'src', 'mod_grupos_crear.html' );
       $page->addvar('listado', 'id', '');
       $page->addvar('listado', 'nombre', '');
       $page->addvar('listado', 'password', '');
       break;
    
}
$page->Volcar();
