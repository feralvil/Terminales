<?php
$phpuser = $_SERVER['PHP_AUTH_USER'];
$phppass = $_SERVER['PHP_AUTH_PW'];
$flota_usu = 0;
$resetpassw = 0;

/* Determinamos el usuario para ver la gestión de permisos (Nueva versiñón) */
$sql_user = "SELECT ID, PASSRESET FROM flotas WHERE (LOGIN = '$phpuser') AND (PASSWORD = '$phppass')";
$res_user = mysqli_query($link, $sql_user) or die('Error en la consulta de Usuario: ' . mysql_error);
$nuser = mysqli_num_rows($res_user);
if ($nuser > 0){
    $row_user =  mysqli_fetch_assoc($res_user);
    if ($row_user['PASSRESET'] == "PDTE"){
        $flota_usu = 0;
        $resetpassw = 1;
    }
    else {
        $flota_usu = $row_user['ID'];
    }
}
?>
