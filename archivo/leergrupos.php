<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/leergrupos_$idioma.php";
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
                $nomHoja = "(4) GSSI-TEL";

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
                $columna = 0;
                // Obtenemos el número de filas y columnas de la hoja:
                $maxfilas = $objPHPExcel->getActiveSheet()->getHighestRow();
                $maxcol = $objPHPExcel->getActiveSheet()->getHighestColumn();
                $maxcolumnas = PHPExcel_Cell::columnIndexFromString($maxcol);
                // Buscamos la fila donde empieza la lista de terminal
                $fila_grupos = false;
                $fila_inicio = 0;
                while (($fila < $maxfilas) && (!$fila_grupos)) {
                    if ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue() == "CARPETA 1") {
                        $fila_inicio = $fila;
                        $fila_grupos = true;
                    }
                    else{
                        $fila++;
                    }
                }
                $grupos = array();
                $i = 0;
                $ngrupos = 0;
                $nmaxgc = 0;
                $ncarpetas = 0;
                while ($columna < $maxcolumnas){
                    $leido = false;
                    $fila = $fila_inicio;
                    if ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue() != ""){
                        $i++;
                        $grupos[$i]['CARPETA'] = 'CARPETA ' . $i;
                        $fila++;
                        $grupos[$i]['NOMBRE'] = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue();
                        $fila++;
                        $fila++;
                        $listagissi = array();
                        $ngrupcarpeta = 0;
                        while ((!$leido) && ($fila < $maxfilas)){
                            $gissi = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue();
                            $mnemo = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(($columna + 1), $fila)->getValue();
                            if ($gissi == ""){
                                $leido = true;
                            }
                            else{
                                $arrygissi = array('GISSI' => $gissi, 'MNEMONICO' => $mnemo);
                                array_push($listagissi, $arrygissi);
                                $ngrupos++;
                                $ngrupcarpeta++;
                            }
                            $fila++;
                        }
                        if ($ngrupcarpeta > $nmaxgc){
                            $nmaxgc = $ngrupcarpeta;
                        }
                        $grupos[$i]['GISSI'] = $listagissi;
                        $ncarpetas++;
                    }
                    $columna = $columna + 2;
                }
                ?>
                <h2><?php echo $h2grupos;?> &mdash; <?php echo $ngrupos;?></h2>
                <form name="accion_flota" action="update_grupo.php" method="post">
                    <input type="hidden" name="idflota" value="<?php echo $idflota;?>">
                    <input type="hidden" name="fila_inicio" value="<?php echo $fila_inicio;?>">
                    <input type="hidden" name="maxfilas" value="<?php echo $maxfilas;?>">
                    <input type="hidden" name="maxcolumnas" value="<?php echo $maxcolumnas;?>">
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
                if ($ngrupos > 0){
                ?>
                    <table>
                        <tr>
                        <?php
                        foreach ($grupos as $grupo){
                        ?>
                            <th colspan="3"><?php echo $grupo['CARPETA'];?></th>
                        <?php
                        }
                        ?>
                        </tr>
                        <tr>
                        <?php
                        foreach ($grupos as $grupo){
                        ?>
                            <th colspan="3"><?php echo $grupo['NOMBRE'];?></th>
                        <?php
                        }
                        ?>
                        </tr>
                        <tr>
                        <?php
                        foreach ($grupos as $grupo){
                        ?>
                            <th><?php echo $thaccion;?></th>
                            <th>GSSI</th>
                            <th><?php echo $thmnemo;?></th>
                        <?php
                        }
                        ?>
                        </tr>
                        <?php
                        for ($i = 0; $i < $nmaxgc; $i++){
                        ?>
                            <tr <?php if(($i % 2) == 1){echo "class='filapar'";} ?>>
                                <?php
                                for ($j = 1; $j <= $ncarpetas; $j++){
                                    if ($i < count ($grupos[$j]['GISSI'])){
                                        $gissi = $grupos[$j]['GISSI'][$i]['GISSI'];
                                        //datos de la tabla grupos
                                        $sql_grupos = "SELECT * FROM grupos WHERE GISSI='$gissi'";
                                        $res_grupos = mysql_query($sql_grupos) or die("Error en la consulta de Grupos: " . mysql_error());
                                        $ngrupos = mysql_num_rows($res_grupos);
                                        if ($ngrupos > 0){
                                            $imagen = "imagenes/update.png";
                                            $texto = $grupoexist;
                                        }
                                        else{
                                            $imagen = "imagenes/nueva.png";
                                            $texto = $gruponew;
                                        }

                                ?>
                                        <td class="centro">
                                            <img src="<?php echo $imagen; ?>" alt="<?php echo $texto; ?>" title="<?php echo $texto; ?>">
                                        </td>
                                        <td><?php echo $gissi; ?></td>
                                        <td><?php echo $grupos[$j]['GISSI'][$i]['MNEMONICO']; ?></td>
                                    <?php
                                    }
                                    else{
                                    ?>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                <?php
                                    }
                                }
                                ?>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                <?php
                }
                else{
                ?>
                    <p class="error">Error: <b><?php echo $errnogrupos;?></b></p>
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
