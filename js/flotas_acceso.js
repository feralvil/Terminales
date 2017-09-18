/*
Funciones Javascript para flotas_acceso.php
*/

$(function(){
    // Boton atrás:
    $("a#botatras").click(function(){
        $("form#detflota").submit();
    });
    // Boton cancelar:
    $("a#botreset").click(function(){
        $("form#formacceso")[0].reset()
    });
    // Validar formulario
    $("form#formacceso").submit(function(e){
        var valido = true;
        var error = '';
        var password = $('input#password').val();
        var passconf = $('input#passconf').val();
        if (password.length < 6){
            error = $('input#errpasslong').val();
            valido = false;
            $('input#password').focus();
        }
        else{
            if (password != passconf){
                error = $('input#errpassconf').val();
                valido = false;
                $('input#password').focus();
            }
        }
        if(!valido) {
            alert(error);
            e.preventDefault();
        }
    });
});
