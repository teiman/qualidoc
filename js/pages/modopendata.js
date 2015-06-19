


function comprueba_campos() {
    var errores = 0;

    $(".js-textoerror").hide().removeClass("oculto");

    if (!$("#categoriaseleccionada").val()){
        $("#error_validacion_carpeta").show();
        errores++;
    }

    if( !$("#titulo").val() ){
        $("#error_validacion_titulo").show();
        errores++;
    }


    if( !$("#url").val() ){
        $("#error_validacion_canal").show();
        errores++;
    }

    if(errores==0){
        var texto = JSON.stringify(extraer_array_compartido()) ;
        $("#resultado_compartido").val(texto);
        return true;
    }else{
        return false;
    }
}



