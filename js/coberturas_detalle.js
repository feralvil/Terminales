// Funciones JQuery para coberturas_detalle.php:
$(function(){
    // Variables de páginas
    var pagina = $('input#pagina').val();
    var npag = $('input#npag').val();

    // Botones de formularios:
    $('a#newtab').click(function(){
        $('form#formcobdetalle').submit();
    });
    $('a#export').click(function(){
        $('form#formcobexport').submit();
    });
    $('a#reset').click(function(){
        $('form#formcobdetalle').attr('target', '_self');
        $('form#formcobdetalle').submit();
    });

    // Gestión de divs al cargar
    $('div#cobmuni').show();
    $('div#cobflotas').hide();
    $('a#linkmuni').addClass('activo');
    $('a#linkflotas').removeClass('activo');
    // Enlaces de pestañas:
    // Tab de Municipios
    $('a#linkmuni').click(function(){
        $('div#cobflotas').hide();
        $('div#cobmuni').show();
        $('ul li a').removeClass('activo');
        $(this).addClass('activo');
    });
    // Tab de Flotas
    $('a#linkflotas').click(function(){
        $('div#cobmuni').hide();
        $('div#cobflotas').show();
        $('ul li a').removeClass('activo');
        $(this).addClass('activo');
    });
    // Botones de paginación
    $('a#primpag').click(function(){
        $('input#pagina').val(1);
        $('form#formmuni').submit();
    });
    $('a#prevpag').click(function(){
        $('input#pagina').val(pagina - 1);
        $('form#formmuni').submit();
    });
    $('a#sigpag').click(function(){
        $('input#pagina').val(+pagina + 1);
        $('form#formmuni').submit();
    });
    $('a#ultpag').click(function(){
        $('input#pagina').val(npag);
        $('form#formmuni').submit();
    });
    // Select de formularios:
    $('form#formmuni select').change(function(){
        $('input#pagina').val(1);
        $('form#formmuni').submit();
    });

});
