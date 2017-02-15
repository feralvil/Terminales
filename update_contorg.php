<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/organizacon_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>" . mysql_error();
}
else {
     // Seleccionamos la BBDD y codificamos la conexión en UTF-8:
    if (!mysql_select_db($base_datos, $link)) {
        echo 'Error al seleccionar la Base de Datos: '. mysql_error();
        exit;
    }
    mysql_set_charset('utf8', $link);
}
// ------------------------------------------------------------------------------------- //

// Importamos las variables de formulario:
import_request_variables("p", "");

/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación (Oficina COMDES)
 */
// Obtenemos el usuario
include_once('auth_user.php');

$permiso = 0;
if ($flota_usu == 100) {
    $permiso = 2;
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
<?php
        if ($flota_usu == 0){
?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
    </head>
    <body>
<?php
    if ($permiso == 2) {
        $res_update = false;
        if ($origen == "editar"){
            $titulo = $titedi;
            $mensaje = $mensedi;
            $error = $erredi;           
            if ($idcont > 0){
                $sql_update = "UPDATE contactos SET NOMBRE='$nombre', NIF='$nif', CARGO='$cargo', ";
                $sql_update = $sql_update . "TELEFONO='$telefono', MAIL='$mail' WHERE ID=$idcont";
                $res_update = mysql_query($sql_update) or die ("Error al modificar contacto: " . mysql_error($link));
            }
            else{
                $error .= ". " . $errnocont;
            }            
        }
        
        if ($origen == "borrar"){
            $titulo = $titdel;
            $mensaje = $mensdel;
            $error = $errdel;
            $sql_update = "UPDATE organizaciones SET RESPONSABLE = 0 WHERE ID = " . $idorg;
            $res_update = mysql_query($sql_update) or die ("Error al borrar contacto: " . mysql_error($link));
        }
        if ($origen == "addexist"){
            $rolindex = substr($rol, 0, 3);
            $titulo = $titexist;
            $mensaje = $mensexist;
            $error = $errexist;
            if ($idcont > 0){
                $sql_update = "UPDATE organizaciones SET RESPONSABLE = $idcont WHERE ID = " . $idorg;
                $res_update .= mysql_query($sql_update) or die ("Error al insertar contacto existente: " . mysql_error($link));
            }
            else{
                $error .= ". " . $errnocontexist;
            }
        }
        if ($origen == "addnew"){
            $titulo = $titnew;
            $mensaje = $mensnew;
            $error = $errnew;
            if ($nombre == ""){
                $error .= ". " . $errnocontnew;
            }
            else{
                $sql_check = "SELECT * FROM contactos WHERE (NOMBRE = '$nombre')";
                $res_check = mysql_query($sql_check) or die ("Error en la consulta de comprobación: " . mysql_error($link));
                $ncheck = mysql_num_rows($res_check);
                if ($ncheck > 0){                    
                    $error .= ". " . $errcontrep;
                }
                else {
                    $sql_insert = "INSERT INTO contactos (NOMBRE, CARGO, NIF, MAIL, TELEFONO)";
                    $sql_insert .= " VALUES ('$nombre', '$cargo', '$nif', '$mail', '$telefono')";
                    $res_insert = mysql_query($sql_insert) or die ("Error al insertar nuevo contacto: " . mysql_error($link));
                    $idcont = mysql_insert_id($link);
                    $sql_update = "UPDATE organizaciones SET RESPONSABLE = $idcont WHERE ID = " . $idorg;
                    $res_update = mysql_query($sql_update) or die ("Error al insertar nuevo contacto en la Organización: " . mysql_error($link));
                }
            }
        }
        
        if ($res_update){
            $enlace = $enlaceok;
            $mensflash = $mensaje;
            $update = "OK";
        }
        else{
            $enlace = $enlacefail;
            $mensflash = $error.  mysql_error();
            $update = "KO";
        }
?>
       <h1><?php echo $titulo; ?></h1>
        <form name="formupd" action="contactos_organiza.php" method="POST">
            <input name="idorg" type="hidden" value="<?php echo $idorg;?>">
            <input name="update" type="hidden" value="<?php echo $update;?>">
            <input name="mensflash" type="hidden" value="<?php echo $mensflash;?>">
        </form>
         <script language="javascript" type="text/javascript">
            document.formupd.submit();
         </script>
         <noscript>
                <input type="submit" value="verify submit">
         </noscript>
<?php
    }
    else {
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
