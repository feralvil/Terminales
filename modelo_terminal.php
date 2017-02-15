<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/termmodelo_$idioma.php";
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
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($flota_usu == ""){
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
        if ($permiso > 1){
            $sql_modelo = "SELECT DISTINCT modelo FROM TERMINALES ORDER BY MODELO ASC";
            $res_modelo = mysql_query($sql_modelo, $link) or die($errsqlmod . ': ' . mysql_error());
            $nmodelo = mysql_num_rows($res_modelo);
        ?>
            <h1><?php echo $h1; ?></h1>
            <?php
            if ($nmodelo > 0) {
            ?>
                <form action="modelo_terminal.php" method="POST" name="formmodelo">
                    <table>
                        <tr>
                            <td>
                                <label for="selModelo"><?php echo $txtmodelo; ?></label> &nbsp;
                                <select name="modelo" onchange="document.formmodelo.submit();" id="selModelo">
                                    <option value="NN">Seleccionar</option>
                                    <?php
                                    for ($i = 0; $i < $nmodelo; $i++){
                                        $row_modelo = mysql_fetch_array($res_modelo);
                                    ?>
                                        <option value="<?php echo $row_modelo[0];?>" <?php if ($modelo == $row_modelo[0]) echo 'selected'; ?>>
                                            <?php echo $row_modelo[0];?>
                                        </option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </form>
                <form action="update_terminal.php" method="POST" name="formupdate">
                    <input type="hidden" name="origen" value="modelo">
                    <input type="hidden" name="modeloact" value="<?php echo $modelo;?>">
                    <?php
                    $modelonew = "";
                    if (($modelo != "00") && ($modelo != "")){
                        $arraymod = explode(' ', $modelo);
                        foreach ($arraymod as $mod) {
                            $modelonew .= $mod;
                        }
                    }
                    ?>
                    <table>
                        <tr>
                            <label for="newModelo"><?php echo $txtmodnew; ?></label> &nbsp;
                            <input type="text" name="modelonew" id="newModelo" value="<?php echo $modelonew;?>">
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td class="borde">
                                <a href='terminales.php'>
                                    <img src='imagenes/atras.png' alt='<?php echo $botatras;?>' title="<?php echo $botatras;?>">
                                </a><br><?php echo $botatras;?>
                            </td>
                            <?php
                            if ($modelonew != ""){
                            ?>
                                <td class="borde">
                                    <input type='image' name='action' src='imagenes/guardar.png' alt='<?php echo $botguarda;?>' title="<?php echo $botguarda;?>">
                                    <br><?php echo $botguarda;?>
                                </td>
                                <td class="borde">
                                    <a href='#' onclick='document.formmodelo.reset();document.formupdate.reset();'>
                                        <img src='imagenes/no.png' alt='<?php echo $botcancel;?>' title="<?php echo $botcancel;?>">
                                    </a><br><?php echo $botcancel;?>
                                </td>
                            <?php
                            }
                            ?>
                        </tr>
                    </table>
                </form>
            <?php
            }
            else{
            ?>
                <p class="error"><?php echo $errnomodelo; ?></p>
            <?php
            }
            ?>
        <?php
        }
        else{
        ?>
            <h1><?php echo $h1perm; ?></h1>
            <p class="error"><?php echo $errnoperm; ?></p>
        <?php
        }
        ?>
    </body>
</html>
