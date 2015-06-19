

function popup(id_elemento, tipo){
    // esta funcion llama al popup y despues me retorna los
    // datos de los grupos y usuarios con los que se ha compartido
    var actual_compartido=[];
    actual_compartido = extraer_array_compartido();

    console.dir(actual_compartido);

    dialog.modificar_permisos(id_elemento, tipo, actual_compartido,  function(devcompartidos){
        $.ajax({
            type: 'POST',
            url: "dlg_compartir.php",
            data: {
                modo:"crear_html_compartido",
                id_elemento:id_elemento,
                tipo:tipo,
                compartidos:devcompartidos
            },
            dataType:"json",
            success: function(data){
                // actualizar html
                $("#compartidocon").html(data);
            }

        });

    });
    return false
}


function extraer_array_compartido() {
    var compartido=[];
    var vistos=[];//antibug squad

    var $caja = $("#compartidocon");
    $(".js-etiq",$caja).each(function(){
        var id = $("input[name=id_usrgrp]",this).val();
        var tipo = $("input[name=tipo_usrgrp]",this).val();

        if(!vistos[id+"_"+tipo])
            compartido.push ({id_elemento:id, tipo_elemento: tipo});

        vistos[id+"_"+tipo] = true;
    });

    return compartido;
}


function no_compartido(id_elemento,  nombre){

    cuadro_dialogo.si_no("No compartir..", "El documento ya no será compartido con "+nombre+". ¿Desea continuar?",
        function(){
            $("#etiqueta"+id_elemento).remove();
            return true;
        },
        function(){
            // no hay codigo
            return false;
        });

    return false;
}



function onCarga(){

    var config = {
        toolbar:
            [
                ['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink'],
                ['UIColor']
            ]
    };
    var config2 = {
        toolbar:
            [
                ['Bold','Italic','Underline','Strike'],
                ['NumberedList','BulletedList','-','Outdent','Indent'],
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                ['Link','Unlink','Image','MyButton','Preview'],
                '/',
                ['Print', 'SpellChecker', 'Scayt'],
                ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                '/',
                ['Format','Font','FontSize'],
                ['TextColor','BGColor']
            ]
    };


    // Initialize the editor.
    // Callback function can be passed and executed after full instance creation.
    config.autoGrow_minHeight = 100;
    config.autoGrow_maxHeight = 100;
    config.height  = 100;
    $('.jquery_ckeditor').ckeditor(config);

    config2.autoGrow_minHeight = 300;
    config2.height = 300;
    //var editor = $('.jquery_ckeditor2').ckeditor(config2);


    if($("#jquery_ckeditor2").length)
        try {
            var editor = CKEDITOR.replace( 'jquery_ckeditor2',config2);

            // Listen for the "pluginsLoaded" event, so we are sure that the
            // "dialog" plugin has been loaded and we are able to do our
            // customizations.
            editor.on( 'pluginsLoaded', function( ev ){
                // If our custom dialog has not been registered, do that now.
                if ( !CKEDITOR.dialog.exists( 'myDialog' ) )
                {
                    // We need to do the following trick to find out the dialog
                    // definition file URL path. In the real world, you would simply
                    // point to an absolute path directly, like "/mydir/mydialog.js".

                    var href = document.location.href.split( '/' );
                    href.pop();
                    href.push( 'js/ckupload.js' );
                    href = href.join( '/' );

                    // Finally, register the dialog.
                    CKEDITOR.dialog.add( 'myDialog', href );
                }

                // Register the command used to open the dialog.
                editor.addCommand( 'myDialogCmd', new CKEDITOR.dialogCommand( 'myDialog' ) );

                // Add the a custom toolbar buttons, which fires the above
                // command..
                editor.ui.addButton( 'MyButton',
                    {
                        label : 'Subir imagen',
                        command : 'myDialogCmd'
                    } );
            });

        }catch(e){
            console.log(e);
        }
};
