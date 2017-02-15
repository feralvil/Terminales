// Funciones JQuery para grupos.php:

$(function(){
    // Select de tipos
    $("select").change(function(){
        document.formgrupos.submit();
    });
    // Botón detalle:
   $("a[id^=det]").click(function(){
       var idgrupo = $(this).attr('id');
       idgrupo = idgrupo.substr(4);
       $("input#gissi").val(idgrupo);
       $("form#formaccion").attr('action', 'detalle_grupo.php');
       $("form#formaccion").submit();
   });
   // Botón editar:
  $("a[id^=edi]").click(function(){
      var idgrupo = $(this).attr('id');
      idgrupo = idgrupo.substr(4);
      $("input#gissi").val(idgrupo);
      $("form#formaccion").attr('action', 'editar_grupo.php');
      $("form#formaccion").submit();
  });
  // Botón borrar:
 $("a[id^=del]").click(function(){
     var idgrupo = $(this).attr('id');
     idgrupo = idgrupo.substr(4);
     $("input#gissi").val(idgrupo);
     $("form#formaccion").attr('action', 'eliminar_grupo.php');
     $("form#formaccion").submit();
 });
});
