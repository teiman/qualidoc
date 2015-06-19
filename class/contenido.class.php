<?php


include_once(__ROOT__."/inc/modcontenidos.inc.php");

function reajustarHojas(){
	$sql = "SELECT * FROM categorias WHERE eliminado=0";

	$res = query($sql);

	while($row = Row($res)){
		$self_s = $row["id_categoria"];
		$data = queryrow("SELECT id_categoria FROM categorias WHERE id_padre='$self_s' AND eliminado=0");

		$esHoja = 1;

		if ($data["id_categoria"])
			$esHoja = 0;

		query("UPDATE categorias SET eshoja='$esHoja' WHERE id_categoria='$self_s'");
	}
}



/* --------------  */

/*
function func_txt($file="ejemplos/test.txt"){
	return file_get_contents($file);
}


function func_extraepdf($file=""){
	//sudo apt-get install poppler-utils
	$tmp = tempnam("/tmp", "FOO");

	$cmd = " /usr/bin/pdftotext -layout -q -nopgbrk ejemplos/prueba.pdf $tmp";
	$return = system($cmd);

	//if (!$return) {
	//	return "ERROR: no se pudo crear texto\ncmd: $cmd";
	//}

	return func_txt($tmp);
}

function func_extraedoc($file=""){

	//sudo apt-get install poppler-utils
	$tmp = tempnam("/tmp", "FOO");

	$cmd = " /usr/bin/antiword -i 1  ejemplos/prueba.doc > $tmp";
	$return = system($cmd);


	return func_txt($tmp);
}


function func_htm($file=""){
	//sudo apt-get install poppler-utils
	$tmp = tempnam("/tmp", "FOO");

	$cmd = " /usr/bin/lynx --dump ejemplos/ejemplo2.html > $tmp";
	$return = system($cmd);

	return func_txt($tmp);
}

*/

/* --------------  */


class contenido extends Cursor {

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("contenidos", "id_contenido", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("nombre");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo contenido"));
	}


	function Alta(){
		global $UltimaInsercion;

		$data = $this->export();

		$coma = false;
		$listaKeys = "";
		$listaValues = "";

		foreach ($data as $key=>$value){
			if ($coma) {
				$listaKeys .= ", ";
				$listaValues .= ", ";
			}

			$value = sql($value);

			$listaKeys .= " `$key`";
			$listaValues .= " '$value'";
			$coma = true;
		}

		$sql = "INSERT INTO contenidos ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

		//if ($resultado){
		    $this->set("id_contenido",$UltimaInsercion,FORCE);
		//}

		$this->ActualizarIndexado();

		return $resultado;
	}

	function ActualizarIndexado(){
		
		$this->Indexar();
	}



