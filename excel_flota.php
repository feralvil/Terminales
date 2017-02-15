<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotaexc_$idioma.php";
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
    if ($permiso != 0) {
        //datos de la tabla Flotas
        $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
        $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
        $nflota = mysql_num_rows($res_flota);
        if ($nflota == 0) {
?>
            <p class='error'><?php echo $errnoflota; ?></p>
<?php
        }
        else {
            $row_flota = mysql_fetch_array($res_flota);
            $usuario = $row_flota["LOGIN"];
        }
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
        <h1><?php echo $h1; ?> <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
        <h2><?php echo $h2admin; ?></h2>
        <table>
            <tr>
                <th class="t10c">ID</th>
                <th class="t40p"><?php echo $nomflota; ?></th>
                <th class="t5c"><?php echo $acroflota; ?></th>
                <th class="t5c"><?php echo $usuflota; ?></th>
                <th class="t10c"><?php echo $activa; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["ID"]; ?></td>
                <td><?php echo $row_flota["FLOTA"]; ?></td>
                <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                <td><?php echo $row_flota["LOGIN"]; ?></td>
                <td><?php echo $row_flota["ACTIVO"]; ?></td>
            </tr>
        </table>
        <form name="updteik" action="update_archexcel.php" method="POST" enctype="multipart/form-data">
            <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
        <h2><?php echo $h2cargar; ?></h2>
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
                    $acciones = array("NO","ACTCONT","IMPTERM","IMPGRUPOS", "IMPPERMISOS");
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
                <td class="borde">
                    <a href="#" onclick="document.detflota.submit();">
                        <img src='imagenes/atras.png' alt='<?php echo $detalle;?>' title="<?php echo $detalle;?>">
                    </a><br><?php echo $detalle." de Flota";?>
                </td>
            </tr>
        </table>
        </form>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
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
