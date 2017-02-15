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
        $sql_flota = "SELECT * from FLOTAS WHERE ID = '$idflota'";
        $res_flota = mysql_query($sql_flota) or die("Error en la consulta de flota: ".mysql_error());
        $nflota = mysql_num_rows($res_flota);
        if ($nflota == 0){
            $res_update = false;
            $error = $error_noflota;
        }
        else{
            // Clases para generar el Excel
            // Aumentamos el tamaño de la memoria:
            ini_set('memory_limit', '64M');
            set_time_limit(120);
            /** PHPExcel */
            require_once 'Classes/PHPExcel.php';
            $fichero = "flotas/$idflota.xls";
            /** Error reporting */
            error_reporting(E_ALL);
            date_default_timezone_set('Europe/Madrid');

            // Creamos el objeto PHPExcel
            $objPHPExcel = new PHPExcel();
            //Cargamos el fichero de datos
            $tipoFich = PHPExcel_IOFactory::identify($fichero);
            $objReader = PHPExcel_IOFactory::createReader($tipoFich);
            // Leemos sólo la hoja de los ISSI:
            //$objReader->setLoadSheetsOnly('(3) ISSI');
            // Sólo nos interesa cargar los datos:
            $objReader->setReadDataOnly(true);
            try {
                $objPHPExcel = $objReader->load($fichero);
            }
            catch(Exception $e){
                die("Error al cargar el fichero de datos: ".$e->getMessage());
            }

            // Leemos los datos de la hoja de Terminales:
            $nomHoja = "(2) ISSI";
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
            // Obtenemos el número de filas de la hoja:
            $numfilas = $objPHPExcel->getActiveSheet()->getHighestRow();

            $row_flota = mysql_fetch_array($res_flota);
            $flota = $row_flota["FLOTA"];
            //Leemos los datos de la hoja:
            $terminado = false;
            $res_update = true;
            $sql_termcamp = "INSERT INTO terminales (MARCA, MODELO, TIPO, PROVEEDOR, CODIGOHW, AM, AUTENTICADO, ";
            $sql_termcamp .= "ENCRIPTADO, ISSI, VERSION, TEI, NSERIE, MNEMONICO, CARPETA, DUPLEX, SEMID, ";
            $sql_termcamp .= "OBSERVACIONES, FALTA, DOTS, NUMEROK, DIRIP, FLOTA, ESTADO, FBAJA) VALUES ";
            $ninsert = $nupdate = $ntins = 0;
            $termexcel = array();
            // Arrays pasa pasar los terminales que se actualizan e insertan:
            $termupd = array();
            $termins = array();
            for ($i = 0; $i < $nterm; $i++){
                $fila = $filaterm + $i;
                if ($ntins == 0){
                    $sql_term = $sql_termcamp;
                }
                $selec = true;
                if (isset($idfila)) {
                    $selec = in_array($fila, $idfila);
                }
                if ($selec){
                    $issi = $objPHPExcel->getActiveSheet()->getCell("H".$fila)->getValue();
                    array_push($termexcel, $issi);
                    // Si el terminal está seleccionado, cargamos los datos de la hoja
                    $marca = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue();
                    $modelo = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue();
                    $tipo = $objPHPExcel->getActiveSheet()->getCell("D".$fila)->getValue();
                    $prov = $objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue();
                    $codhw = $objPHPExcel->getActiveSheet()->getCell("F".$fila)->getValue();
                    $am  = $objPHPExcel->getActiveSheet()->getCell("G".$fila)->getValue();
                    $am = strtoupper($am);
                    if (($am != "SI")&&($am != "NO")){
                        if (($am == "S")||($am == "SÍ")||($am == "X")){
                            $am = "SI";
                        }
                        elseif (($am == "")||($am == "N")){
                            $am = "NO";
                        }
                    }
                    $tei = $objPHPExcel->getActiveSheet()->getCell("I".$fila)->getValue();
                    if (strlen($tei) < 15){
                        $numceros = 15 - strlen($tei);
                        for ($j = 0; $j < $numceros; $j++){
                            $tei = '0' . $tei;
                        }
                    }
                    if (strlen($tei) > 15){
                        $inicio = strlen($tei) - 15;
                        $tei = substr($tei, $inicio, 15);
                    }
                    $nserie = $objPHPExcel->getActiveSheet()->getCell("J".$fila)->getValue();
                    $mnemo = $objPHPExcel->getActiveSheet()->getCell("K".$fila)->getValue();
                    $carp = $objPHPExcel->getActiveSheet()->getCell("L".$fila)->getValue();
                    $duplex = $objPHPExcel->getActiveSheet()->getCell("M".$fila)->getValue();
                    $duplex = strtoupper($duplex);
                    if (($duplex != "SI")&&($duplex != "NO")){
                        if (($duplex == "S")||($duplex == "SÍ")||($duplex == "X")){
                            $duplex = "SI";
                        }
                        elseif (($duplex == "")||($duplex == "N")){
                            $duplex = "NO";
                        }
                    }
                    $semid = $objPHPExcel->getActiveSheet()->getCell("N".$fila)->getValue();
                    $semid = strtoupper($semid);
                    if (($semid != "SI")&&($semid != "NO")){
                        if (($semid == "S")||($semid == "SÍ")||($duplex == "X")){
                            $semid = "SI";
                        }
                        elseif (($duplex == "")||($duplex == "N")){
                            $duplex = "NO";
                        }
                    }
                    $dots = $objPHPExcel->getActiveSheet()->getCell("O".$fila)->getValue();
                    $dots = strtoupper($dots);
                    if (($dots != "SI")&&($dots != "NO")){
                        if (($dots == "S")||($dots == "SÍ")||($dots == "X")){
                            $dots = "SI";
                        }
                        elseif (($dots == "")||($dots == "N")){
                            $dots = "NO";
                        }
                    }
                    $aut = $objPHPExcel->getActiveSheet()->getCell("P".$fila)->getValue();
                    $aut = strtoupper($aut);
                    if (($aut != "SI")&&($aut != "NO")){
                        if (($aut == "S")||($aut == "SÍ")||($aut == "X")){
                            $aut = "SI";
                        }
                        elseif (($aut == "")||($aut == "N")){
                            $aut = "NO";
                        }
                    }
                    $enc = $objPHPExcel->getActiveSheet()->getCell("Q".$fila)->getValue();
                    $enc = strtoupper($enc);
                    if (($enc != "SI")&&($enc != "NO")){
                        if (($enc == "S")||($enc == "SÍ")||($enc == "X")){
                            $enc = "SI";
                        }
                        elseif (($enc == "")||($enc == "N")){
                            $enc = "NO";
                        }
                    }
                    $dirip = $objPHPExcel->getActiveSheet()->getCell("R".$fila)->getValue();
                    $dirip = str_replace(',', '.', $dirip);
                    $version = $objPHPExcel->getActiveSheet()->getCell("S".$fila)->getValue();
                    $falta = $objPHPExcel->getActiveSheet()->getCell("T".$fila)->getValue();
                    $falta = substr($falta,6,4)."-".substr($falta,3,2)."-".substr($falta,0,2);
                    $observ = $objPHPExcel->getActiveSheet()->getCell("U".$fila)->getValue();
                    $numk = $objPHPExcel->getActiveSheet()->getCell("V".$fila)->getValue();
                    $estado = "A";
                    $fbaja = "0000-00-00";

                    // Comprobamos si el ISSI está registrado en la BBDD:
                    $sql_issi = "SELECT * FROM terminales WHERE ISSI = '$issi'";
                    $res_issi = mysql_query($sql_issi) or die ($errissi." $issi: ".mysql_error());
                    $nissi = mysql_num_rows($res_issi);
                    if ($nissi > 0){
                        // Si existe, comprobamos que pertenece a la flota correcta:
                        $row_issi = mysql_fetch_array($res_issi);
                        $floissi = $row_issi["FLOTA"];
                        $idterm = $row_issi["ID"];
                        if ($floissi != $idflota){
                            // Si no pertenece a la flota correcta, damos error
                            $terminado = true;
                            $res_update = false;
                            $error = "$errissirep $issi $errissirep2 '$idterm'";
                            $sql_floissi = "SELECT * from FLOTAS WHERE ID = '$floissi'";
                            $res_floissi = mysql_query($sql_floissi) or die ($errflota." ".mysql_error());
                            $nfloissi = mysql_num_rows($res_floissi);
                            if ($nfloissi >0){
                              $row_floissi = mysql_fetch_array($res_floissi);
                              $flotaissi = $row_floissi["FLOTA"];
                              $error .= " en la flota $flotaissi";
                          }
                        }
                        else{
                            // Si pertenece a la flota adecuada, se actualiza el terminal
                            $sql_act = "UPDATE terminales set MARCA = '$marca', MODELO = '$modelo', TIPO = '$tipo', ";
                            $sql_act .= "PROVEEDOR = '$prov', CODIGOHW = '$codhw', AM = '$am', ISSI = '$issi', VERSION = '$version', ";
                            $sql_act .= "TEI = '$tei', NSERIE = '$nserie', MNEMONICO = '$mnemo', CARPETA = '$carp', ";
                            $sql_act .= "DUPLEX = '$duplex', SEMID = '$semid', OBSERVACIONES = '$observ', ";
                            $sql_act .= "AUTENTICADO = '$aut', ENCRIPTADO = '$enc', DIRIP = '$dirip', ";
                            $sql_act .= "FALTA = '$falta', DOTS = '$dots', NUMEROK = '$numk', FLOTA = '$idflota', ";
                            $sql_act .= "ESTADO = '$estado', FBAJA = '$fbaja' WHERE ID = $idterm";
                            $res_act = mysql_query($sql_act) or die($errupd." Terminal ISSI = $issi / ID = $idterm: ".mysql_error());
                            $nupdate++;
                            $termupd[] = $idterm;
                        }
                    }
                    else{
                        // Si no existe el ISSI, lo añadimos a la consulta de inserción;
                        $ntins++;
                        $sql_val = "('$marca', '$modelo', '$tipo', '$prov', '$codhw', '$am', '$aut', '$enc', ";
                        $sql_val .= "'$issi', '$version', '$tei', '$nserie', '$mnemo', '$carp', '$duplex', '$semid', ";
                        $sql_val .= "'$observ', '$falta', '$dots', '$numk', '$dirip', '$idflota', '$estado', '$fbaja')";
                        $sql_term .= $sql_val;
                        $termins[] = $issi;
                        // Comprobamos si hay 20 terminales para insertar.
                        if ($ntins == 20){
                            // Si los hay, concluimos, ejecutamos la consulta e iniciamos una nueva consulta:
                            $sql_term .= ";";
                            $res_update = mysql_query($sql_term) or die($errorimp.": SQL = $sql_term - ".mysql_error());
                            $ninsert = $ninsert + $ntins;
                            $ntins = 0;
                        }
                        else {
                            // Si no los hay, continuamos la consulta
                            $sql_term .= ", ";
                        }
                    }
                }
            }

            // Comprobamos si quedan terminales para insertar:
            if ($ntins > 0){
                $sql_term = substr($sql_term, 0, -2).";";
                $res_update = mysql_query($sql_term) or die($errorimp.":".mysql_error());
                $ninsert = $ninsert + $ntins;
                $terminado = true;
            }

            // Comprobamos si hay terminales que eliminar:
            $sql_delterm = "SELECT * FROM terminales WHERE FLOTA = '$idflota' AND ISSI NOT IN (";
            for ($j = 0; $j < count($termexcel); $j++){
                $sql_delterm .= "'$termexcel[$j]'";
                if ($j < (count($termexcel) - 1)){
                    $sql_delterm .= ", ";
                }
            }
            $sql_delterm .= ")";
            $res_delterm = mysql_query($sql_delterm) or die ($errissi. " - " . count($termexcel) . " - " . $sql_delterm . "- " . mysql_error());
            $ndelterm = mysql_num_rows($res_delterm);
        }
        if ($res_update){
          $update = "OK";
          $clase = "flashok";
          $imagen = "imagenes/okm.png";
          $alt = "OK";
          $mensflash = sprintf($mensaje, $ninsert, $nupdate);
        }
        else{
          $update = "KO";
          $clase = "flashko";
          $imagen = "imagenes/cancelar.png";
          $alt = "Error";
          $mensflash = $errorimp;
        }
