




var Listas = Listas || [];

Listas.id_listado = 0;

Listas.TIPO_TEXTO = 1;
Listas.TIPO_EXCLUSIVO = 2;
Listas.TIPO_MULTIPLE = 3;

Listas.necesita_autoload = true;

function html(text){
    return $("<div/>").text(text).html();
}


/**
 *
 * @param id_grupo int
 */
Listas.editar_grupo_proxy =function(id_grupo){
    editar_grupo(id_grupo);

    var posicion = $("#textposiciongrupo"+id_grupo).val();
    var texto = $("#textgrupo"+id_grupo).val();


    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: {modo:"guardarcambiosgrupo","id_grupo":id_grupo,"texto":texto,"posicion":posicion},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            //console.log("tipodetectado:"+tipo+",cuantos:"+cuantos+",listE:"+Listas.TIPO_EXCLUSIVO);
        }
    });//ajax

};


/**
 *
 * @param id_pregunta int
 */
Listas.oculta_no_exclusivas = function(id_pregunta){

    //se ven las que haya
    return;
    /*
    var n = 0;
    $(".prg-fila-"+id_pregunta).each(function(){
        if(n>=2){
            $(this).hide();
        } else {
            $(this).show();
        }
        n++;
    })

    if(n>=1){
        console.log("Listas.oculta_no_exclusivas: ocultando +");
        $("#div_linea_valores"+id_pregunta+" .js-siempreultima").hide().removeClass("oculto");
    }*/
};


//Listas.borrarValor('<patTemplate:var name="id_pregunta"  modifier="html8" />',<patTemplate:var name="id_valor_posible"  modifier="html8" />)
//<tr class='prg-fila-<patTemplate:var name="id_pregunta"  modifier="html8" /> valorposible-valor<patTemplate:var name="id_valor_posible" /> {CSS_EXCLUSIVA}'>

Listas.borrarValor = function(id_pregunta,id_valor_posible){
    $(".valorposible-valor"+id_valor_posible).remove();
};


/**
 *
 *   @param id_pregunta int
 *   @param id_grupo int
 */
Listas.onchange_tipoPregunta = function(id_pregunta,id_grupo){
    if(typeof cambio_tipo_pregunta !="undefined") cambio_tipo_pregunta(id_pregunta);

    Listas.genInterfacePregunta(id_pregunta,id_grupo);
};



Listas.getTipoPregunta = function(id_pregunta){
    var tipo = $("#btn_editar_prg"+id_pregunta).attr("data-tipo");

    return tipo;
};


//Listas.nuevoValor(28,60)
Listas.nuevoValor = function(id_pregunta,id_grupo){
    var valortext = "Nueva pregunta";


    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: {modo:"nuevovalor","id_pregunta":id_pregunta,"valor":valortext},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            var $html = $(data["html"]);

            $html.insertBefore("#div_linea_valores"+id_pregunta+" .js-siempreultima");
            //$("#div_linea_valores"+id_pregunta+" .js-siempreultima").insertBefore($html);

            /*
            var tipo = $("#tipopregunta"+id_pregunta).val()*1;
            var cuantos = $(".prg-fila-"+id_pregunta).length;

            switch(tipo){
                case Listas.TIPO_TEXTO:
                    $("#div_linea_valores"+id_pregunta+" .js-siempreultima").hide();
                    break;
                case Listas.TIPO_EXCLUSIVO:

                    console.log("es exclusiva, aplicamos reglas de restriccion");
                    if(cuantos>=2){
                        $("#div_linea_valores"+id_pregunta+" .js-siempreultima").hide();
                    }else{
                        $("#div_linea_valores"+id_pregunta+" .js-siempreultima").show();
                    }

                    Listas.oculta_no_exclusivas(id_pregunta);

                    break;
                case Listas.TIPO_MULTIPLE:
                    $("#div_linea_valores"+id_pregunta+" .js-siempreultima").show();
                    break;

            }
            */

            //console.log("tipodetectado:"+tipo+",cuantos:"+cuantos+",listE:"+Listas.TIPO_EXCLUSIVO);
        }
    });//ajax
};


