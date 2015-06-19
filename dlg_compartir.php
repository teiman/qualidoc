<?php
//require ("kint/Kint.class.php");
include("tool.php");
include(__ROOT__ . "/inc/busquedas.inc.php");

$modo = $_POST["modo"];

switch ($modo){
    case "cargar_grupos":
       
       $arrdatos = array();
       $arrcompartidos=array();
       $actualcompartido = $_POST["actual_compartido"];
        // filtro busqueda
       $like= trim($_POST["filtro"]);
       
       // añadirmos todos los grupos al combo
       if ($like <> ""){
           $like = " AND nombregrupo LIKE '%".sql($like)."%' ";
       }
       $sql = "SELECT id_grupo, nombregrupo FROM grupos WHERE eliminado = 0 ".$like." ORDER BY nombregrupo";
       
       $res = query($sql);
       $i=0;
       while ($row = Row($res)) {
           ++$i;
           if (($i % 2 )== 0){
               $pi = "odd";
           }
           else { 
               $pi = "even";
           }
           $valor = "";
           foreach($actualcompartido as $valores){
                if ($valores["id_elemento"]== $row["id_grupo"] && $valores["tipo_elemento"]==1){
                    $valor = "checked";
                    $arrcompartidos[] = array("id_elemento" => $row["id_grupo"], "tipo_elemento"=> "1" );
                    
                }    
           }
           //tipo usuario = 1 (Carpeta)
           $arrdatos[] = array("id_elemento" => $row["id_grupo"], "nombre" => $row["nombregrupo"], "compartido" => $valor, "tipousuario"=> "1" );
       }
       
       // añadirmos todos los usuarios al combo
       
       if ($like <> ""){
           $like = " AND nombregrupo LIKE '%".sql($like)."%' ";
       }
       $sql = "SELECT id_usuario, nombreusuario  FROM usuarios WHERE eliminado = 0 ".$like." ORDER BY nombreusuario";
       $res = query($sql);
       $i=0;
       while ($row = Row($res)) {
           ++$i;
           if (($i % 2 )== 0){
               $pi = "odd";
           }
           else { 
               $pi = "even";
           }
          $valor = "";
           foreach($actualcompartido as $valores){
                if ($valores["id_elemento"]== $row["id_usuario"] && $valores["tipo_elemento"]==0){
                   $valor = "checked";
                   $arrcompartidos[] = array("id_elemento" => $row["id_usuario"], "tipo_elemento"=> "0" );
                  
                }    
           }
            
           //tipo usuario = 0 (usuario)
           $arrdatos[] = array("id_elemento" => $row["id_usuario"], "nombre" => $row["nombreusuario"], "compartido" => $valor, "tipousuario" => "0" );
       }
       
       $grupos = array("lineas"=>$arrdatos, "compartidos"=>$arrcompartidos, "ok"=> true );
        echo json_encode($grupos);
        exit();
       break;      
       
    
       
    case "crear_html_compartido":
        
        //actualizamos la lista de compartidos dentro 
        $compartidos = $_POST["compartidos"];
        $id_elemento = $_POST["id_elemento"];
        $tipo_documento = $_POST["tipo"];
        $grppt = "<!-- dlg_compartir.php:crear_html_compartido -->";

        forEach($compartidos as $valores){
             if ($valores["tipo_elemento"] == 0){
                $id = $valores["id_elemento"];
                $tipo = 0;
                $tipoclass="js-usuario";
                $sql = parametros("SELECT nombreusuario FROM usuarios WHERE id_usuario=%d",$id);
                $row = queryrow($sql);
                $nombre= $row["nombreusuario"];
            }else{
                $tipo = 1;
                $tipoclass="js-grupo";
                $id = $valores["id_elemento"];
                $sql = parametros("SELECT nombregrupo FROM grupos WHERE id_grupo=%d",$id);
                $row = queryrow($sql);
                $nombre= $row["nombregrupo"];
            }

            $grppt = $grppt. "<div class='etiqueta-grupo js-etiq ".$tipoclass."' id=etiqueta".$id."tipo".$tipo.">";
            $grppt = $grppt. "<input type='hidden' name= 'id_usrgrp' value=".$id." />";
            $grppt = $grppt. "<input type='hidden' name= 'tipo_usrgrp' value=".$tipo." />";
            $grppt = $grppt. "<input type='image' src='img/etiqueta-x.png' title='Eliminar' onclick='return no_compartido(".$id.",".$tipo.",\"".html($nombre)."\")'/>";
            $grppt = $grppt. "<span>".html($nombre)."</span>";
            $grppt = $grppt. "</div><!-- /dlg_compartir.php:crear_html_compartido -->";
                
       }   
       
        echo json_encode($grppt);
         exit();
        break; 
   
       
    case "crear_html_compartido_bd":
        $compartidos = $_POST["compartidos"];
        $tipo_documento = $_POST["tipo"];
        if ( $tipo_documento == 0){
            $id_documento = $_POST["id_elemento"];
            $id_categoria= 0;
        }else{
            $id_documento = 0;
            $id_categoria= $_POST["id_elemento"];
        }    
        //actualizamos la lista de compartidos tambien en la base de datos
        
        $sql= "DELETE FROM permisosdocumentos WHERE id_documento_compartido =%d AND id_categoria_compartida =%d";
        $sql = parametros($sql, $id_documento, $id_categoria);
        queryrow($sql);
        
        forEach($compartidos as $valores){
             if ($valores["tipo_elemento"] == 0){
                $idusr = $valores["id_elemento"];
                $idgrp = 0;
                
                         
            }else{
                $idusr =0;
                $idgrp =$valores["id_elemento"];
       
            }
            $sql = "INSERT INTO permisosdocumentos (id_documento_compartido, id_categoria_compartida, id_usuario_permitido, id_grupo_permitido) VALUES (%d,%d,%d,%d)";
            $sql = parametros($sql,$id_documento, $id_categoria, $idusr, $idgrp);
            queryrow($sql);    
        }    
        // creamos el html
        $sql =  parametros("SELECT id_permiso, id_usuario_permitido, id_grupo_permitido FROM permisosdocumentos WHERE id_documento_compartido = %d AND id_categoria_compartida = 0",$id_documento); 
        $res = query($sql);
        $grppt = "";
        //grppt = "<input type='image' src='img/verde/compartir-medio.png' title='Compartido con...' class='enlinea' onclick='return popup($id_documento,0)'/>";
        //$grppt = $grppt. "<div id=compartidocon$id_documento>";
        while ($rowgroup = Row($res)) {
            if ($rowgroup["id_usuario_permitido"]) {
                $tipo = "0";
                $tipoclass="js-usuario";
                $id = $rowgroup["id_usuario_permitido"];
                $nombre = nombre_usuario($id);
            }else{
                $tipo ="1";
                $tipoclass="js-grupo";
                $id= $rowgroup["id_grupo_permitido"];
                $nombre = nombre_grupo($id); 
            }
                    
            $grppt = $grppt. "<div class='etiqueta-grupo js-etiq$id_documento " .$tipoclass."' id=etiqueta".$rowgroup["id_permiso"].">";
            $grppt = $grppt. "<input type='hidden' name= 'id_usrgrp' value=".$id." />";
            $grppt = $grppt. "<input type='hidden' name= 'tipo_usrgrp' value=".$tipo." />";
            $grppt = $grppt. "<input type='image' src='img/etiqueta-x.png' title='Eliminar' onclick='return no_compartido(".$rowgroup["id_permiso"].",\"".$nombre."\")'/>";
            $grppt = $grppt. "<span>" .$nombre. "</span>";
            $grppt = $grppt. "</div>";
        }   
       //$grppt = $grppt. "</div>";        
       
       echo json_encode($grppt);
       exit();
       break; 
       
       
}
