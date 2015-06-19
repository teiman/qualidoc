



var fecha = new Date();
var diames = fecha.getDate();
var diasemana=fecha.getDay();
var mes=fecha.getMonth() +1 ;
var ano=fecha.getFullYear();

var textosemana = new Array (7);
textosemana[0]="Domingo";
textosemana[1]="Lunes";
textosemana[2]="Martes";
textosemana[3]="Miércoles";
textosemana[4]="Jueves";
textosemana[5]="Viernes";
textosemana[6]="Sábado";

var textomes = new Array (12);
textomes[1]="Enero";
textomes[2]="Febrero";
textomes[3]="Marzo";
textomes[4]="Abril";
textomes[5]="Mayo";
textomes[6]="Junio";
textomes[7]="Julio";
textomes[7]="Agosto";
textomes[9]="Septiembre";
textomes[10]="Octubre";
textomes[11]="Noviembre";
textomes[12]="Diciembre";

$(function() {

    var textodia = textosemana[diasemana] + ", " + diames + " de " + textomes[mes] + " de " + ano;

    $("#cajafecha").html(textodia);

    if($(".focuseme").length)
        $(".focuseme").focus();

    if(typeof window.onCarga == 'function') {
        onCarga();
    }

    function AjustarTamagnosDinamicos(){
        var w = $(window).width();

        var neww = w - 200 - 20;//modelo estandar
        neww = w - 200-10 + 190;//modelo extraño de ie

        $("#cajacontenido").css("width",neww+"px");

        var neww2 = w - 200 -10;

        $("#tablactitulo").css("width",neww2+"px" );

    }

    if ($.browser.msie){
        AjustarTamagnosDinamicos();
        $(window).resize( AjustarTamagnosDinamicos );
    }

});




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