/**
 *
 * @param id_pregunta
 * @param id_grupo
 */
Listas.genInterfacePregunta = function(id_pregunta,id_grupo){
    $.ajax({
        type: "POST",
        url: "fragmentos.php",
        dataType: "json",
        data: {modo:"geninterfacepregunta","id_pregunta":id_pregunta},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            //console.log("html:"+data["html"]);
            $(".opcion"+id_grupo).html("");
            var nuevo = $(data["html"]);
            $(".opcion"+id_grupo).append( nuevo );
        }
    });//ajax
};



//Listas.guardarCambios(<patTemplate:var name="id_pregunta" />)

Listas.guardarCambios = function(id_pregunta,id_grupo){
    var textpregunta = $("#textpregunta"+id_pregunta).val();
    var textposicionpregunta = $("#textposicionpregunta"+id_pregunta).val();
    var tipopregunta = $("#tipopregunta"+id_pregunta).val();

     $("#btn_editar_"+id_pregunta).attr("data-tipo",tipopregunta);



    var datos = [];
    var $caja = $("#opcion"+id_pregunta);
    var $preguntas = $(".prg-fila-"+id_pregunta,$caja);

    //console.log("cajan count:"+$caja.length);
    //console.log("preguntas leng:"+$preguntas.length);

    //var num= 0;
    $preguntas.each(function(){
        var texto = $("input[name=texto]",this).val();
        var valor = $("input[name=valor]",this).val();
        var idpos = $("input[name=texto]",this).attr("data-idpos");

        //console.log("encontramos:"+texto+",valor:"+valor);
        datos.push( {texto:texto,valor:valor,idpos:idpos} );
        //num++;
    })

    //console.log("encontrados:num:"+num+",elementos");
    //console.dir(datos);

    var jsondatos = JSON.stringify(datos);


    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: {modo:"guardarcambiospregunta","id_pregunta":id_pregunta,"pregunta":textpregunta,"posicion":textposicionpregunta,"tipopregunta":tipopregunta,"jsondatos":jsondatos},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            $("#spanpregunta"+id_pregunta).html( html(textpregunta) );
            $("#spanposicionpregunta"+id_pregunta).html( html(textposicionpregunta) );

            if(id_grupo){
                Listas.genPreguntas("preguntaseditar",id_grupo,function(){});
            }

        }
    });//ajax
};


/**
 *
 * @param id_pregunta int
 * @param id_grupo int
 * @returns {boolean}
 */
Listas.borrarPregunta_ui = function(id_pregunta,id_grupo){
    cuadro_dialogo.si_no("Borrando pregunta", "¿Borrar esta pregunta?", function(){
        Listas.borrarPregunta(id_pregunta,id_grupo);
    }, function(){
        return;
    });

    return false;
};


/**
 *
 * @param id_pregunta
 * @param id_grupo
 * @returns {boolean}
 */
Listas.borrarPregunta = function(id_pregunta,id_grupo){
    if(!id_pregunta) return false;

    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: {modo:"eliminarpregunta","id_pregunta":id_pregunta},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            if(id_grupo){
                //$("#preguntas_"+id_grupo).html("");

                Listas.genPreguntas("preguntaseditar",id_grupo,function(){});
            }
        }
    });//ajax

    return false;
};


Listas.crearPregunta = function(id_grupo){

    var maxval = 0;
    $(".pospreguntagrp"+id_grupo).each(function(){
        var v = $(this).val();
        if (v>maxval) maxval = v;
    })

    var nombre = "Nueva pregunta";
    var posicion = maxval*1 + 10;

    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: {modo:"crearpregunta","id_listado":Listas.id_listado,"nombre":nombre,"posicion":posicion,"id_grupo":id_grupo},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            $("#preguntas_"+id_grupo).html("");

            Listas.genPreguntas("preguntaseditar",id_grupo,function(){
                Blink.elementos($(".blkpregunta"+data.id_pregunta));
            });
        }
    });//ajax

};


