<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotaacc_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>" . mysql_error();
}
else{
    // Codificación de carácteres de la conexión a la BBDD
    mysql_set_charset('utf8',$link);
}
// ------------ Conexión a BBDD de Terminales ----------------------------------------- //

// Importamos las variables de formulario:
import_request_variables("gp", "");

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
else{
    if ($flota_usu == $idflota) {
        $permiso = 1;
    }
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <script type="text/javascript">
            function validaForm(){
                var pwd = document.forms["formacceso"]["passwd1"].value;
                if ((pwd==null)||(pwd=="")){
                    alert('<?php echo $passvacia; ?>');
                    return false;
                }
                else{
                    var pwd2 = document.forms["formacceso"]["passwd2"].value;
                    if (pwd != pwd2){
                        alert('<?php echo $passconf; ?>');
                        return false;
                    }
                    else{
                        return true;
                    }
                }
            }
        </script>
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
if ($permiso > 0) {
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota == 0) {
?>
        <p class='error'><?php echo $noflota; ?></p>
<?php
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
?>
        <h1><?php echo $h1; ?> <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
        <form name="formacceso" action="update_flota.php" method="POST" onsubmit="return validaForm();">
            <table>
                <tr>
                    <th><?php echo $usutxt; ?></th>
                    <td colspan="3"><?php echo $row_flota["LOGIN"]; ?></td>
                </tr>
                <tr>
                    <th class="t4c"><?php echo $pwd1; ?></th>
                    <td><input type='password' name='passwd1' size='20' maxlength='20' value=""></td>
                    <th class="t4c"><?php echo $pwd2; ?></th>
                    <td><input type='password' name='passwd2' size='20' maxlength='20' value=""></td>
                </tr>
            </table>
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <input type="hidden" name="flota_org" value="<?php echo $row_flota["FLOTA"]; ?>">
            <input type="hidden" name="acro_org" value="<?php echo $row_flota["ACRONIMO"]; ?>">
            <input type="hidden" name="origen" value="acceso">
            <table>
                <tr>
                    <td class='borde'>
                        <input type='image' name='guardar' value="<?php echo $guardar; ?>" src='imagenes/guardar.png' alt='<?php echo $guardar; ?>' title="<?php echo $guardar; ?>"><br><?php echo $guardar; ?>
                    </td>
                    <td class='borde'>
                        <a href='#' onclick='document.formacceso.reset();'>
                            <img src='imagenes/no.png' alt='<?php echo $cancel; ?>' title="<?php echo $cancel; ?>">
                        </a><br><?php echo $cancel; ?>
                    </td>
                    <td class='borde'>
                        <a href='#' onclick='document.detflota.submit();'>
                            <img src='imagenes/atras.png' alt='<?php echo $volver; ?>' title="<?php echo $volver; ?>">
                        </a><br><?php echo $volver; ?>
                    </td>
                </tr>
            </table>
        </form>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
        </form>
<?php
    }
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