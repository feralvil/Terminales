// Funciones JQuery para coberturas.php:
$(function(){
    // Variables de páginas
    var pagtbs = $('input#pagtbs').val();
    var npag = $('input#npag').val();
    var pagmun = $('input#pagmun').val();
    var npagmun = $('input#npagmun').val();

    // Gestión de pestañas-divs
    var divshow = 'div#cobtbs';
    var divhide = 'div#cobmuni';
    var linkshow = 'a#linkhome';
    var linkhide = 'a#linkmuni';
    var divoculta = $('input#divoculta').val();
    if (divoculta === 'cobtbs'){
        divshow = 'div#cobmuni';
        divhide = 'div#cobtbs';
        linkhide = 'a#linkhome';
        linkshow= 'a#linkmuni';
    }
    $(divshow).show();
    $(divhide).hide();
    $(linkshow).addClass('activo');
    $(linkhide).removeClass('activo');

    // Enlaces de pestañas:
    // Tab de Inicio
    $('a#linkhome').click(function(){
        $('div#cobmuni').hide();
        $('div#cobtbs').show();
        $('ul li a').removeClass('activo');
        $(this).addClass('activo');
    });
    // Tab de Municipios
    $('a#linkmuni').click(function(){
        $('div#cobtbs').hide();
        $('div#cobmuni').show();
        $('ul li a').removeClass('activo');
        $(this).addClass('activo');
    });
    $('a#resetmun').click(function(){
        document.formmunreset.submit();
    });
    // Enlaces de detalle:
    $("a[id^='dettbs']").click(function(){
        var dettbs = $(this).attr('id');
        $("input[name='idemp']").val(dettbs.substr(7,5));
        document.formcobdetalle.submit();
    });
    $("a[id^='detmun']").click(function(){
        var detmuni = $(this).attr('id');
        $("input[name='idmuni']").val(detmuni.substr(7,5));
        document.formmunidetalle.submit();
    });
    // Select de formularios:
    $('form#formcoberturas select').change(function(){
        $('input#pagtbs').val(1);
        $('input#divoculta').val('cobmuni');
        $('form#formcoberturas').submit();
    });
    $('form#formmunicipios select').change(function(){
        $('input#pagmun').val(1);
        $('input#divoculta').val('cobtbs');
        $('form#formmunicipios').submit();
    });
    // Botones de paginación
    $('a#primtbs').click(function(){
        $('input#pagtbs').val(1);
        $('input#divoculta').val('cobmuni');
        $('form#formcoberturas').submit();
    });
    $('a#prevtbs').click(function(){
        $('input#pagtbs').val(pagtbs - 1);
        $('input#divoculta').val('cobmuni');
        $('form#formcoberturas').submit();
    });
    $('a#sigtbs').click(function(){
        $('input#pagtbs').val(+pagtbs + 1);
        $('input#divoculta').val('cobmuni');
        $('form#formcoberturas').submit();
    });
    $('a#ulttbs').click(function(){
        $('input#pagtbs').val(npag);
        $('input#divoculta').val('cobmuni');
        $('form#formcoberturas').submit();
    });
    $('a#primmun').click(function(){
        $('input#pagmun').val(1);
        $('input#divoculta').val('cobtbs');
        $('form#formmunicipios').submit();
    });
    $('a#prevmun').click(function(){
        $('input#pagmun').val(pagmun - 1);
        $('input#divoculta').val('cobtbs');
        $('form#formmunicipios').submit();
    });
    $('a#sigmun').click(function(){
        $('input#pagmun').val(+pagmun + 1);
        $('input#divoculta').val('cobtbs');
        $('form#formmunicipios').submit();
    });
    $('a#ultmun').click(function(){
        $('input#pagmun').val(npagmun);
        $('input#divoculta').val('cobtbs');
        $('form#formmunicipios').submit();
    });
});
