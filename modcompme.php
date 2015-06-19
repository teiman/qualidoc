<?php


include("tool.php");
include_once(__ROOT__."/inc/modcontenidos.inc.php");
include_once(__ROOT__."/inc/gestion_permisos.inc.php");

if ( !getSesionDato("eslogueado") ){
	header("Location: conexioncerrada.php");
	exit();
}


$modo = $_REQUEST["modo"];

$cat = CleanID($_REQUEST["cat"]);

$cat_s = sql($cat);

$idcategoria = $_REQUEST["idcategoria"];

$con = new contenido();



switch($modo){
	
	case "editar":

		$id_contenido = CleanID($_REQUEST["id_contenido"]);
		$con->Load($id_contenido);
		
		break;

	case "guardarcambios":

		$id_contenido = $_REQUEST["id_contenido"];


		if($con->Load($id_contenido)){

			$descripcion = $_REQUEST["descripcion"];
			$con->set("descripcion",$descripcion);
                        $con->set("id_categoria",$_POST["categorias"]);
			$con->set("nombre",$_REQUEST["nombre_contenido"]);
                        $compartidos = json_decode($_REQUEST["compartido"],true);
                        actualiza_permisos($compartidosm,$id_contenido);
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




/* ----------------  FIXING...  ------------------
 * 
 *
 */

reajustarHojas();

if(0){
	//correcciones pruebas
	query("UPDATE contenidos SET tipo='datos' WHERE tipo='' ");
	query("UPDATE contenidos SET tipo='texto' WHERE tipo='txt' ");
	query("UPDATE contenidos SET tipo='word' WHERE tipo='doc' ");
}

/*
 *
 *  ----------------  --------  ------------------ */


if ( $modo == "editar" ){

	$tipo_fichero = $con->get("tipo");
        //cargamos las categorias disponibles
        $combocategoriashoja = getComboCategoriasHoja();
        //
	switch($tipo_fichero){
		case "html":
			$page->setAttribute( 'listado', 'src', 'visor_inlineeditor_contenidos.html' );
                        $page->addvar('listado', 'combocategoriashoja',$combocategoriashoja);
			$contenido = $con->getContenido();
			$page->addVar( 'listado', 'articulo', $contenido );

			break;
		default:
			$page->setAttribute( 'listado', 'src', 'visor_uploader_contenidos.html' );
			$page->addVar( 'listado' ,'modouploader' , 'subiendofichero');
			break;
	}	

	$page->addArrayFromCursor( "listado",$con, array("nombre","id_categoria","id_contenido","descripcion","nombre","path_archivo","tipo") );

	$page->addVar( 'listado', 'id_actual', $cat_s );
	$page->addVar( 'listado', 'id_contenido', $id_contenido );
	$page->addVar( 'listado', 'modo' , "guardarcambios");

	$page->addVar( 'listado', 'enalta', 'normal');
	$page->addVar( 'listado', 'enmod', 'none');
}


if ($modo == "altaarticulo"){
        $combocategoriashoja = getComboCategoriasHoja();
        $page->addVar('listado', 'combocategoriashoja', $combocategoriashoja);
	$page->setAttribute( 'listado', 'src', 'visor_inlineeditor_contenidos.html' );

	$page->addVar( 'listado', 'id_actual', $cat_s );
	$page->addVar( 'listado', 'id_contenido', "-1" );
	$page->addVar( 'listado', 'modo' , "guardaraltaarticulo");

	$page->addVar( 'listado', 'enalta', 'none');
	$page->addVar( 'listado', 'enmod', 'normal');

}

if ($modo == "altafichero"){
        $combocategoriashoja = getComboCategoriasHoja();
        $page->addVar('listado', 'combocategoriashoja', $combocategoriashoja);
	$page->setAttribute( 'listado', 'src', 'visor_uploader_contenidos.html' );
	$page->addVar( 'listado', 'id_actual', $cat_s );
	$page->addVar( 'listado', 'id_contenido', "-1" );
	$page->addVar( 'listado', 'modo' , "guardaraltafichero");

	//$page->addVar();
	//subiendofichero
	$page->addVar( 'listado' ,'modouploader' , 'subiendoficheronuevo');
	
	$page->addVar( 'listado', 'enalta', 'none');
	$page->addVar( 'listado', 'enmod', 'normal');


}


if ( $modo == "listando" ) {

	$page->setAttribute( 'listado', 'src', 'mod_compartido_conmigo.html' );

	$page->addVar( 'listado', 'id_actual',	$cat_s );

	$filtro = $_REQUEST["tipofiltro"];

	if ( $filtro =="tipofichero" ){
		$tipo_s = sql($_REQUEST["tipodefichero"]);

		$extraTipo = " AND contenidos.tipo = '$tipo_s' ";
	} else if ( $filtro == "categoria"  ){
		$id_categoria_s = sql(CleanID($_REQUEST["filtrocategoria"]));

		$extraCategoria = " AND contenidos.id_categoria = '$id_categoria_s'  ";
	}


	/* ----------------  LISTANDO  ------------------
	 *
	 */
        $idusrsesion = getSesionDato("id_usuario");
    

	$sql = "SELECT *, usuarios.nombreusuario, contenidos.nombre_contenido as nombrecontenido, contenidos.fecha_creacion, categorias.nombre as nombrecategoria FROM contenidos ".
			"  JOIN categorias ON contenidos.id_categoria = categorias.id_categoria " .
                        " JOIN  usuarios ON contenidos.id_propietario = usuarios.id_usuario".
			" WHERE contenidos.eliminado=0 " . $extraTipo . $extraCategoria  . " ORDER BY contenidos.id_contenido DESC LIMIT 100" ;
        
        $sql = "(SELECT contenidos.nombre_contenido as nombrecontenido, contenidos.fecha_creacion, usuarios.nombreusuario, contenidos.tipo, contenidos.id_contenido," 
               ."  categorias.nombre as nombrecategoria, contenidos.id_categoria, id_documento_compartido "
               ."  FROM `permisosdocumentos` INNER JOIN grupos ON grupos.id_grupo = permisosdocumentos.id_grupo_permitido"
               ." INNER JOIN gruposelementos ON grupos.id_grupo = gruposelementos.id_grupo INNER JOIN contenidos ON" 
               ." contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
               ." INNER JOIN categorias ON contenidos.id_categoria = categorias.id_categoria"
               ." INNER JOIN usuarios ON contenidos.id_propietario = usuarios.id_usuario" 
               ." WHERE gruposelementos.id_usuario = %d AND contenidos.id_propietario <> %d"
               ." AND contenidos.eliminado = 0)" 
               ." UNION (SELECT contenidos.nombre_contenido as nombrecontenido, contenidos.fecha_creacion, usuarios.nombreusuario, contenidos.tipo, contenidos.id_contenido,"
               ."  categorias.nombre as nombrecategoria, contenidos.id_categoria, id_documento_compartido FROM permisosdocumentos INNER JOIN contenidos ON"
               ." contenidos.id_contenido = permisosdocumentos.id_documento_compartido"
               ." INNER JOIN categorias ON contenidos.id_categoria = categorias.id_categoria" 
               ." INNER JOIN  usuarios ON contenidos.id_propietario = usuarios.id_usuario"
               ." WHERE id_usuario_permitido = %d AND contenidos.id_propietario <> %d"
               ." AND contenidos.eliminado = 0) ORDER BY  fecha_creacion DESC";
        $sql = parametros($sql, $idusrsesion, $idusrsesion, $idusrsesion, $idusrsesion);
        
    
      

	$res = query($sql);

	$tt = 0;

	while($row = Row($res)){

		$row["ext"] = getExtension($row["path_archivo"]);
		$row["tt"] = $tt++;
                $idcategoria = $row["id_categoria"];
                
                // las categorias a las que no tengamos acceso  no debe aparecer el nombre
                
                $sql = parametros("SELECT permisosdocumentos.id_categoria_compartida,gruposelementos.id_usuario FROM permisosdocumentos "
                    . " INNER JOIN grupos on grupos.id_grupo = id_grupo_permitido" 
                    . " INNER JOIN gruposelementos on gruposelementos.id_grupo = grupos.id_grupo "
                    . " where id_usuario = %s AND id_categoria_compartida = %d",$idusrsesion, $idcategoria);
                
                $rrow = queryrow($sql);
                if (!$rrow){
                  $row["nuevacategoria"]= "------";
                }else{
                  $row["nuevacategoria"]= $row["nombrecategoria"];  
                }
       		$rows[]= $row;
	}

	$page->addRows('listadocumentos', $rows );


	if (!$tt){
			$page->addVar("listado","mensajevacio","<div class='mensajeaviso bocadillo-aviso' style='margin-top:32px'>Sin resultados. <a href='modcontenidos.php?cat=$cat_s'>Ver todos</a></div>" );
			$page->addVar("listado","cssocultarlistado","oculto");
	}


	$combocategoriashoja = getComboCategoriasHoja();

	$page->addVar( 'listado', 'combocategoriashoja',	$combocategoriashoja );


	$tiposdeficheros = "<option value='html'>Documento HTML</option".
		"<option value='pdf'>Documento PDF</option".
		"<option value='word'>Documento de word (.doc)</option".
		"<option value='video'>Videos</option".		
		"<option value='datos'>Otros</option";

	$page->addVar( 'listado', 'tiposdeficheros',	$tiposdeficheros );

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



?>