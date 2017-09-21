/*
Funciones Javascript para flotas_editar.php
*/

$(function(){
    // Boton atrás:
    $("a#botvolver").click(function(){
        $("form#detflota").submit();
    });
    // Boton cancelar:
    $("a#botreset").click(function(){
        $("form#formflota")[0].reset()
    });
    // Validar formulario
    $("form#formflota").submit(function(e){
        var valido = true;
        var error = '';
        var flota = $('input#flota').val();
        var acronimo = $('input#acronimo').val();
        if (flota.length < 6){
            error = $('input#errflotalong').val();
            valido = false;
            $('input#flota').focus();
        }
        else{
            if (acronimo.length < 6){
                error = $('input#erracrolong').val();
                valido = false;
                $('input#acronimo').focus();
            }
        }
        if(!valido) {
            alert(error);
            e.preventDefault();
        }
    });
});
