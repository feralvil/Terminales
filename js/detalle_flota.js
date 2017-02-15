// Funciones JQuery para detalle_flota.php:

$(function(){
    // Escondemos los div de contactos y terminales:
    $('div#contactos').hide();
    $('div#term').hide();
    // Enlaces de pestañas:
    // Tab de Inicio
    $('a#linkhome').click(function(){
        $('div#contactos').hide();
        $('div#term').hide();
        $('div#inicio').show();
        $('ul li a').removeClass('activo');
        $(this).addClass('activo');
    });
    // Tab de Contactos
    $('a#linkcont').click(function(){
        $('div#inicio').hide();
        $('div#term').hide();
        $('div#contactos').show();
        $('ul li a').removeClass('activo');
        $(this).addClass('activo');
    });
    // Tab de Inicio
    $('a#linkterm').click(function(){
        $('div#inicio').hide();
        $('div#contactos').hide();
        $('div#term').show();
        $('ul li a').removeClass('activo');
        $(this).addClass('activo');
    });
});
