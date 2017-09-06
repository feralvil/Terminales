<?php
/*
Mensajes Flash para actualizaciones de ficheros:
*/
if ($update == "KO"){
    $clase = "flashko";
    $imagen = "imagenes/cancelar.png";
    $alt = "Error";
}
if ($update == "OK"){
    $clase = "flashok";
    $imagen = "imagenes/okm.png";
    $alt = "OK";
}
?>
<p class="<?php echo $clase;?>" id="mensflash">
    <img src="<?php echo $imagen;?>" alt="<?php echo $alt;?>" title="<?php echo $alt;?>"> &mdash; <?php echo $mensflash;?>
</p>
