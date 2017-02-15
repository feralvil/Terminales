<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/leerterm_$idioma.php";
include ($lang);

// ------------ Conexión a BBDD de Terminales ----------------------------------------- //
include("conexion.php");
$base_datos = $dbbdatos;
$link = mysql_connect($dbserv, $dbusu, $dbpaso);
if (!link) {
    echo "<b>ERROR MySQL:</b>" . mysql_error();
} else {
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
        <script type="text/javascript">
            function checkAll() {
                var nodoCheck = document.getElementsByTagName("input");
                var varCheck = document.getElementById("seltodo").checked;
                for (i = 0; i < nodoCheck.length; i++) {
                    if (nodoCheck[i].type == "checkbox" && nodoCheck[i].name != "seltodo" && nodoCheck[i].disabled == false) {
                        nodoCheck[i].checked = varCheck;
                    }
                }
            }
        </script>
        <?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($flota_usu == 0) {
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
            //datos de la tabla Flotas
            $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
            $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
            $nflota = mysql_num_rows($res_flota);
            if ($nflota == 0) {
                ?>
                <p class='error'><?php echo $errnoflota; ?></p>
                <?php
            } else {
                $row_flota = mysql_fetch_array($res_flota);
                $usuario = $row_flota["LOGIN"];
                $sql_term = "SELECT * FROM terminales WHERE FLOTA = '$idflota'";
                $res_term = mysql_query($sql_term) or die("Error en la consulta de Terminales: " . mysql_error());
                $nterm = mysql_num_rows($res_term);
            }

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
            $nomHoja = "(3) ISSI";

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
        ?>
            <form name="detalleflota" method="POST" action="leerterm.php" target="_blank">
                <input type="hidden" name="idflota" value="<?php echo $idflota; ?>" />
                <h1>
                    Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>) &mdash;
                    <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $newtab; ?>' title="<?php echo $newtab; ?>">
                </h1>
            </form>

            <h2><?php echo $h2admin; ?></h2>
            <table>
                <tr>
                    <th class="t10c">ID</th>
                    <th class="t2c"><?php echo $nomflota; ?></th>
                    <th class="t5c"><?php echo $acroflota; ?></th>
                    <th class="t5c"><?php echo $usuflota; ?></th>
                </tr>
                <tr>
                    <td><?php echo $row_flota["ID"]; ?></td>
                    <td><?php echo $row_flota["FLOTA"]; ?></td>
                    <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                    <td><?php echo $row_flota["LOGIN"]; ?></td>
                </tr>
            </table>
            <h2><?php echo $h2term; ?></h2>
            <form name="updterm" action="update_termexcel.php" method="POST">
                <table>
                    <tr>
    <?php
                $termexcel = array();
                if ($nterm > 0) {
    ?>
                            <th>
                                <input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();" />
                                &mdash; Seleccionar
                            </th>
    <?php
                }
                for ($i = 0; $i < count($campos); $i++) {
    ?>
                    <th><?php echo $campos[$i]; ?></th>
    <?php
                }
    ?>
                    </tr>
    <?php
                    // Obtenemos el número de filas de la hoja:
                    $numfilas = $objPHPExcel->getActiveSheet()->getHighestRow();
                    $fila = 2;
                    $par = 0;

                    // Buscamos la fila donde empieza la lista de terminal
                    $term = false;
                    $i = $fila;
                    while (($i < $numfilas) && (!$term)) {
                        $celda = "B$i";
                        if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "TERMINAL") {
                            $term = true;
                        }
                        $i++;
                    }
                    $filaterm = $fila = $i + 1;
                    while ($objPHPExcel->getActiveSheet()->getCell("H" . $fila)->getValue() != "") {
                        $issi = $objPHPExcel->getActiveSheet()->getCell("H" . $fila)->getValue();
                        // Comprobamos si el ISSI está registrado en la BBDD:
                        $sql_issi = "SELECT * FROM terminales WHERE ISSI = '$issi' AND FLOTA = '$idflota'";
                        $res_issi = mysql_query($sql_issi) or die($errissi . " $issi: " . mysql_error());
                        $nissi = mysql_num_rows($res_issi);
                        if ($nissi > 0) {
                            $estado = "imagenes/update.png";
                            $alt = $updterm;
                        }
                        else {
                            $estado = "imagenes/nueva.png";
                            $alt = $nuevo;
                        }
                        array_push($termexcel, $issi);
                        $dup = $objPHPExcel->getActiveSheet()->getCell("M" . $fila)->getValue();
                        $sem = $objPHPExcel->getActiveSheet()->getCell("N" . $fila)->getValue();
                        $dup = strtoupper($dup);
                        if (($dup != "SI") && ($dup != "NO")) {
                            if (($dup == "S") || ($dup == "SÍ") || ($dup == "X")) {
                                $dup = "SI";
                            }
                            elseif (($dup == "") || ($dup == "N")) {
                                $dup = "NO";
                            }
                        }
                        $sem = strtoupper($sem);
                        if (($sem != "SI") && ($sem != "NO")) {
                            if (($sem == "S") || ($sem == "SÍ") || ($sem == "X")) {
                                $sem = "SI";
                            } 
                            elseif (($sem == "") || ($sem == "N")) {
                                $sem = "NO";
                            }
                        }
    ?>
                        <tr <?php if (($par % 2) == 1) echo "class='filapar'"; ?>>
    <?php
                        if ($nterm > 0) {
    ?>
                            <td class="centro">
                                <input type="checkbox" name="idfila[]" value="<?php echo $fila; ?>" /> &mdash;
                                <img src="<?php echo $estado; ?>" alt="<?php echo $alt; ?>" title="<?php echo $alt; ?>" />
                            </td>
    <?php
                        }
    ?>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("H" . $fila)->getValue(); ?></td>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("I" . $fila)->getValue(); ?></td>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("D" . $fila)->getValue(); ?></td>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("B" . $fila)->getValue(); ?></td>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("C" . $fila)->getValue(); ?></td>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("E" . $fila)->getValue(); ?></td>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("O" . $fila)->getValue(); ?></td>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("G" . $fila)->getValue(); ?></td>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("P" . $fila)->getValue(); ?></td>
                        <td><?php echo $objPHPExcel->getActiveSheet()->getCell("K" . $fila)->getValue(); ?></td>
                        <td><?php echo $dup; ?></td>
                        <td><?php echo $sem; ?></td>
                    </tr>
    <?php
                    $fila++;
                    $par++;
                }
                $tot_term = $fila - $filaterm;
    ?>
                </table>

                <table>
                    <tr>
                    <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                    <input name="origen" type="hidden" value="<?php echo $origen; ?>">
                    <input name="filaterm" type="hidden" value="<?php echo $filaterm; ?>">
                    <input name="nterm" type="hidden" value="<?php echo $tot_term; ?>">
                    <td class="borde">
                        <input type='image' name='nueva' src='imagenes/guardar.png' alt='Guardar' title="Guardar"><br>Guardar <?php echo $terminales; ?>
                    </td>
                    <td class="borde">
                        <a href="#" onclick="document.detflota.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $detalle; ?>' title="<?php echo $detalle; ?>">
                        </a><br><?php echo $detalle . " de Flota"; ?>
                    </td>
                    </tr>
                </table>
            </form>
            <form name="detflota" action="update_flota.php" method="POST">
                <input name="origen" type="hidden" value="leerexcel">
                <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
            </form>
    <?php
} else {
    ?>
            <h1><?php echo $h1perm ?></h1>
            <p class='error'><?php echo $permno ?></p>
    <?php
}
?>
    </body>
</html>