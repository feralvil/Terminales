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
    // Select de flotas
    $("select#idflota").change(function(){
        $("form#selflota").submit();
    });
    // Click de exportar a Excel
    $('a#linkexcel').click(function(){
        $("form#export").attr('action', 'flotas_detexcel.php');
        $("form#export").submit();
    });
    // Click de exportar a PDF
    $('a#linkpdf').click(function(){
        $("form#export").attr('action', 'flotas_detpdf.php');
        $("form#export").submit();
    });
    // Click de ir a Organización
    $('a#linkorg').click(function(){
        $("form#detorg").submit();
    });
    // Click de Acciones:
    $('a#linkacceso').click(function(){
        $("form#modflota").attr('action', 'acceso_flota.php');
        $("form#modflota").submit();
    });
    $('a#linkgrupos').click(function(){
        $("form#modflota").attr('action', 'grupos_flota.php');
        $("form#modflota").submit();
    });
    $('a#linkperm').click(function(){
        $("form#modflota").attr('action', 'permisos_flota.php');
        $("form#modflota").submit();
    });
    $('a#linkeditar').click(function(){
        $("form#modflota").attr('action', 'editar_flota.php');
        $("form#modflota").submit();
    });
    $('a#linkimpexcel').click(function(){
        $("form#modflota").attr('action', 'excel_flota.php');
        $("form#modflota").submit();
    });
    $('a#linkcontactos').click(function(){
        $("form#modflota").attr('action', 'contactos_flota.php');
        $("form#modflota").submit();
    });
    $('a#linkakdc').click(function(){
        $("form#modflota").attr('action', 'akdc_flota.php');
        $("form#modflota").submit();
    });
    $('a#linkbase').click(function(){
        $("form#modflota").attr('action', 'base_flota.php');
        $("form#modflota").submit();
    });
    $('a#linkaut').click(function(){
        $("form#modflota").attr('action', 'aut_flota.php');
        $("form#modflota").submit();
    });
    $('a#linkdots').click(function(){
        $("form#modflota").attr('action', 'dots_flota.php');
        $("form#modflota").submit();
    });
    $('a#linktermflota').click(function(){
        $("form#formterm").submit();
    });
});