?>
        <p class="<?php echo $clase; ?>">
          <img src="<?php echo $imagen; ?>" alt="<?php echo $alt; ?>" title="<?php echo $alt; ?>"> &mdash; <?php echo $mensflash; ?>
        </p>
        <h1><?php echo $titulo; ?></h1>
        <h2><?php echo $h2termd; ?></h2>
        <form name="formupd" action="update_delterm.php" method="POST">
            <input name="idflota" type="hidden" value="<?php echo $idflota;?>">
            <?php
            foreach ($termupd as $idupd){
            ?>
                <input type="hidden" name="idupd[]" value="<?php echo $idupd; ?>">
            <?php
            }
            foreach ($termins as $issins){
            ?>
                <input type="hidden" name="issiins[]" value="<?php echo $issins; ?>">
            <?php
            }
            ?>
            <?php
            if ($ndelterm > 0) {
            ?>
            <p><?php echo $ptermd; ?></p>
                <table>
                    <tr>
                       <th>
                            <input type="checkbox" name="seltodo" id="seltodo" onclick="checkAll();" />
                            &mdash; Seleccionar
                        </th>
                        <th>ID</th>
                        <th><?php echo $tipo;?></th>
                        <th>Marca</th>
                        <th><?php echo $modelo;?></th>
                        <th>ISSI</th>
                        <th>TEI</th>
                        <th><?php echo $mnemonico;?></th>
                        <th>DOTS</th>
                    </tr>
            <?php
                for ($j = 0; $j < $ndelterm; $j++){
                    $terminal = mysql_fetch_array($res_delterm);
            ?>
                    <tr <?php if (($j % 2) == 1) { echo "class = 'filapar'";} ?>>
                        <td class="centro">
                            <input type="checkbox" name="delterm[]" value="<?php echo $terminal["ID"]; ?>">
                        </td>
                        <td><?php echo $terminal["ID"]; ?></td>
                        <td><?php echo $terminal["TIPO"]; ?></td>
                        <td><?php echo $terminal["MARCA"]; ?></td>
                        <td><?php echo $terminal["MODELO"]; ?></td>
                        <td><?php echo $terminal["ISSI"]; ?></td>
                        <td><?php echo $terminal["TEI"]; ?></td>
                        <td><?php echo $terminal["MNEMONICO"]; ?></td>
                        <td><?php echo $terminal["DOTS"]; ?></td>
                    </tr>
            <?php
                }
            ?>
                </table>
            <?php
            }
            else {
            ?>
              <p><?php echo $notermdel;?></p>
            <?php
            }
            ?>
        <table>
            <tr>
                <td class="borde">
                    <input type='image' name='nueva' src='imagenes/adelante.png' alt='Continuar' title="Continuar"><br>Continuar
                </td>
            </tr>
        </table>
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
