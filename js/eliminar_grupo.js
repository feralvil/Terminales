// Funciones JQuery para eliminar_grupo.php:
$(function(){
   // Botón Cancelar:
  $("a[id=botcancel]").click(function(){
      $("form#formdetalle").submit();
  });
});
