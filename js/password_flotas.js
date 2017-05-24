// Funciones JQuery para password_flotas.php:

$(function(){
    // Cambio del checkbox
    $("input#seltodo").change(function(){
        var seltodo = $(this).prop('checked');
        $("input[id^=flotasel]").each(function(){
            $(this).prop('checked', seltodo);
        });
    });
});
