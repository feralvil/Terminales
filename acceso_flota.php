<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotasacceso_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
// Conexión a la BBDD:
require_once 'conectabbdd.php';

// Obtenemos el usuario
include_once('autenticacion.php');
/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación (Oficina COMDES)
 */
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
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/flotas_acceso.js"></script>
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
    include_once 'sql/flotas_acceso.php';
    if ($nflota > 0){
?>
        <h1><?php echo $h1; ?> <?php echo $flota["FLOTA"]; ?> (<?php echo $flota["ACRONIMO"]; ?>)</h1>
        <form name="detflota" id="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
        </form>
        <form name="formacceso" id="formacceso" action="update_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <input type="hidden" name="origen" value="acceso">
            <input type="hidden" name="errpasslong" id="errpasslong" value="<?php echo $errpasslong; ?>">
            <input type="hidden" name="errpassconf" id="errpassconf" value="<?php echo $errpassconf; ?>">
            <table>
                <tr>
                    <th><?php echo $txtusuario; ?></th>
                    <td colspan="3"><?php echo $flota["LOGIN"]; ?></td>
                </tr>
                <tr>
                    <th class="t4c"><?php echo $txtpass1; ?></th>
                    <td><input type='password' name='passwd1' id="password" size='20' maxlength='20' value=""></td>
                    <th class="t4c"><?php echo $txtpass2; ?></th>
                    <td><input type='password' name='passwd2' id="passconf" size='20' maxlength='20' value=""></td>
                </tr>
            </table>
            <table>
                <tr>
                    <td class='borde'>
                        <input type='image' name='guardar' value="<?php echo $botguardar; ?>" src='imagenes/guardar.png' alt='<?php echo $botguardar; ?>' title="<?php echo $botguardar; ?>"><br><?php echo $botguardar; ?>
                    </td>
                    <td class='borde'>
                        <a href='#' id="botreset">
                            <img src='imagenes/no.png' alt='<?php echo $botcancel; ?>' title="<?php echo $botcancel; ?>">
                        </a><br><?php echo $botcancel; ?>
                    </td>
                    <td class='borde'>
                        <a href='#' id="botatras">
                            <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title="<?php echo $botatras; ?>">
                        </a><br><?php echo $botatras; ?>
                    </td>
                </tr>
            </table>
        </form>
    <?php
    }
    else {
    ?>
        <p class='error'><?php echo $errnoflota; ?></p>
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
