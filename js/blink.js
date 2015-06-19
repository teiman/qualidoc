

var Blink = Blink || [];


Blink.elementos = function(id){

    $(id).addClass("js-remarcado-1");

    setTimeout(function(){
        $(id).removeClass("js-remarcado-1").addClass("js-remarcado-2");
    },300);

    setTimeout(function(){
        $(id).removeClass("js-remarcado-2").addClass("js-remarcado-3");
    },400);

    setTimeout(function(){
        $(id).removeClass("js-remarcado-3");
    },500);

};