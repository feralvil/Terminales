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
$lang = "idioma/leercont_$idioma.php";
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
?>
        <p class='error'><?php echo $errnoflota;?></p>
<?php
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        $usuario = $row_flota["LOGIN"];
    }
    //datos de la tabla Municipio
    // INE
    $ine = $row_flota["INE"];
    $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
    $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
    $nmun = mysql_num_rows($res_mun);
    if ($nmun == 0) {
?>
        <p class='error'><?php echo $errnomun;?></p>
<?php
    }
    else {
        $row_mun = mysql_fetch_array($res_mun);
    }

    // Clases para generar el Excel
        /** Error reporting */
    //error_reporting(E_ALL);
    date_default_timezone_set('Europe/Madrid');
    /** PHPExcel */
    require_once 'Classes/PHPExcel.php';
    $fichero = "flotas/$idflota.xls";

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
    // Fijamos como hoja activa la primera
    $objPHPExcel->setActiveSheetIndex(0);
    
    // Obtenemos el número de filas de la hoja:
    $numfilas = $objPHPExcel->getActiveSheet()->getHighestRow();
    $fila = 2;

    // Buscamos la fila donde empieza el responsable de la
    $resp = false;
    $i = $fila;
    while (($i < $numfilas)&&(!$resp)){
        $celda = "A$i";
        if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "DATOS RESPONSABLE ORGANIZACIÓN:"){
            $resp = true;
        }
        $i++;
    }
    $filaresp = $fila = $i;

    $responsable = array();
    $celda = "C$fila";
    $responsable ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell($celda)->getValue();
    $celda = "J$fila";
    $responsable ["NIF"] = $objPHPExcel->getActiveSheet()->getCell($celda)->getValue();
    $fila++;
    $celda = "C$fila";
    $responsable ["CARGO"] = $objPHPExcel->getActiveSheet()->getCell($celda)->getValue();
    $fila++;
    $celda = "C$fila";
    $responsable ["DOMICILIO"] = $objPHPExcel->getActiveSheet()->getCell($celda)->getValue();
    $fila++;
    //Saltamos la fila del municipio
    $fila++;
    $celda = "C$fila";
    $responsable ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell($celda)->getValue();
    $celda = "I$fila";
    $responsable ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell($celda)->getValue();
    $fila++;
    $respstring = serialize($responsable);

    // Buscamos la fila donde empiezan los contactos
    $cont = false;
    $i = $fila;
    while (($i < $numfilas)&&(!$cont)){
        $celda = "A$i";
        if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "PERSONAS CONTACTO:"){
            $cont = true;
        }
        $i++;
    }
    $filacont = $filaini = $i;
    $contactos = array("","","");
    for ($j = 0; $j < count($contactos); $j++){
        $fila = $filaini + 3*$j;
        if ($objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue() != ""){
            $contactos[$j] = array(
                "NOMBRE" => $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue(),
                "CARGO" => $objPHPExcel->getActiveSheet()->getCell("J$fila")->getValue(),
                "TELEFONO" => $objPHPExcel->getActiveSheet()->getCell("C".($fila+1))->getValue(),
                "MOVIL" => $objPHPExcel->getActiveSheet()->getCell("F".($fila+1))->getValue(),
                "MAIL" => $objPHPExcel->getActiveSheet()->getCell("K".($fila+1))->getValue()
            );
        }
    }

    // Buscamos la fila donde empiezan los contactos
    $inc = false;
    $i = $fila;
    while (($i < $numfilas)&&(!$inc)){
        $celda = "A$i";
        if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "PERSONAS CONTACTO INCIDENCIAS:"){
            $inc = true;
        }
        $i++;
    }
    $filainc = $filaini = $i + 1;
    $continc = array("","","","");
    for ($j = 0; $j < count($continc); $j++){
        $fila = $filaini + $j;
        if ($objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue() != ""){
            $continc[$j] = array(
                "NOMBRE" => $objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue(),
                "CARGO" => $objPHPExcel->getActiveSheet()->getCell("D$fila")->getValue(),
                "TELEFONO" => $objPHPExcel->getActiveSheet()->getCell("G$fila")->getValue(),
                "MOVIL" => $objPHPExcel->getActiveSheet()->getCell("I$fila")->getValue(),
                "HORARIO" => $objPHPExcel->getActiveSheet()->getCell("L$fila")->getValue()
            );
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
        <h2><?php echo $h2localiza; ?></h2>
        <table>
            <tr>
                <th class="t40p"><?php echo $ciudad; ?></th>
                <th class="t10c"><?php echo $cp; ?></th>
                <th class="t40p"><?php echo $provincia; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_mun["MUNICIPIO"]; ?></td>
                <td><?php echo $row_flota["CP"]; ?></td>
                <td><?php echo $row_mun["PROVINCIA"]; ?></td>
            </tr>
        </table>
        <h2>Responsable de la Flota</h2>
        <table>
            <tr>
                <th class="t5c"><?php echo $nomflota; ?></th>
                <td class="t40p"><?php echo $responsable["NOMBRE"]; ?></td>
                <th class="t5c">NIF</th>
                <td class="t5c"><?php echo $responsable["NIF"]; ?></td>
            </tr>
            <tr>
                <th class="t5c"><?php echo $cargo; ?></th>
                <td colspan="3"><?php echo $responsable["CARGO"]; ?></td>
            </tr>
            <tr>
                <th class="t5c"><?php echo $direccion; ?></th>
                <td colspan="3"><?php echo $responsable["DOMICILIO"]; ?></td>
            </tr>
            <tr>
                <th class="t5c"><?php echo $telefono; ?></th>
                <td class="t40p"><?php echo $responsable["TELEFONO"]; ?></td>
                <th class="t5c"><?php echo $mail; ?></th>
                <td class="t5c"><?php echo $responsable["MAIL"]; ?></td>
            </tr>
        </table>
        <h2><?php echo $h3flota; ?></h2>
        <table>
            <tr>
                <td>&nbsp;</td>
                <th><?php echo $nomflota; ?></th>
                <th><?php echo $cargo; ?></th>
                <th><?php echo $telefono; ?></th>
                <th><?php echo $movil; ?></th>
                <th><?php echo $mail; ?></th>
            </tr>
<?php
        $par = 0;
        for ($i = 0; $i < count($contactos); $i++) {
            if ($contactos[$i] != "") {
?>
                <tr <?php if (($par % 2) == 1) echo "class='filapar'"; ?>>
                    <th><?php echo $contacto . " " . ($i + 1); ?></th>
                    <td><?php echo $contactos[$i]["NOMBRE"]; ?></td>
                    <td><?php echo $contactos[$i]["CARGO"]; ?></td>
                    <td><?php echo $contactos[$i]["TELEFONO"]; ?></td>
                    <td><?php echo $contactos[$i]["MOVIL"]; ?></td>
                    <td><?php echo $contactos[$i]["MAIL"]; ?></td>
                </tr>
<?php
                $par++;
            }
        }
?>
        </table>
        <h3><?php echo $h3incid; ?></h3>

        <table>
            <tr>
                <td>&nbsp;</td>
                <th><?php echo $nomflota; ?></th>
                <th><?php echo $cargo; ?></th>
                <th><?php echo $telefono; ?></th>
                <th><?php echo $movil; ?></th>
                <th><?php echo $horario; ?></th>
            </tr>
<?php
            $par = 0;
            for ($i = 0; $i < count($continc); $i++) {
            if ($continc[$i] != "") {
?>
                <tr <?php if (($par % 2) == 1) echo "class='filapar'"; ?>>
                    <th><?php echo $contacto . " " . ($i + 1); ?></th>
                    <td><?php echo $continc[$i]["NOMBRE"]; ?></td>
                    <td><?php echo $continc[$i]["CARGO"]; ?></td>
                    <td><?php echo $continc[$i]["TELEFONO"]; ?></td>
                    <td><?php echo $continc[$i]["MOVIL"]; ?></td>
                    <td><?php echo $continc[$i]["HORARIO"]; ?></td>
                </tr>
<?php
                $par++;
            }
        }
?>
        </table>
        <table>
            <tr>
            <form name="updcont" action="update_contexcel.php" method="POST">
                <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                <input name="filaresp" type="hidden" value="<?php echo $filaresp; ?>">
                <input name="filacont" type="hidden" value="<?php echo $filacont; ?>">
                <input name="filainc" type="hidden" value="<?php echo $filainc; ?>">
                <td class="borde">
                    <input type='image' name='nueva' src='imagenes/guardar.png' alt='Guardar' title="Guardar"><br>Guardar <?php echo $contacto . "s"; ?>
                </td>
            </form>
            <form name="detflota" action="leercont.php" method="POST">
                <input name="origen" type="hidden" value="leerexcel">
                <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
                <td class="borde">
                    <input type='image' name='nueva' src='imagenes/atras.png' alt='<?php echo $detalle; ?>' title="<?php echo $detalle; ?>"><br><?php echo $detalle . " de Flota"; ?>
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