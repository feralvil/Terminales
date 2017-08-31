// Funciones JQuery para coberturas_detmuni.php:
$(function(){
    // Botones de formularios:
    $('a#newtab').click(function(){
        $('form#formmunidetalle').submit();
    });
    $('a#export').click(function(){
        $('form#formmuniexport').submit();
    });

    // Gestión de divs al cargar
    $('div#cobtbs').show();
    $('div#cobflotas').hide();
    $('a#linktbs').addClass('activo');
    $('a#linkflotas').removeClass('activo');
    // Enlaces de pestañas:
    // Tab de TBS
    $('a#linktbs').click(function(){
        $('div#cobflotas').hide();
        $('div#cobtbs').show();
        $('ul li a').removeClass('activo');
        $(this).addClass('activo');
    });
    // Tab de Flotas
    $('a#linkflotas').click(function(){
        $('div#cobtbs').hide();
        $('div#cobflotas').show();
        $('ul li a').removeClass('activo');
        $(this).addClass('activo');
    });
});
