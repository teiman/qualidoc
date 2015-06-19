<?php

include("tool.php");


$cat = CleanID($_REQUEST["cat"]);

$cat_s = sql($cat);


$page->setAttribute( 'listado', 'src', 'conexioncerrada.html' );


$page->Volcar();



?>