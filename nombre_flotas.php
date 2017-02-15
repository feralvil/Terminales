<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotasnom_$idioma.php";
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
<!DOCTYPE html>
<html lang="es">
    <head>
        <title><?php echo $h1; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($flota_usu = 0){
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
    if ($permiso > 1){
    ?>
        <h1><?php echo $h1; ?></h1>
        <form name="formtipo" id="formnombre" action="nombre_flotas.php" method="post">
            <h4><?php echo $h2tipo; ?></h4>
            <select name="tipo" onchange="document.formtipo.submit();">
                <option value="NN"><?php echo $txtseltipo; ?></option>
                <option value="PL" <?php if ($tipo == "PL"){echo "selected";} ;?>><?php echo $txtselpl; ?></option>
                <option value="PC" <?php if ($tipo == "PC"){echo "selected";} ;?>><?php echo $txtselpc; ?></option>
            </select>
            <?php
            $condicion = "((FLOTA LIKE 'PL%') OR (FLOTA LIKE 'PC%'))";
            if (($tipo == 'PL') || ($tipo == 'PC')){
                $condicion = "(FLOTA LIKE '" . $tipo . "%')";
            }
            $sql_flotas = "SELECT * FROM flotas WHERE " . $condicion . " ORDER BY FLOTA ASC";
            $flotas = mysql_query($sql_flotas) or die("Error en la consulta de Flotas" . mysql_error());
            $nflotas = mysql_num_rows($flotas);
            ?>
        </form>
        <form name="formflotas" id="formflotas" action="update_flota.php" method="post">
            <input type="hidden" name="origen" value="nomflota">
            <h4><?php echo $h2res; ?></h4>
            <table>
                <tr>
                    <td class="borde">
                        <?php echo $txtnflotas;?>: <b><?php echo $nflotas;?></b>
                    </td>
                    <?php
                    if ($nflotas > 0){
                    ?>
                        <td class="borde">
                            <input type="image" src="imagenes/importar.png" alt="<?php echo $botguardar;?>" title="<?php echo $nflotas;?>">
                            &mdash; <?php echo $nflotas;?>
                        </td>
                    <?php
                    }
                    ?>
                </tr>
            </table>
        </form>
            <?php
            if ($nflotas > 0){
            ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th><?php echo $thnomact;?></th>
                        <th><?php echo $thnomnou;?></th>
                    </tr>
                <?php
                for ($i = 0; $i < $nflotas; $i++){
                    $flota = mysql_fetch_array($flotas);
                    $prefijo = substr($flota['FLOTA'], 0, 2);
                    if ($prefijo == "PL"){
                        $nomnou = "POLICÍA LOCAL " . substr($flota['FLOTA'], 3);
                    }
                    elseif ($prefijo == "PC") {
                        $nomnou = "PROTECCIÓN CIVIL " . substr($flota['FLOTA'], 3);
                    }
                    else{
                        $nomnou = substr($flota['FLOTA'], 3);
                    }
                ?>
                    <tr <?php if (($i % 2) == 1){echo "class='filapar'";}?>>
                        <td class="centro">
                            <?php echo $flota['ID'];?>
                        </td>
                        <td>
                            <?php echo $flota['FLOTA'];?>
                        </td>

                        <td>
                            <?php echo $nomnou;?>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class="error">Error: <?php echo $errnoflotas;?></p>
            <?php
            }
            ?>
    <?php
    }
    else{
    ?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $errnoperm; ?></p>
    <?php
    }
    ?>
    </body>
</html>
