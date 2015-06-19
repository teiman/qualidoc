<?php

function existe_usuario($usuario,$password){
    // en esta funcion vamos a leer si existe usuario así como almacenaremos todos su atributos
   $valor = false;
   $sql = "SELECT id_usuario, nombreusuario, administrador, password FROM usuarios WHERE nombreusuario = '".sql($usuario)."' and eliminado = 0";
   $res = query($sql);
   $row= Row($res);
   if ($row) {
        $hash  = md5( $row["id_usuario"] . "_TARARI_". $password );
        if ($hash == $row["password"]){
           // usuario ok, nos guardamos en la sesion
           // los datos del mismo 
           login_user($row);
           $valor = true;
         
        }   
   }       
   return $valor;
}



function login_user($row){
    limpiarSesion();
    setSesionDato("eslogueado","1"); 
    setSesionDato("esadmin",$row["administrador"]);
    setSesionDato("id_usuario", $row["id_usuario"]);
    
    setSesionDato("nombreusuario", $row["nombreusuario"]);
    setSesionDato("listarmisdocumentossupervisor","NO");
   }