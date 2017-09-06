/*
Funciones Javascript para flotas.php
*/
$(function(){
    // Cambio en el select de Flota:
    $("select").change(function(){
        $("input#inputpag").val(1);
        $("form#formflotas").submit();
    });
    // Enlace Primera página
    $("a#primera").click(function(){
        $("input#inputpag").val(1);
        $("form#formflotas").submit();
    });
    // Enlace Página anterior
    $("a#anterior").click(function(){
        var newpag =  $("input#inputpag").val();
        newpag--;
        $("input#inputpag").val(newpag);
        $("form#formflotas").submit();
    });
    // Enlace página siguiente:
    $("a#siguiente").click(function(){
        var newpag =  $("input#inputpag").val();
        newpag++;
        $("input#inputpag").val(newpag);
        $("form#formflotas").submit();
    });
    // Enlace Última página
    $("a#ultima").click(function(){
        $("input#inputpag").val($("input#inputnpag").val());
        $("form#formflotas").submit();
    });
    // Botón Exportar a PDF:
    $("a#pdfflotas").click(function(){
        $("form#formflotas").attr('action', 'flotas_pdf.php');
        $("form#formflotas").attr('target', '_blank');
        $("form#formflotas").submit();
        $("form#formflotas").attr('target', '_self');
        $("form#formflotas").attr('action', 'flotas.php');
   });
    // Botón Exportar a Excel:
    $("a#xlsflotas").click(function(){
        $("form#formflotas").attr('action', 'flotas_excel.php');
        $("form#formflotas").attr('target', '_blank');
        $("form#formflotas").submit();
        $("form#formflotas").attr('target', '_self');
        $("form#formflotas").attr('action', 'flotas.php');
    });
    // Link detalle:
    $("a[name^=det]").click(function(){
        var idflota = $(this).attr('id');
        $("input#idflota").val(idflota);
        $("form#formdetalle").submit();
    });
});
