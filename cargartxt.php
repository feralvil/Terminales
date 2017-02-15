<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/cargartxt_$idioma.php";
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
<?php
    if ($permiso > 0) {
        if (isset ($update)){
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
            <p class="<?php echo $clase;?>">
            <img src="<?php echo $imagen;?>" alt="<?php echo $alt;?>" title="<?php echo $alt;?>"> &mdash; <?php echo $mensflash;?>
            </p>
<?php
        }
?>
        <h1><?php echo $h1; ?></h1>
        <h2><?php echo $h2datos; ?></h2>
        <form name="updteik" action="update_archtxt.php" method="POST" enctype="multipart/form-data">
            <table>
                <tr>
                    <td>
                        <label for="archivo"><?php echo $selarch;?>:</label>
                        <input type="file" name="archivo" id="archivo" />
                    </td>
                    <td>
                        <label for="accion"><?php echo $selacc;?>:</label>
                        <select name="accion">
                        <?php
                        $acciones = array("NO","FLOTAS","TERMINALES");
                        for ($i = 0; $i < count($acciones);$i++){
                        ?>
                            <option value="<?php echo $acciones[$i];?>" <?php if($accion == $acciones[$i]) {echo 'selected';}?>>
                            <?php echo $optsel[$i];?>
                            </option>
                        <?php
                        }
                        ?>
                        </select>
                    </td>
                </tr>
            </table>
        <table>
            <tr>
                <td class="borde">
                    <input type='image' name='nueva' src='imagenes/guardar.png' alt='Guardar' title="Guardar"><br>Guardar
                </td>
            </tr>
        </table>
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
