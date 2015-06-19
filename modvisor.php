<?php


include("tool.php");
include_once(__ROOT__ . "/inc/visor.inc.php");
//include_once(__ROOT__ . "/class/contenido_checklist.class.php");

if ( !getSesionDato("eslogueado") ){
	header("Location: conexioncerrada.php");
	exit();
}

$cat_s = sql(CleanID($_REQUEST["cat"]));



$id_contenido_s = sql(CleanID($_REQUEST["id_contenido"]));


$con = new contenido();
$cargado = $con->Load($id_contenido_s);


if (!$cargado){
	$page->setAttribute( 'listado', 'src', 'visorerror.html' );
} else {
	$ext = $con->getTipo();

	switch($ext){
        case "imagen":{
            $page->setAttribute( 'listado', 'src', 'visorimagen.html' );
            $page->addArrayFromCursor( "listado",$con, array("nombre","id_categoria","id_contenido","descripcion") );
            $page->addVar( 'listado', 'id_actual',	$cat_s  );
            break;
        }

        case "chklst":{
            include_once(__ROOT__ . "/modvisor_checklist.snip.php");
            break;
        }

        case "rss": {
            include_once(__ROOT__ . "/class/contenido_opendata.class.php");
            $page->setAttribute( 'listado', 'src', 'visorrss.html' );

            $con = new contenido_opendata();
            $con->Load($id_contenido_s);

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
            break;
        }


		case "video": {
			$page->setAttribute( 'listado', 'src', 'visorvideos.html' );
			$page->addArrayFromCursor( "listado",$con, array("nombre","id_categoria","id_contenido","descripcion","path_archivo") );
			$page->addVar( 'listado', 'id_actual',	$cat_s  );
			
			}
			break;
		case "pdf": {
			$page->setAttribute( 'listado', 'src', 'visorpdf.html' );
			$page->addArrayFromCursor( "listado",$con, array("nombre","id_categoria","id_contenido","descripcion") );
			$page->addVar( 'listado', 'id_actual',	$cat_s  );

			}
			break;

		case "html": {
			$page->setAttribute( 'listado', 'src', 'visorhtml.html' );
			$page->addArrayFromCursor( "listado",$con, array("nombre_contenido","id_categoria","id_contenido","descripcion") );
			$page->addVar( 'listado', 'html',	$con->getContenido()  );
			$page->addVar( 'listado', 'id_actual',	$cat_s  );
			}
			break;

		default:
		case "datos": {
			$page->setAttribute( 'listado', 'src', 'visordescarga.html' );
			$page->addArrayFromCursor( "listado",$con, array("nombre","id_categoria","id_contenido","descripcion","path_archivo") );
			$page->addVar( 'listado', 'id_actual',	$cat_s  );
			}
			break;
	}
}

$page->setAttribute( 'menu', 'src', 'menu.html' );
$page->ajustaMigas($cat_s);

$submodo = $_REQUEST["submodo"];

if ($submodo=="volvercontenidos"){
	$volverhtml = "modcontenidos.php?cat=$cat_s";
} else{
	$volverhtml = "javascript:history.go(-1)";
}

$page->addVar("listado","volverhref",$volverhtml);

$esadmin="oculto";
if (getSesionDato("esadmin")){
	$esadmin="";
} 
$page->addVar("listado","esadmin",$esadmin);



$page->addVar('headbar', 'usuario_logueado', getSesionDato("nombreusuario"));
$page->Volcar();