Listas.borrar_grupo_ui = function(id_grupo){
    cuadro_dialogo.si_no("Borrando grupo", "¿Borrar este grupo?", function(){
        Listas.borrar_grupo(id_grupo);
    }, function(){
        return;
    });
};

/**
 *
 * @param id_grupo int
 */
Listas.borrar_grupo = function(id_grupo){

    if(!id_grupo) return false;

    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: {modo:"eliminargrupo","id_grupo":id_grupo},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            $("#trgroup_"+id_grupo).remove();
            $(".trgroup_"+id_grupo).remove();
        }
    });//ajax

};

/**
 * crear el grupo con la bd
 * @param nombre string
 */
Listas.crearGrupo = function(nombre){
    if(!nombre)
        nombre = "Nombre del grupo";

    //busca el mayor
    var maxposicion = 0;
    $(".js-posicion-grupo").each(function(){

        var val = $(this).val() * 1;
        if(val>maxposicion)
            maxposicion = val;
    });

    //el siguiente sera el mayor anterior + 10
    var posicion = maxposicion*1 + 10;

    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType: "json",
        data: {modo:"creargrupo","id_listado":Listas.id_listado,"nombre":nombre,"posicion":posicion},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            Listas.genGrupo("editargrupo",data.id_grupo);
        }
    });//ajax
};


/**
 *
 * @param modo string
 * @param id_grupo int
 * @param echo function
 */
Listas.genGrupo = function(modo,id_grupo,echo){
    $.ajax({
        type: "POST",
        url: "fragmentos.php",
        dataType: "json",
        data: {modo:modo,"id_grupo":id_grupo},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            //console.log("html:"+data["html"]);

            var nuevo = $(data["html"]);
            $("#listado_grupos").append( nuevo );

            Blink.elementos("#trgroup_"+id_grupo);

            Listas.autoLoad();//no deberia haber

            if(typeof echo != "undefined") echo();
        }
    });//ajax
};


/**
 *
 * @param modo string
 * @param id_grupo int
 * @param echo function
 */
Listas.genPreguntas = function(modo,id_grupo,echo){
    $.ajax({
        type: "POST",
        url: "fragmentos.php",
        dataType: "json",
        data: {modo:modo,"id_grupo":id_grupo},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            $("#preguntas_"+id_grupo).html( data["html"]);

            if(typeof echo != "undefined") echo();

            Listas.autoLoad();
        }
    });//ajax
};


Listas.genPreguntasValores = function(modo,id_pregunta,echo){
    $.ajax({
        type: "POST",
        url: "fragmentos.php",
        dataType: "json",
        data: {modo:modo,"id_pregunta":id_pregunta},
        success: function(data){
            if(!data || !data["ok"])
                return; //hay algun problema

            $("#caja_valores_"+id_pregunta).html( data["html"]);

            if(typeof echo != "undefined") echo();


        }
    });//ajax

};


/**
 *
 */
Listas.autoLoad = function(){

    var num = 0;

    $(".js-autogen").each(function(){
        var funcion = $(this).attr("data-funcion");
        var modo = $(this).attr("data-modo");
        var param0 = $(this).attr("data-param0");

        switch(funcion){
            case "genpreguntas_valores":
                Listas.genPreguntasValores(modo,param0,function(){
                    //console.log("Se ha autogenerado "+ JSON.stringify({funcion:funcion,modo:modo,param0:param0}));
                });
                break;
            case "genpreguntas_responder":
                Listas.genPreguntas(modo,param0,function(){
                    //console.log("Se ha autogenerado "+ JSON.stringify({funcion:funcion,modo:modo,param0:param0}));
                });
                break;
            case "genpreguntas":
                Listas.genPreguntas(modo,param0,function(){
                    //console.log("Se ha autogenerado "+ JSON.stringify({funcion:funcion,modo:modo,param0:param0}));
                });
                break;
        }

        $(this).attr("data-funcion","");
        $(this).remove();

        num++;
    });

    if(num>0)
        Listas.necesita_autoload = true;

};





