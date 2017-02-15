// Funciones JQuery para detalle_grupo.php:
$(function(){
    // Botón editar:
    $("a[id=botedit]").click(function(){
        $("form#formgrupo").attr('action', 'editar_grupo.php');
        $("form#formgrupo").submit();
    });
   // Botón borrar:
   $("a[id=botdel]").click(function(){
       $("form#formgrupo").attr('action', 'eliminar_grupo.php');
       $("form#formgrupo").submit();
   });
   // Botón ir a flota:
   $("a[id^=det]").click(function(){
       var idflota = $(this).attr('id');
       idflota = idflota.substr(4);
       $("input#idflota").val(idflota);
       $("form#formflota").submit();
   });
});
