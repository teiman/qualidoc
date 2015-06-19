function ver_grupos(datos){
    // construye la parte de html para poder agnadir, quitar usuarios grupos, editar grupos o eliminarlos
    $("#gruposexistentes").html("");
    if (datos.error){
        
        return
    }
    
    datos.lineas.forEach(function(item){
                         
         // contenedor
         var $li = $(document.createElement("li")); 
          // imagen borrar
         var $borrar = $(document.createElement("input"));
         $borrar.attr("type","image");
         $borrar.attr("src", "img/etiqueta-x.png");
         $borrar.attr("name", "borrargrupo");
         $borrar.attr("id", "borraridgrupo"+item.idgrupo)
         $borrar.click(function(){
            togglea_borrado(item.idgrupo)   
         });
         $borrar.addClass("js-finedicion oculto");
         $li.append($borrar);
         // input
         var $linea  = $(document.createElement("input"));
         
         $linea.attr("type","checkbox");
         $linea.addClass("js-edicion");
         if (item.perteneceagrupo){
             $linea.attr("checked", item.perteneceagrupo);
         }
         $linea.attr("value", item.idgrupo);
         $linea.attr("name", "grupo");
         $linea.attr("id", "idgrupo"+item.idgrupo)
         $linea.click(function(){
            togglea_grupo(item.idgrupo)   
         });
         $li.append($linea);
         // caja de texto editar
         var $editar = $(document.createElement("input"));
         $editar.attr("type","text");
         $editar.attr("value", item.nombregrupo);
         $editar.attr("name", "ediciongrupo");
         $editar.attr("id", "idediciongrupo"+item.idgrupo);
         $editar.keyup(function(){
            actualizar_grupo(item.idgrupo)   
         });
         $editar.addClass("js-finedicion oculto");
         $li.append($editar);
         // borrado
         var $bborrar = $(document.createElement("input"));
         $bborrar.attr("type","button");
         $bborrar.attr("value", "Eliminar");
         $bborrar.attr("name", "bborrargrupo");
         $bborrar.attr("id", "bborraridgrupo"+item.idgrupo)
         $bborrar.click(function(){
            borrar_grupo(item.idgrupo)   
         });
         $bborrar.addClass("js-bborrar oculto");
         $li.append($bborrar);
         
         // texto 
         var $sp = $(document.createElement("span"));
         $sp.html(item.nombregrupo);
         $sp.attr("id","idetiquetagrupo"+item.idgrupo);
         $sp.addClass("js-edicion");
         $li.append($sp);
         $li.attr("id", "li"+item.idgrupo);
         
         $("#gruposexistentes").append($li);
         
         //span
         
     });
}

function filtro_buscar_grupo(){
  var buscar = $("#buscargrupo").val();
  if (buscar == "Buscar"){
      buscar = "";
  }  
  return buscar;
}