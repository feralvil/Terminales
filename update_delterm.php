<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/termexc_$idioma.php";
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
        $sql_flota = "SELECT * from FLOTAS WHERE ID = '$idflota'";
        $res_flota = mysql_query($sql_flota) or die("Error en la consulta de flota: ".mysql_error());
        $nflota = mysql_num_rows($res_flota);
        if ($nflota == 0){
            $res_update = false;
            $mensflash = $error_noflota;
        }
        else{
            if (!(empty ($delterm))){
                $ntermdel = count($delterm);
                for ($i = 0; $i < $ntermdel; $i++){
                    $sql_delterm = "DELETE FROM terminales WHERE ID = '".$delterm[$i]."'";
                    $res_update = mysql_query($sql_delterm) or die ($errdelterm." ID -  ".$delterm[$i].": ".mysql_error());
                }
                if ($res_update){
                    $fichero = "flotas/$idflota.xls";
                    if (unlink($fichero)){
                        $update = "OK";
                        $mensflash = sprintf($menstermd, $ntermdel);
                    }
                    else{
                        $update = "KO";
                        $mensflash = "$nterm $mensaje. $errdelfich.";
                    }
                }
                else{
                    $update = "KO";
                    $mensflash = $errdelt;
                }
            }
            else{
                $update = "OK";
                $mensflash = $termimp;
            }

            // Terminales para AKDC
            $termakdc = array();
            if(!(empty($idupd))){
                foreach ($idupd as $idakdc){
                    $termakdc[] = $idakdc;
                }
            }
            if(!(empty($issiins))){
                foreach ($issiins as $issiakdc){
                    $sql_termissi = "SELECT ID FROM terminales WHERE (FLOTA = '$idflota') AND (ISSI = '$issiakdc')";
                    $res_termissi = mysql_query($sql_termissi) or die ($errissi . " " . $issiakdc . " " .mysql_error());
                    $ntermissi = mysql_num_rows($res_termissi);
                    if ($ntermissi > 0){
                        $termissi = mysql_fetch_array($res_termissi);
                        $termakdc[] = $termissi['ID'];
                    }

                }
            }
        }

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

        <h1><?php echo $titulo; ?></h1>
        <form name="formupd" action="detalle_flota.php" method="POST">
            <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
            <input name="update" type="hidden" value="<?php echo $update;?>">
            <input name="mensflash" type="hidden" value="<?php echo $mensflash;?>">
            <input name="parametro" type="hidden" id="param" value="itsi">
            <?php
            foreach ($termakdc as $idakdc){
            ?>
                <input type="hidden" name="termsel[]" value="<?php echo $idakdc; ?>">
            <?php
            }
            ?>
            <table>
                <tr>
                    <td class="borde">
                        <a href='#' onclick="document.formupd.action='detalle_flota.php';document.formupd.submit();">
                            <img src='imagenes/adelante.png' alt='<?php echo $volver; ?>' title="<?php echo $volver; ?>">
                        </a><br><?php echo $volver; ?>
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.formupd.action='xlsdotsflota.php';document.formupd.submit();">
                            <img src='imagenes/dots.png' alt='Exportar a DOTS' title="Exportar a DOTS">
                        </a><br>Exportar a DOTS
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.formupd.action='akdc_ref.php';document.getElementById('param').value='itsi';document.formupd.submit();">
                            <img src='imagenes/akdc.png' alt='Generar REF-ITSI' title="Generar REF-ITSI">
                        </a><br>Generar REF-ITSI
                    </td>
                    <td class="borde">
                        <a href='#' onclick="document.formupd.action='akdc_ref.php';document.getElementById('param').value='numk';document.formupd.submit();">
                            <img src='imagenes/akdc.png' alt='Generar REF-K' title="Generar REF-K">
                        </a><br>Generar REF-K
                    </td>
                </tr>
            </table>
        </form>
<?php
    }
    else{
?>
        <h1><?php echo $h1perm; ?></h1>
        <p class='error'><?php echo $permno; ?></p>
<?php
    }
?>

    </body>
</html>
