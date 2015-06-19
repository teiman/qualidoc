<?php

include("tool.php");


$id_categoria = $_REQUEST["id_categoria"];


header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sumario_carpeta.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');


if($modo=="checklist"){

    // output the column headings
    fputcsv($output, array('Nombre documento', 'Puntos', 'Es conforme'));

    // fetch the data

    $iduser = getSesionDato("id_usuario");
    $sql = parametros("SELECT nombre_contenido,id_listado FROM contenidos  WHERE id_categoria=%d and tipo='chklst' and eliminado=0 "  ,$id_categoria);
    $res = query($sql);


    error_log("sqlm:".$sql);


    // loop over the rows, outputting them
    while ($row = Row($res)){

        $id_listado = $row["id_listado"];

        $sql = parametros("SELECT * FROM contenidos JOIN contenidos_metadatos ON contenidos.id_contenido = contenidos_metadatos.id_contenido WHERE contenidos.id_propietario=%d AND contenidos.id_listado=%d and eliminado=0 ORDER BY contenidos.id_contenido DESC LIMIT 1",$iduser,$id_listado);
        $data = queryrow($sql);

        error_log("sql:$sql");

        if($data){
            $meta = array("puntos"=>$data["puntos"],"es_conforme"=>$data["es_conforme"]);
        } else {
            $meta = array("puntos"=>0,"es_conforme"=>0);
        }


        $out = array("nombre"=>$row["nombre_contenido"], "puntos"=>$meta["puntos"], "es_conforme"=>($meta["es_conforme"]?"Si":"No"  ) );

        fputcsv($output, $out);
    }
}

if($modo="cualquiera"){
    // output the column headings
    fputcsv($output, array('Nombre documento', 'Tipo'));

    // fetch the data

    $iduser = getSesionDato("id_usuario");
    $sql = parametros("SELECT nombre_contenido,tipo FROM contenidos  WHERE id_categoria=%d and eliminado=0 "  ,$id_categoria);
    $res = query($sql);


    error_log("sqlm:".$sql);


    // loop over the rows, outputting them
    while ($row = Row($res)){
        $out = array("nombre"=>$row["nombre_contenido"], "tipo"=>$row["tipo"]  );
        fputcsv($output, $out);
    }
}


