<?php
/**
 *
 *
 * Handle file uploads via XMLHttpRequest
 */


function CleanPath($path){

	$path = preg_replace('/[^(\x20-\x7F)]*/','', $path);

	$path = str_replace(" ","_",$path);
	$path = str_replace("(","_",$path);
	$path = str_replace(")","_",$path);
	$path = str_replace(",","_",$path);
	$path = str_replace(":","_",$path);
	$path = str_replace("\\","_",$path);
	$path = str_replace("\"","'",$path);

	return $path;
}


class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($realSize != $this->getSize()){
            return false;
        }

		error_log("info: guardando desde FileXhr");

        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;
    }

    function getName() {
        return $_GET['qqfile'];
    }

    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];
        } else {
            throw new Exception('Getting content length is not supported.');
        }
    }
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {

		error_log("info: guardando desde FileForm");

        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
			error_log("fatal: guardando desde FileForm");

            return false;
        }

		$info = filesize($path);
		error_log("info,tamguardar ($path): ". var_export($info,true));


        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;
    var $limite_sobrepasado = false;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){
        $allowedExtensions = array_map("strtolower", $allowedExtensions);

        $this->allowedExtensions = $allowedExtensions;
        $this->sizeLimit = $sizeLimit;

        $this->checkServerSettings();

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false;
        }
    }

    private function checkServerSettings(){
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            //die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
            $this->limite_sobrepasado = true;
        }
    }

    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $baseDir, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Error del servidor. Configure el directorio de datos para escritura..");
        }

        if (!$this->file){
            return array('error' => 'No se subio ficheros.');
        }

        $size = $this->file->getSize();

        if ($size == 0) {
            return array('error' => 'El fichero esta vacio (0 bytes)');
        }

        if ($size > $this->sizeLimit) {
            return array('error' => 'El fichero es demasiado grande');
        }

        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'Tipo de fichero no valido, debe ser '. $these . '.');
        }

		$filenameSave = CleanPath($filename);

        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filenameSave . '.' . $ext)) {
                $filenameSave .= rand(10, 99);
            }
        }
		

        if ($this->file->save($uploadDirectory . $filenameSave . '.' . $ext)){
            return array('success'=>true,'filename'=>($filenameSave.'.'.$ext),'uploadir'=>$uploadDirectory, 'localpath'=>($baseDir. $filenameSave . "." . $ext));
        } else {
            return array('error'=> 'No se puede guardar el fichero.' .
                'La subida se cancelo por un error desconocido.');
        }

    }
}


?>