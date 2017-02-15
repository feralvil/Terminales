<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotanew_$idioma.php";
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
                window.top.location.href = "https://comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
    </head>
    <body>
<?php
if ($permiso == 2) {
    $sql_mun = "SELECT * FROM municipios ORDER BY MUNICIPIO ASC";
    $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
    $nmun = mysql_num_rows($res_mun);
    if ($nmun == 0) {
?>
        <p class='error'>No hay resultados en la consulta del Municipio</p>
<?php
    }
    $sql_organiza = "SELECT * FROM organizaciones ORDER BY ORGANIZACION ASC";
    $res_organiza = mysql_query($sql_organiza) or die("Error en la consulta de Organizaciones: " . mysql_error());
    $norganiza = mysql_num_rows($res_organiza);
    if ($norganiza == 0) {
?>
        <?php echo $errnoorg; ?>
<?php
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
        <h1><?php echo $h1; ?></h1>
        <form name="formflota" action="update_flota.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="origen" value="nueva">
            <h2><?php echo $h2admin; ?></h2>
            <table>
                <tr>
                    <th class="t40p"><?php echo $nomflota; ?></th>
                    <th class="t5c"><?php echo $acroflota; ?></th>
                    <th class="t5c"><?php echo $usuflota; ?></th>
                    <th class="t5c"><?php echo $passflota; ?></th>
                </tr>
                <tr>
                    <td><input type="text" name="flota" value="" size="40"></td>
                    <td><input type="text" name="acronimo" value="" size="10"></td>
                    <td><input type="text" name="usuario" value="" size="10"></td>
                    <td><input type="password" name="password" value="" size="10"></td>
                </tr>
            </table>
            <h2><?php echo $h2otros; ?></h2>
            <table>
                <tr>
                    <th class="t40p"><?php echo $ciudad; ?></th>
                    <th class="t40p"><?php echo $domicilio; ?></th>
                    <th class="t5c"><?php echo $cp; ?></th>
                    <th class="t5c"><?php echo $activa; ?></th>
                    <th class="t5c"><?php echo $encripta; ?></th>
                </tr>
                <tr>
                    <td>
                        <select name="ine">
<?php
                           for ($i = 0; $i < $nmun; $i++) {
                               $row_mun = mysql_fetch_array($res_mun);
                               $ine_mun = $row_mun["INE"];
                               $nom_mun = $row_mun["MUNICIPIO"];
?>
                            <option value="<?php echo $ine_mun; ?>"><?php echo $nom_mun; ?></option>
<?php
                           }
?>
                        </select>
                    </td>                    
                    <td><input type="text" name="domicilio" value="" size="30"></td>
                    <td><input type="text" name="cp" value="" size="5"></td>
                    <td>
                        <select name="activa">
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </td>
                    <td>
                        <select name="encriptacion">
                            <option value="SI">SI</option>
                            <option value="NO">NO</option>
                        </select>
                    </td>
                </tr>
            </table>
            <h2><?php echo $h2organiza; ?></h2>
            <select name="organiza">
                <option value="0"><?php echo $selorganiza; ?></option>
            <?php
            for ($i = 0; $i < $norganiza; $i++){
                $organiza = mysql_fetch_array($res_organiza);
            ?>
                <option value="<?php echo $organiza['ID'];?>">
                    <?php echo $organiza['ORGANIZACION'];?>
                </option>
            <?php
            }
            ?>
            </select>
            
            
            <h2><?php echo $h2rango; ?></h2>
            <p>
                <input type="text" name="rangoini" value="" size="10"> &mdash; <input type="text" name="rangofin" value="" size="10">
            </p>
            <h2><?php echo $h2arch; ?></h2>
            <p>
                <label for="archivo">Seleccionar:</label>
                <input type="file" name="archivo" id="archivo" />
            </p>
            
            <table>
                <tr>
                    <td class="borde"><input type='image' name='nueva' src='imagenes/guardar.png' alt='Guardar' title="Guardar"><br>Guardar Flota</td>
                    <td class="borde"><a href='flotas.php'><img src='imagenes/atras.png' alt='<?php echo $volver; ?>' title="<?php echo $volver; ?>"></a><br><?php echo $volver; ?></td>
                    <td class="borde"><a href='#' onclick='document.formflota.reset();'><img src='imagenes/no.png' alt='<?php echo $cancel; ?>' title="<?php echo $cancel; ?>"></a><br><?php echo $cancel; ?></td>
                </tr>
            </table>
        </form>
<?php
}
 else {
?>
    <h1><?php echo $h1perm ?></h1>
    <p class='error'><?php echo $permno; ?></p>
<?php
 }
 ?>
    </body>
</html>