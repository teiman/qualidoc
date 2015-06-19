//creación de objeto
//creamos un  los siguientes metodos para cada tipo de  cuadro de diálogo
//.si_no  .aceptar_cancelar  .ok  .si_no_cancelar

var cuadro_dialogo = {
    version: 1
};

cuadro_dialogo.si_no = function (titulo, mensaje, si, no) {
    $("#dialog-confirm").attr("title", titulo);
    $("#dialog-confirm .js-mensaje").html(mensaje);
    $("#dialog-confirm").hide()
                        .removeClass("oculto")
                        .show()
                        .dialog({
        resizable: false,
        height:    240,
        position:['middle',200],
        modal:     true,
        buttons:   {
            "Sí": function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                if(typeof si != "undefined") si();
            },
            "No": function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                if(typeof no != "undefined") no();
            }
        }
    });
};

cuadro_dialogo.ok = function (titulo, mensaje) {
    $("#dialog-confirm").attr("title", titulo);
    $("#dialog-confirm .js-mensaje").html(mensaje);
    $("#dialog-confirm").hide()
            .removeClass("oculto")
            .show()
            .dialog({
        resizable: false,
        height:    240,
        position:['middle',200],
        modal:     true,
        buttons:   {
            "Ok": function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
            }
        }
    });
};

cuadro_dialogo.aceptar_cancelar = function (titulo, mensaje, aceptar, cancelar) {
    $("#dialog-confirm").attr("title", titulo);
    $("#dialog-confirm .js-mensaje").html(mensaje);
    $("#dialog-confirm")
        .hide()
        .removeClass("oculto")
        .show()
        .dialog({
        resizable: false,
        height:    240,
        position:['middle',200],
        modal:     true,
        buttons:   {
            "Aceptar":  function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                if(typeof aceptar != "undefined") aceptar();
            },
            "Cancelar": function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                if(typeof cancelar != "undefined") cancelar();
            }
        }
    });
};


cuadro_dialogo.si_no_cancelar = function (titulo, mensaje, si, no, cancelar) {
    $("#dialog-confirm").attr("title", titulo);
    $("#dialog-confirm .js-mensaje").html(mensaje);
    $("#dialog-confirm")
        .hide()
        .removeClass("oculto")
        .show()
        .dialog({
        resizable: false,
        height:    240,
        position:['middle',200],
        modal:     true,
        buttons:   {
            "Sí":       function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                if(typeof si != "undefined") si();
            },
            "No":       function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                if(typeof no != "undefined") no();
            },
            "Cancelar": function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                if(typeof cancelar != "undefined") cancelar();
            }
        }
    });
};

cuadro_dialogo.popup_formulacion = function (opciones) {

    var valordef = {
        titulo:               "Título ventana",
        id_formula:           "0",
        puedo_borrar_formula: true

    };
    //sobreescribo los valores enviados desde el programa
    //a los valores por defecto
    $.extend(valordef, opciones);

    $("#popupformulacion").attr("title", valordef.titulo);

    if (valordef.puedo_borrar_formula == false) {
        $("#borrar_formula").attr("disabled", "disabled")
    }

    $("#popupformulacion").hide()
        .removeClass("oculto")
        .show()
        .dialog({
        resizable: false,
        height:    500,
        position:['middle',200],
        width:     700,
        modal:     true
    });
};


cuadro_dialogo.formulario_popup = function (titulo, mensaje, idorigen, ok) {
    $("#formpopup").attr("title", titulo)
        .hide().removeClass("oculto")
        .show()
        .dialog({
        resizable: false,
        height:    500,
            position:['middle',200],
        width:     700,
        modal:     true
    });

};

cuadro_dialogo.popup_grupos = function (opciones) {

    var valordef = {
        titulo:               "Título ventana",
        id_formula:           "0",
        puedo_borrar_formula: true
    };

    //sobreescribo los valores enviados desde el programa
    //a los valores por defecto
    $.extend(valordef, opciones);

    $("#popupgrupos").attr("title", valordef.titulo);
    //if (valordef.puedo_borrar_formula==false){
    //    $("#borrar_formula").attr("disabled","disabled")
    //}
    $("#popupgrupos").hide().removeClass("oculto")
        .show()
        .dialog({
        resizable: false,
        height:    500,
            position:['middle',200],
        width:     350,
        modal:     true
    });
};


cuadro_dialogo.popup_grp_usr = function (opciones, ok) {

    var valordef = {
        titulo: "Título ventana"
    };

    //sobreescribo los valores enviados desde el programa
    //a los valores por defecto
    $.extend(valordef, opciones);

    $("#popuppermisos").attr("title", valordef.titulo);

    $("#popuppermisos").hide().removeClass("oculto")
        .show()
        .dialog({
        resizable: false,
        height:    500,
            position:['middle',200],
        width:     350,
        modal:     true,
        buttons:   {
            "Ok": function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                ok()
            }
        }
    });
};


cuadro_dialogo.popup_categorias = function (opciones, aceptar, cancelar) {

    var valordef = {
        titulo: "Título ventana"
    };

    //sobreescribo los valores enviados desde el programa
    //a los valores por defecto
    $.extend(valordef, opciones);

    $("#popupcategorias").attr("title", valordef.titulo);

    $("#popupcategorias").hide().removeClass("oculto")
        .show()
        .dialog({
        resizable: false,
        height:    500,
        width:     850,
            position:['middle',200],
        modal:     true,
        buttons:   {
            "Aceptar":  function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                if(typeof aceptar != "undefined") aceptar();
            },
            "Cancelar": function () {
                $(this).dialog("close");
                $("#dialog-confirm").hide().addClass("oculto");
                if(typeof cancelar != "undefined") cancelar();
            }
        }
    });
};