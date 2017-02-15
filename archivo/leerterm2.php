<?php
// Revisado 2011-07-12
// ------------ Obtención del usuario Joomla! --------------------------------------- //
// Le decimos que estamos en Joomla
define('_JEXEC', 1);

// Definimos la constante de directorio actual y el separador de directorios (windows server: \ y linux server: /)
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__) . DS . '..');

// Cargamos los ficheros de framework de Joomla 1.5, y las definiciones de constantes (IMPORTANTE AMBAS LÍNEAS)
require_once ( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );
require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );

// Iniciamos nuestra aplicación (site: frontend)
$mainframe = & JFactory::getApplication('site');

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/leerterm_$idioma.php";
include ($lang);

// Obtenemos los parámetros de Joomla
$user = & JFactory::getUser();
$usu = $user->username;
// ------------------------------------------------------------------------------------- //
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

import_request_variables("gp", "");

/* Determinamos si es usuario OFICINA COMDES para ver la gestión de flotas */
$sql_oficina = "SELECT ID FROM flotas WHERE LOGIN='$usu'";
$res_oficina = mysql_query($sql_oficina);
$row_oficina = mysql_fetch_array($res_oficina);
$flota_usu = $row_oficina["ID"];
/*
 *  $permiso = variable de permisos de flota:
 *      0: Sin permiso
 *      1: Permiso de consulta
 *      2: Permiso de modificación
 */
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
        if ($usu == ""){
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
    if ($permiso != 0) {
        //datos de la tabla Flotas
        $sql_flota = "SELECT * FROM flotas WHERE ID='$idflota'";
        $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
        $nflota = mysql_num_rows($res_flota);
        if ($nflota == 0) {
            echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
        }
        else {
            $row_flota = mysql_fetch_array($res_flota);
            $usuario = $row_flota["LOGIN"];
        }

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
        // Sólo nos interesa cargar los datos:
        $objReader->setReadDataOnly(true);
        try {
         $objPHPExcel = $objReader->load($fichero);
        }
        catch(Exception $e){
            die("Error al cargar el fichero de datos: ".$e->getMessage());
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
        <h1>Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
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
        <h2><?php echo $h2term; ?></h2>
        <table>
            <tr>
<?php
                for ($i = 0; $i < count($campos); $i++){
?>
                    <th><?php echo $campos[$i];?></th>
<?php
                }
?>
            </tr>
<?php
            // Fijamos como hoja activa la aegunda
            $objPHPExcel->setActiveSheetIndex(1);

            // Obtenemos el número de filas de la hoja:
            $numfilas = $objPHPExcel->getActiveSheet()->getHighestRow();
            $fila = 2;
            $par = 0;

            // Buscamos la fila donde empieza la lista de terminal
            $term = false;
            $i = $fila;
            while (($i < $numfilas)&&(!$term)){
                $celda = "B$i";
                if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "TERMINAL"){
                    $term = true;
                }
                $i++;
            }
            $filaterm = $fila = $i + 1;
            while ($objPHPExcel->getActiveSheet()->getCell("H".$fila)->getValue()!= "") {
                $dup = $objPHPExcel->getActiveSheet()->getCell("M".$fila)->getValue();
                $sem = $objPHPExcel->getActiveSheet()->getCell("N".$fila)->getValue();
                if ($dup == "SI"){
                    $llam = "D";
                    if ($sem == "SI"){
                        $llam .= "+ S";
                    }
                }
                else {
                    if ($sem == "SI"){
                        $llam = "S";
                    }
                    else {
                        $llam = "N";
                    }
                }
?>
                <tr <?php if (($par % 2) == 1) echo "class='filapar'";?>>
                    <td><?php echo $objPHPExcel->getActiveSheet()->getCell("H".$fila)->getValue(); ?></td>
                    <td><?php echo $objPHPExcel->getActiveSheet()->getCell("I".$fila)->getValue(); ?></td>
                    <td><?php echo $objPHPExcel->getActiveSheet()->getCell("D".$fila)->getValue(); ?></td>
                    <td><?php echo $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue(); ?></td>
                    <td><?php echo $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue(); ?></td>
                    <td><?php echo $objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue(); ?></td>
                    <td><?php echo $objPHPExcel->getActiveSheet()->getCell("Q".$fila)->getValue(); ?></td>
                    <td><?php echo $objPHPExcel->getActiveSheet()->getCell("G".$fila)->getValue(); ?></td>
                    <td><?php echo $objPHPExcel->getActiveSheet()->getCell("K".$fila)->getValue(); ?></td>
                    <td><?php echo $llam; ?></td>
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
            <form name="updterm" action="update_termexcel.php" method="POST">
                <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
                <input name="filaterm" type="hidden" value="<?php echo $filaterm; ?>">
                <td class="borde">
                    <input type='image' name='nueva' src='imagenes/guardar.png' alt='Guardar' title="Guardar"><br>Guardar <?php echo "$tot_term $terminales";?>
                </td>
            </form>
            <form name="detflota" action="leerterm.php" method="POST">
                <input name="origen" type="hidden" value="leerexcel">
                <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
                <td class="borde">
                    <input type='image' name='nueva' src='imagenes/atras.png' alt='<?php echo $detalle;?>' title="<?php echo $detalle;?>"><br><?php echo $detalle." de Flota";?>
                </td>
            </form>
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