<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/leerpermisos_$idioma.php";
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
        <title><?php echo $titulo; ?></title>
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
        if (isset($update)) {
            if ($update == "KO") {
                $clase = "flashko";
                $imagen = "imagenes/cancelar.png";
                $alt = "Error";
            }
            if ($update == "OK") {
                $clase = "flashok";
                $imagen = "imagenes/okm.png";
                $alt = "OK";
            }
        ?>
            <p class="<?php echo $clase; ?>">
                <img src="<?php echo $imagen; ?>" alt="<?php echo $alt; ?>" title="<?php echo $alt; ?>"> &mdash; <?php echo $mensflash; ?>
            </p>
        <?php
        }
        if ($permiso == 2){
            //datos de la tabla Flotas
            $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
            $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
            $nflota = mysql_num_rows($res_flota);
        ?>
            <h1><?php echo $h1;?></h1>
            <h2><?php echo $h2flota;?></h2>
            <?php
            if ($nflota > 0){
                $row_flota = mysql_fetch_array($res_flota);
            ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th><?php echo $thflota;?></th>
                        <th><?php echo $thacro;?></th>
                        <th><?php echo $thlogin;?></th>
                    </tr>
                    <tr>
                        <td><?php echo $row_flota['ID'];?></td>
                        <td><?php echo $row_flota['FLOTA'];?></td>
                        <td><?php echo $row_flota['ACRONIMO'];?></td>
                        <td><?php echo $row_flota['LOGIN'];?></td>
                    </tr>
                </table>
                <form name="detflota" action="update_flota.php" method="POST">
                    <input name="origen" type="hidden" value="leerexcel">
                    <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                </form>
                <?php
                 // Aumentamos el tamaño de la memoria:
                ini_set('memory_limit', '64M');
                // Clases para generar el Excel
                /** Error reporting */
                error_reporting(E_ALL);
                date_default_timezone_set('Europe/Madrid');
                /** PHPExcel */
                require_once 'Classes/PHPExcel.php';
                $fichero = "flotas/$idflota.xls";

                // Creamos el objeto PHPExcel
                $objPHPExcel = new PHPExcel();

                $tipoFich = PHPExcel_IOFactory::identify($fichero);
                $objReader = PHPExcel_IOFactory::createReader($tipoFich);
                // Leemos los datos de la hoja de Terminales:
                $nomHoja = "(4) ISSIs - PERMISOS";

                // Sólo nos interesa cargar los datos:
                $objReader->setReadDataOnly(true);
                try {
                    $objPHPExcel = $objReader->load($fichero);
                } catch (Exception $e) {
                    die("Error al cargar el fichero de datos: " . $e->getMessage());
                }

                // Fijamos como hoja activa la primera (sólo se importa una)
                try{
                    $objPHPExcel->setActiveSheetIndexByName($nomHoja);
                }
                catch (Exception $e){
                    $nomHoja = $nomHoja." ";
                    try {
                        $objPHPExcel->setActiveSheetIndexByName($nomHoja);
                    }
                    catch (Exception $f){
                        echo "<p class = 'error'>No se ha encontrado la hoja de datos buscada</p>";
                    }
                }
                // Buscamos la celda de inicio de las celdas:
                $fila = 1;
                $columna = 1;
                // Obtenemos el número de filas y columnas de la hoja:
                $maxfilas = $objPHPExcel->getActiveSheet()->getHighestRow();
                $maxcol = $objPHPExcel->getActiveSheet()->getHighestColumn();
                $maxcolumnas = PHPExcel_Cell::columnIndexFromString($maxcol);
                // Buscamos la fila donde empieza la lista de terminal
                $fila_perm = false;
                $fila_inicio = 0;
                while (($fila < $maxfilas) && (!$fila_perm)) {
                    if ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue() == "GSSI") {
                        $fila_inicio = $fila;
                        $fila_perm = true;
                    }
                    else{
                        $fila++;
                    }
                }
                $columna = 3;
                $col_max = 0;
                $carpetas = array();
                $col_perm = false;
                while (($columna < $maxcolumnas) && (!$col_perm)) {
                    $carpeta = trim($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue());
                    if ($carpeta != "") {
                        $carpetas[] = $carpeta;
                    }
                    else{
                        $col_perm = true;
                        $maxcolumnas = $columna;
                    }
                    $columna++;
                }
                $gissiperm = array();
                $i = 0;
                $ncarpetas = count($carpetas);
                $fin_grupos = false;
                $fila = $fila_inicio + 1;
                while (($fila  < $maxfilas) && (!$fin_grupos)){
                    $columna = 1;
                    if ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue() != ""){
                        $grupo['GISSI'] = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue();
                        $columna++;
                        $grupo['MNEMONICO'] = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue();
                        $columna++;
                        foreach ($carpetas as $carpeta) {
                            $permiso = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue();
                            $grupo[$carpeta] = $permiso;
                            $columna++;
                        }
                        $gissiperm[] = $grupo;
                    }
                    else{
                        $fin_grupos = true;
                        $maxfilas = $fila;
                    }
                    $fila++;
                }
                ?>
                <h2><?php echo $h2permisos;?></h2>
                <form name="accion_flota" action="update_permiso.php" method="post">
                    <input type="hidden" name="idflota" value="<?php echo $idflota;?>">
                    <input type="hidden" name="fila_inicio" value="<?php echo ($fila_inicio + 1);?>">
                    <input type="hidden" name="maxfila" value="<?php echo $maxfilas;?>">
                    <input type="hidden" name="maxcolumna" value="<?php echo $maxcolumnas;?>">
                    <input name="origen" type="hidden" value="impexcel">
                    <table>
                        <tr>
                            <td class="borde">
                                <input type="image" src="imagenes/guardar.png" title="<?php echo $botguardar;?>"> <br/>
                                <?php echo $botguardar;?>
                            </td>
                            <td class="borde">
                                <a href="#" onclick="document.detflota.submit();">
                                <img src='imagenes/atras.png' alt='<?php echo $botatras;?>' title="<?php echo $botatras;?>">
                                </a><br><?php echo $botatras;?>
                            </td>
                        </tr>
                    </table>
                </form>
                <?php
                if (count($gissiperm) > 0){
                ?>
                    <table>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <th colspan="<?php echo $ncarpetas;?>"><?php echo $thorg;?></th>
                        </tr>
                        <tr>
                            <th>GSSI</th>
                            <th><?php echo $thmnemo;?></th>
                            <?php
                            foreach ($carpetas as $carpeta) {
                                $sql_carpeta = "SELECT * FROM terminales WHERE FLOTA = '$idflota' AND CARPETA = '" . $carpeta ."'";
                                $res_carpeta = mysql_query($sql_carpeta) or die("Error en la consulta de Carpeta: " . mysql_error());
                                $ncarpeta = mysql_num_rows($res_carpeta);
                            ?>
                                <th><?php echo $carpeta . " (" . $ncarpeta . ")";?></th>
                            <?php
                            }
                            ?>
                        </tr>
                        <?php
                        $relleno = false;
                        foreach ($gissiperm as $grupo) {
                            $sql_gissi = "SELECT * FROM grupos_flotas WHERE FLOTA = '$idflota' AND GISSI = '" . $grupo['GISSI'] ."'";
                            $res_gissi = mysql_query($sql_gissi) or die("Error en la consulta de Carpeta: " . mysql_error());
                            $ngissi = mysql_num_rows($res_gissi);
                            if ($ngissi > 0){
                                $logo = "imagenes/okm.png";
                                $title = $grupoexist;
                            }
                            else{
                                $logo = "imagenes/nom.png";
                                $title = $gruponew;
                            }
                        ?>
                            <tr <?php if ($relleno){echo "class='filapar'";}?>>
                                <td class="centro">
                                    <?php echo $grupo['GISSI'];?> &mdash; <img src="<?php echo $logo;?>" alt="<?php echo $title;?>" title="<?php echo $title;?>" />
                                </td>
                                <td class="centro"><?php echo $grupo['MNEMONICO'];?></td>
                                <?php
                                foreach ($carpetas as $carpeta){
                                ?>
                                    <td class="centro"><?php echo $grupo[$carpeta];?></td>
                                <?php
                                }
                                ?>

                            </tr>
                        <?php
                            $relleno = !($relleno);
                        }
                        ?>
                    </table>
                <?php
                }
                else{
                ?>
                    <p class="error">Error: <b><?php echo $errnogissi;?></b></p>
            <?php
                }
            }
            else{
            ?>
                <p class="error"><?php echo $errnoflota;?></p>
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
