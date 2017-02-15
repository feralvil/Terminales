<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/organizanew_$idioma.php";
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
<?php
        if ($flota_usu == 0){
?>
            <script type="text/javascript">
                window.top.location.href = "https://comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
    </head>
    <body>
    <?php
    if ($permiso > 1){
        $sql_munorg = "SELECT * FROM municipios ORDER BY MUNICIPIO ASC";
        $res_munorg = mysql_query($sql_munorg) or die("Error en la consulta de Municipios" . mysql_error());
        $nmunorg = mysql_num_rows($res_munorg);
        $sql_contactos = "SELECT * FROM contactos ORDER BY NOMBRE ASC";
        $res_contactos = mysql_query($sql_contactos) or die("Error en la consulta de Contactos" . mysql_error());
        $ncontactos = mysql_num_rows($res_contactos);
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
        <h1>
            <?php echo $h1;?> &mdash;
            <a href="nueva_organiza.php" target="_blank">
                <img src="imagenes/newtab.png" alt="<?php echo $newtab;?>" title="<?php echo $newtab;?>">
            </a>
        </h1>
        <h2><?php echo $h2organiza;?></h2>
        <form name="orgedit" action="update_org.php" method="post">
            <input type="hidden" name="origen" value="agregar">
            <table>
                <tr>
                    <th><?php echo $thorganiza; ?></th>
                    <th><?php echo $thacronimo; ?></th>
                    <th><?php echo $thdomicilio; ?></th>
                    <th><?php echo $thcp; ?></th>
                    <th><?php echo $thciudad; ?></th>
                </tr>
                <tr>
                    <td><input type="text" name="organiza" size="30"></td>
                    <td><input type="text" name="acronimo" size="10"></td>
                    <td><input type="text" name="domicilio" size="30"></td>
                    <td><input type="text" name="cp" size="5"></td>
                    <td>
                        <select name="ine">
                            <option value="00000"><?php echo $txtselmuni;?></option>
                        <?php
                        for ($i = 0; $i < $nmunorg; $i++){
                            $row_muni = mysql_fetch_array($res_munorg);
                        ?>
                            <option value="<?php echo $row_muni['INE'];?>">
                                <?php echo $row_muni['MUNICIPIO'];?>
                            </option>
                        <?php
                        }
                        ?>
                        </select>
                    </td>
                </tr>
            </table>
            <h2><?php echo $h2resp;?></h2>
            <h3><?php echo $h3exist;?></h3>
            <select name="idcontacto">
                <option value="0"><?php echo $txselcont; ?></option>
                <?php
                for ($i = 0; $i < $ncontactos; $i++){
                    $contacto = mysql_fetch_array($res_contactos);
                ?>
                    <option value="<?php echo $contacto['ID'];?>">
                        <?php echo $contacto['NOMBRE'];?>
                    </option>
                <?php
                }
                ?>
            </select>
            <h3><?php echo $h3new;?></h3>
            <table>
                <tr>
                    <th><?php echo $thnombre;?></th>
                    <th><?php echo $thcargo;?></th>
                    <th><?php echo $thmail;?></th>
                    <th><?php echo $thtelef;?></th>
                </tr>
                <tr>
                    <td><input type="text" name="nombre" size="30"></td>
                    <td><input type="text" name="cargo" size="30"></td>
                    <td><input type="text" name="mail" size="20"></td>
                    <td><input type="text" name="telefono" size="20"></td>
                </tr>
            </table>
            <table>
                <tr>
                    <td class="borde">
                        <a href='organizaciones.php'>
                            <img src='imagenes/atras.png' alt='<?php echo $botatras; ?>' title='<?php echo $botatras; ?>'>
                        </a><br><?php echo $botatras; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.orgedit.submit();">
                            <img src='imagenes/guardar.png' alt='<?php echo $botguarda; ?>' title='<?php echo $botguarda; ?>'>
                        </a><br><?php echo $botguarda; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.orgedit.reset()">
                            <img src='imagenes/no.png' alt='<?php echo $botreset; ?>' title='<?php echo $botreset; ?>'>
                        </a><br><?php echo $botreset; ?>
                    </td>
                </tr>
            </table>
        </form>
    <?php
    }
    else{
    ?>
        <h1><?php echo $h1noperm;?></h1>
        <p class="error"><?php echo $errnoperm;?></p>
    <?php
    }
    ?>
    </body>
</html>
