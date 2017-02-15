<?php
$phpuser = $_SERVER['PHP_AUTH_USER'];
$phppass = $_SERVER['PHP_AUTH_PW'];
$flota_usu = 0;

/* Determinamos el usuario para ver la gestión de permisos */
$sql_user = "SELECT ID FROM flotas WHERE (LOGIN = '$phpuser') AND (PASSWORD = '$phppass')";
$res_user = mysql_db_query($base_datos, $sql_user) or die('Error en la consulta de Usuario: ' . mysql_error);
$nuser = mysql_num_rows($res_user);
if ($nuser > 0){
    $row_user = mysql_fetch_array($res_user);
    $flota_usu = $row_user["ID"];
}
?>