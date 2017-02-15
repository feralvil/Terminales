<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/leerteik_$idioma.php";
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

// Clases para generar el Excel
// /** Error reporting */
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
/** PHPExcel */
require_once 'Classes/PHPExcel.php';
$fichero = "flotas/$idflota.xls";
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
                for (i=0; i<nodoCheck.length; i++){
                    if (nodoCheck[i].type == "checkbox" && nodoCheck[i].name != "seltodo" && nodoCheck[i].disabled == false) {
                        nodoCheck[i].checked = varCheck;
                    }
                }
            }
        </script>
<?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($usu == ""){
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
            echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
        }
        else {
            $row_flota = mysql_fetch_array($res_flota);
            $sql_term = "SELECT * FROM terminales WHERE FLOTA = '$idflota'";
            $res_term = mysql_query($sql_term, $link) or die ($errterm. mysql_error());
            $nterm = mysql_num_rows($res_term);
        }


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
        <form name="termteik" action="#" method="POST">
            <input name="origen" type="hidden" value="leerexcel">
            <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
        <table>
            <tr>
                <th>
                    <input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();" />
                </th>
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
            $errores = $errfaltan = 0;
            $issierr = "";
            while ($objPHPExcel->getActiveSheet()->getCell("H".$fila)->getValue()!= "") {
                $issi = $objPHPExcel->getActiveSheet()->getCell("H".$fila)->getValue();
                $tei = $objPHPExcel->getActiveSheet()->getCell("I".$fila)->getValue();
                $numk = $objPHPExcel->getActiveSheet()->getCell("R".$fila)->getValue();
                $sql_issi = "SELECT * FROM terminales WHERE ISSI = '$issi' AND FLOTA = '$idflota'";
                $res_issi = mysql_query($sql_issi, $link) or die ($errissi."$issi: ". mysql_error());
                $nissi = mysql_num_rows($res_issi);
                if ($nissi != 1){
                    $imagen = "imagenes/nom.png";
                    $alt = "No";
                    if ($errores > 0){
                        $issierr .= ", ";
                    }
                    $issierr .="$issi";
                    $errores++;
                    $idterm = "-";
                }
                else{
                    $imagen = "imagenes/okm.png";
                    $alt = "OK";
                    $row_issi = mysql_fetch_array($res_issi);
                    $idterm = $row_issi["ID"];
                }
?>
                <tr <?php if (($par % 2) == 1) echo "class='filapar'";?>>
                    <td class="centro">
                        <input type="checkbox" name="issisel[]" value="<?php echo $issi;?>" />
                    </td>
                    <td class="centro">
                        <img src="<?php echo $imagen; ?>" alt="<?php echo $ok; ?>" title="<?php echo $ok; ?>">
                    </td>
                    <td><?php echo $idterm; ?></td>
                    <td><?php echo $issi; ?></td>
                    <td><?php echo $tei; ?></td>
                    <td><?php echo $numk; ?></td>
                </tr>
<?php
                $fila++;
                $par++;
            }
            $nexc = $fila - $filaterm;
?>
        </table>
        <table>
            <tr>
<?php
            $errfaltan = $nterm - $nexc;
            if (($errores > 0)||($errfaltan > 0)){
                if ($errores > 0){
                    $errormens = "$txterrores $errores $txterrores2. <br />$txterrores3 $issierr ";
                }
                else {
                    $errormens = "$errbbdd $errfaltan $errbbdd2";
                }
?>
                <td class="borde">
                    <a href="#" onclick="document.termteik.action='update_flota.php';document.termteik.submit()">
                        <img src="imagenes/warning.png" alt="<?php echo $atencion;?>" title="<?php echo $atencion;?>">
                    </a><br><?php echo $errormens;?>
                </td>
<?php
            }
            else {
?>
                <td class="borde">
                    <a href="#" onclick="document.termteik.action='update_teikexcel.php';document.termteik.submit()">
                        <img src="imagenes/guardar.png" alt="Guardar" title="Guardar">
                    </a><br><?php echo "$actualiza $nterm $terminales";?>
                </td>
                <td class="borde">
                    <a href="#" onclick="document.termteik.action='update_flota.php';document.termteik.submit()">
                        <img src="imagenes/atras.png" alt="<?php echo $detalle;?>" title="<?php echo $detalle;?>">
                    </a><br><?php echo $detalle;?> de Flota
                </td>
<?php
            }
?>
            </tr>
        </table>
            <input name="filaterm" type="hidden" value="<?php echo $filaterm;?>">
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