<?php
// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$link = mysqli_connect($dbserv, $dbusu, $dbpaso, $dbbdatos);
if (mysqli_connect_errno()) {
    echo "<b>ERROR MySQL:</b> ". mysqli_connect_error();
    exit;
}
else{
    // Codificamos la conexión en UTF8:
    mysqli_set_charset($link, 'utf8');
}
?>