	function Modificacion () {

		$data = $this->export();

		$sql = CreaUpdateSimple($data,"contenidos","id_contenido",$this->get("id_contenido"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo contenido");
			return false;
		}


		$this->ActualizarIndexado();

		return true;
	}

	function getExtension_raw($name){		

		$file = basename($name);
		$info = pathinfo($file);

		$ext = strtolower($info["extension"]);

		return $ext;
	}

	function getExtension(){
		$name = $this->get("path_archive");

		return $this->getExtension_raw($name);
	}

	function getTipo(){
		return $this->get("tipo");

	}


	function Indexar(){
		$cuantosCaracteres = 8192;

		$self_s = $this->get('id_contenido');

		$row = queryrow("SELECT id_indice FROM indice WHERE id_contenido='$self_s' ");

		$id_indice_s = CleanID($row["id_indice"]);

		$func = getFileFunction($this->get("path_archivo"));
		$file = $this->getValidFile();
		$data = $func($file);

		$texto_s =  sql(substr($data["texto"], 0, $cuantosCaracteres));
		$id_contenido_s = $this->get("id_contenido");

		if ($id_indice_s){
			$sql = "UPDATE indice SET texto='$texto_s' WHERE id_indice='$id_indice_s'  ";
			query($sql);
		} else {
			$sql = "INSERT INTO indice (id_contenido,texto) VALUES ( '$id_contenido_s','$texto_s' )";
			query($sql);			
		}
	}



    function crearRecursivo($filename){

        $partes = explode("/",$filename);

        $final = "";
        foreach($partes as $parte){

            if($parte){
                $prefinal = $final;
                $final =  $final . "/" . $parte;
            }
        }

        $prefinal = $prefinal ."/";

        system("mkdir -p $prefinal");
        system("touch $filename");
    }

    function getValidFile(){

		$basePath = getParametro("basePath");
        $path_db = $this->get("path_archivo");
		$path = $basePath . $path_db;

        if(!file_exists($path)){
            error_log("getValidFile: no existe el archivado:($basePath)($path_db) => $path. Se ha creado vacio");
            $this->crearRecursivo($path);
        }

		return $path;
	}


	function getContenido(){
		$file = $this->getValidFile();
		return file_get_contents($file);
	}

	function setContenido($data){
		$file = $this->getValidFile();

        error_log("Se va a guardar en $file...");
	    file_put_contents($file,$data);

        $contenido = $this->getContenido();
        error_log("se guardo: ($contenido)");
	}


    function guardarResultado($datos,$puntos){
        $id_contenido = $this->get("id_contenido");
        $id_listado = $this->get("id_listado");

        $sql = parametros("SELECT umbral_conformidad FROM listados WHERE id_listado=%d ",$id_listado);
        $data = queryrow($sql);

        $umbral = $data["umbral_conformidad"];

        $es_conforme = ($umbral<=$puntos)?1:0;

        $this->setMetadatos($datos);

        query(parametros("UPDATE contenidos_metadatos SET puntos=%d,es_conforme=%d WHERE id_contenido=%d",$puntos,$es_conforme,$id_contenido));

        return array("es_conforme"=>$es_conforme,"umbral"=>$umbral*1,"puntos"=>$puntos*1);
    }



    function setMetadatos($datos){
        $id_contenido = $this->get("id_contenido");

        //Existe?
        $data = queryrow(parametros("SELECT id_contenido FROM contenidos_metadatos WHERE id_contenido=%d",$id_contenido));

        if(!$data){
            //se crea
            query(parametros("INSERT INTO contenidos_metadatos (id_contenido,json_metadatos) VALUES( %d,'%s' )",$id_contenido,$puntos));
        } else {
            //se actualiza
            query(parametros("UPDATE contenidos_metadatos SET json_metadatos = '%s' WHERE id_contenido=%d",$datos,$id_contenido));
        }
    }

	function getRandomArticle(){

		$rdir = getRandomDir();

		$htmlfile =   $rdir["dirtxt"]. "articulo_". md5(time()) . ".html";
		
		$baseDir = getParametro("basePath");
		createPath($baseDir,$rdir["dirs"]);


        //$abspath = $baseDir .  $htmlfile;
        //$existe = file_exists($abspath);
        //$tam = filesize($abspath);


		$this->set("path_archivo",$htmlfile);
		$this->setContenido(" ");
		
	}


	function regularTipo($nuevoFichero,$nohtml=true){
		$ext = $this->getExtension_raw($nuevoFichero);

		$ext = strtolower($ext);

		switch($ext){
			case "html":
			case "htm":
			case "asp":
			case "aspx":
				$tipo = ($nohtml)?"datos":"html";
				break;

			case "jpeg":
			case "jpg":
			case "png":
			case "gif":
				$tipo = "imagen";
				break;

			case "flv":
				$tipo = "video";
				break;
			case "pdf":
			case "ps":
				$tipo = "pdf";
				break;

			default:
				$tipo = "datos";

                if($this->get("id_listado")){
                    $tipo = "checklst";
                }else
                if($this->get("id_rss")){
                    $tipo = "rss";
                }
				break;
		}

		//error_log("nt:[".$ext."],para:[" . $nuevoFichero ."]");
		$this->set("tipo",$tipo);
	}
}


