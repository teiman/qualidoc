<?php


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



?>
<form method="post" action="extraetextos.php">

<select name="modo">
<option value="htm">Fichero html</option>
<option value="txt">Fichero de texto</option>
<option value="pdf">Fichero pdf</option>
<option value="doc">Fichero doc</option>
</select>


<input type="submit" value="Enviar" />

</form>

<?php

$modo = $_REQUEST["modo"];

switch($modo){
	case "htm":
		$data = func_htm();
		break;
	case "txt":
		$data = func_txt();
		break;
	case "pdf":
		$data = func_extraepdf();
		break;
	case "doc":
		$data = func_extraedoc();
		break;
}


echo "<pre><xmp>";
echo $data;
echo "</xmp></pre>";



?>


