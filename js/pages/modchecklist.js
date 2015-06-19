

/**
 *
 * @param id_pregunta int
 * @param tipo int
 * @returns {boolean} false
 */
function editar_pregunta (id_pregunta){

    var tipo = $("#btn_editar_"+id_pregunta).attr("data-tipo")*1;

    //console.log("--*0*-- editar_pregunta:id_pregunta:"+id_pregunta+",tipo:"+tipo);

    if ($("#linea_valores"+id_pregunta ).hasClass("oculto")){

        $.ajax({
            type: 'POST',
            url: "modchecklist.php",
            data: {
                modo:"datos_pregunta",
                id_pregunta: id_pregunta,
                tipo: tipo
            },
            dataType:"json",
            success: function(data){
                if (data.ok){
                    $("#div_linea_valores"+id_pregunta).html(data.html);
                    if ($("#linea_valores"+id_pregunta ).hasClass("oculto")){
                        $("#linea_valores"+id_pregunta ).removeClass("oculto");
                    }
                    $("#textpregunta"+id_pregunta).removeClass("oculto");
                    $("#spanpregunta"+id_pregunta).addClass("oculto");
                    $("#textposicionpregunta"+id_pregunta).removeClass("oculto");
                    $("#spanposicionpregunta"+id_pregunta).addClass("oculto");

                    //console.log("--*1*-- editar_pregunta");
                    if(tipo==Listas.TIPO_EXCLUSIVO){
                        $(".prg-fila-"+id_pregunta).each(function(){
                            if($(this).hasClass("js-no-exclusiva")){
                                $(this).hide();
                            }
                        })
                    } else {
                        $(".prg-fila-"+id_pregunta).show();
                    }

                }

            }
        });
    }else{
        //console.log("--*2*-- editar_pregunta");

        $("#linea_valores"+id_pregunta ).addClass("oculto");
        $("#textpregunta"+id_pregunta).addClass("oculto");
        $("#spanpregunta"+id_pregunta).removeClass("oculto");
        $("#textposicionpregunta"+id_pregunta).addClass("oculto");
        $("#spanposicionpregunta"+id_pregunta).removeClass("oculto");
    }

    return false;
}

function editar_grupo (id_grupo){

    var es_oculto = ($("#textgrupo"+id_grupo ).hasClass("oculto"));

    if (es_oculto){
        $("#spangrupo"+id_grupo ).addClass("oculto");
        $("#spanposiciongrupo"+id_grupo).addClass("oculto");

        $("#textgrupo"+id_grupo).removeClass("oculto");
        $("#textposiciongrupo"+id_grupo).removeClass("oculto");
    }else{
        $("#spangrupo"+id_grupo ).removeClass("oculto");
        $("#spanposiciongrupo"+id_grupo).removeClass("oculto");

        $("#textgrupo"+id_grupo).addClass("oculto");
        $("#textposiciongrupo"+id_grupo).addClass("oculto");
    }

    var posicion = $("#textposiciongrupo"+id_grupo).val();
    var texto = $("#textgrupo"+id_grupo).val();

    $("#spangrupo"+id_grupo).html( html(texto));
    $("#spanposiciongrupo"+id_grupo).html( html(posicion));

    console.log("posicion:"+posicion+",texto:"+texto);

    return false;
}



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

    if(errores==0){
        //devolvemos el array con los compartidos
        var texto = JSON.stringify(extraer_array_compartido()) ;

        console.log("modchecklist.js:resultado_compartido:"+texto);
        $("#resultado_compartido").val(texto);
        return true;
    }else{
        return false;
    }
}


function cambio_tipo_pregunta(id_pregunta){
    //console.log($("#tipopregunta"+id_pregunta).val());
    var tipo = $("#tipopregunta"+id_pregunta).val()*1;

    switch(tipo){
        case 1:
            $("#opcion"+id_pregunta).addClass("oculto");
            break;
        case 2:
        case 3:
            $("#opcion"+id_pregunta).removeClass("oculto");
            break;
    }

    $("#btn_editar_"+id_pregunta).attr("data-tipo",tipo);

}
