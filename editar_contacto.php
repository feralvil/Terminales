<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contedi_$idioma.php";
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
        echo 'Error al seleccionar la Base de Datos: ' . mysql_error();
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
        // Si la sesión de Joomla ha caducado, recargamos la página principal
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
        <form name="contflota" action="contactos_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
        </form>
        <?php
        if ($permiso == 2) {
            //datos de la tabla Flotas
            $roltxt = $rolestxt[$rol];
            $sql_flota = "SELECT * FROM flotas WHERE ID = " . $idflota;
            $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
            $nflota = mysql_num_rows($res_flota);
            if ($nflota > 0) {
                $row_flota = mysql_fetch_array($res_flota);
        ?>    
            <h1>Modificar <?php echo $roltxt; ?> de la Flota <?php echo $row_flota['FLOTA']; ?> (<?php echo $row_flota['ACRONIMO']; ?>)</h1>
            <form name="updatecont" action="update_contacto.php" method="post">
                <input type="hidden" name="origen" value="editar">
                <input type="hidden" name="idflota" value="<?php echo $idflota;?>">
                <input type="hidden" name="idcont" value="<?php echo $idcont;?>">
                <input type="hidden" name="idcf" value="<?php echo $idcf;?>">
                <input type="hidden" name="rol" value="<?php echo $rol;?>">
            <?php
                $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $idcont;
                $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de Contacto: " . mysql_error());
                $ncontacto = mysql_num_rows($res_contacto);
                if ($ncontacto > 0){
                    $contacto = mysql_fetch_array($res_contacto);
            ?>
                    <table>
                        <tr>
                            <th class="t4c"><?php echo $thnombre; ?></th>
                            <th class="t10c">DNI</th>
                            <th class="t4c"><?php echo $thcargo; ?></th>
                            <th class="t5c"><?php echo $thmail; ?></th>
                            <th class="t5c"><?php echo $thtelef; ?></th>
                        </tr>
                        <tr>
                            <td>
                                <input type="text" name="nombre" value="<?php echo $contacto['NOMBRE']; ?>" size="30">
                            </td>
                            <td>
                                <input type="text" name="nif" value="<?php echo $contacto['NIF']; ?>" size="10">
                            </td>
                            <td>
                                <input type="text" name="cargo" value="<?php echo $contacto['CARGO']; ?>" size="30">
                            </td>
                            <td>
                                <input type="text" name="mail" value="<?php echo $contacto['MAIL']; ?>" size="30">
                            </td>
                            <td>
                                <input type="text" name="telefono" value="<?php echo $contacto['TELEFONO']; ?>" size="20">
                            </td>
                        </tr>
                    </table>
            <?php
                }
                else{
            ?>
                    <p class="error"><?php echo $errnocont; ?></p>    
        <?php
                }
            }
            else{
        ?>
                <p class="error"><?php echo $errnoflota; ?></p>
        <?php
            }
        ?>
            <table>
                <tr>
                    <td class="borde">
                        <a href="#" onclick="document.contflota.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $botcontactos;?>' title="<?php echo $botcontactos;?>">
                        </a><br><?php echo $botcontactos;?>
                    </td>
                    <td class="borde">
                        <input type="image" src="imagenes/guardar.png" alt='<?php echo $botguardar;?>' title="<?php echo $botguardar;?>">
                        <br><?php echo $botguardar;?>
                    </td>
                    <td class="borde">
                        <a href="#" onclick="document.updatecont.reset();">
                            <img src='imagenes/no.png' alt='<?php echo $botcancel;?>' title="<?php echo $botcancel;?>">
                        </a><br><?php echo $botcancel;?>
                    </td>
                </tr>
            </table>
        </form>
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