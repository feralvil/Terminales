// Funciones JQuery para detalle_grupo.php:
$(function(){
    // Botón editar:
   $("a[id=botdetalle]").click(function(){
       $("form#formdetalle").submit();
   });
   // Botón borrar:
  $("a[id=botcancel]").click(function(){
      document.formedit.reset();
  });
});
