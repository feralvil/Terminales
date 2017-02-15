<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/leercont_$idioma.php";
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
import_request_variables("gp", "");

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

    // Aumentamos el tamaño de la memoria:
    ini_set('memory_limit', '64M');

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

    // Buscamos la fila donde empieza el responsable de la Organización
    $resp = false;
    $i = $fila;
    while (($i < $numfilas)&&(!$resp)){
        $celda = "A$i";
        if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "DATOS RESPONSABLE ORGANIZACIÓN:"){
            $resp = true;
        }
        $i++;
    }
    $filaresporg = $fila = $i;
    $resporg = array();
    $resporg ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
    $resporg ["NIF"] = $objPHPExcel->getActiveSheet()->getCell("J$fila")->getValue();
    $fila++;
    $resporg ["CARGO"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
    $fila++;
    if ($row_flota['ORGANIZACION'] == 0){
        $organizacion = array();
        $organizacion['ORGANIZACION'] = $objPHPExcel->getActiveSheet()->getCell("C6")->getValue();
        $organizacion['INE'] = 0;
        $organizacion['DOMICILIO'] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
        $fila++;
        $celdadom = explode('-',$objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue());
        $organizacion['CP'] = trim($celdadom[0]);
        $nomorganiza = mysql_real_escape_string($organizacion['ORGANIZACION']);
        $sql_organiza = "SELECT * FROM organizaciones WHERE ORGANIZACION = '" . $nomorganiza . "'";
        $res_organiza = mysql_query($sql_organiza) or die("Error en la consulta del Nombre de Organización: " . mysql_error());
        $norganiza = mysql_num_rows($res_organiza);
        if ($norganiza > 0){
            $imagen = "imagenes/update.png";
            $botaccion = $txtbotupdorg;
        }
        else{
            $imagen = "imagenes/nueva.png";
            $botaccion = $txtbotneworg;
        }
    }
    else{
        $idorg = $row_flota['ORGANIZACION'];
        $sql_organiza = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
        $res_organiza = mysql_query($sql_organiza) or die("Error en la consulta de Organización: " . mysql_error());
        $norganiza = mysql_num_rows($res_organiza);
        if ($norganiza > 0) {
            $organizacion = mysql_fetch_array($res_organiza);
        }
        $fila++;
    }
    $fila++;
    $resporg ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
    $resporg ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("I$fila")->getValue();
    $fila++;
    $resporgtxt = serialize($resporg);

    // Buscamos la fila donde empieza el responsable de la Flota
    $cont = false;
    $i = $fila;
    while (($i < $numfilas)&&(!$cont)){
        $celda = "A$i";
        if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "DATOS RESPONSABLE FLOTA:"){
            $cont = true;
        }
        $i++;
    }
    $filarespflo = $fila = $i;
    $respflo = array();
    $respflo ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
    $respflo ["NIF"] = $objPHPExcel->getActiveSheet()->getCell("J$fila")->getValue();
    $fila++;
    $respflo ["CARGO"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
    $fila++;
    $fila++;
    $fila++;
    $respflo ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("C$fila")->getValue();
    $respflo ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("I$fila")->getValue();
    $fila++;
    $respflotxt = serialize($respflo);

    // Buscamos los contactos Operativos:
    $cop = false;
    $i = $fila;
    while (($i < $numfilas)&&(!$cop)){
        $celda = "A$i";
        if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "CONTACTO OPERATIVO:"){
            $cop = true;
        }
        $i++;
    }
    $filaop = 0;
    if ($cop){
        $filaop = $fila = $i + 1;
        $contop = array();
        while ($objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue() != "") {
            $contacto = array();
            $contacto ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue();
            $contacto ["NIF"] = $objPHPExcel->getActiveSheet()->getCell("D$fila")->getValue();
            $contacto ["CARGO"] = $objPHPExcel->getActiveSheet()->getCell("F$fila")->getValue();
            $contacto ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("I$fila")->getValue();
            $contacto ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("L$fila")->getValue();
            array_push($contop, $contacto);
            $fila++;
        }
    }

    // Buscamos los contactos Técnicos:
    $ctec = false;
    $i = $fila;
    while (($i < $numfilas)&&(!$ctec)){
        $celda = "A$i";
        if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "CONTACTO TÉCNICO:"){
            $ctec = true;
        }
        $i++;
    }
    $filatec = 0;
    if ($ctec){
        $filatec = $fila = $i + 1;
        $contec = array();
        while ($objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue() != "") {
            $contacto = array();
            $contacto ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue();
            $contacto ["NIF"] = $objPHPExcel->getActiveSheet()->getCell("D$fila")->getValue();
            $contacto ["CARGO"] = $objPHPExcel->getActiveSheet()->getCell("F$fila")->getValue();
            $contacto ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("I$fila")->getValue();
            $contacto ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("L$fila")->getValue();
            array_push($contec, $contacto);
            $fila++;
        }
    }

    // Buscamos los contactos 24x7:
    $c24h = false;
    $i = $fila;
    while (($i < $numfilas)&&(!$c24h)){
        $celda = "A$i";
        if ($objPHPExcel->getActiveSheet()->getCell($celda)->getValue() == "CONTACTO 24x7:"){
            $c24h = true;
        }
        $i++;
    }
    $fila24h = 0;
    if ($c24h){
        $fila24h = $fila = $i + 1;
        $cont24h = array();
        while ($objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue() != "") {
            $contacto = array();
            $contacto ["NOMBRE"] = $objPHPExcel->getActiveSheet()->getCell("A$fila")->getValue();
            $contacto ["MAIL"] = $objPHPExcel->getActiveSheet()->getCell("F$fila")->getValue();
            $contacto ["TELEFONO"] = $objPHPExcel->getActiveSheet()->getCell("K$fila")->getValue();
            array_push($cont24h, $contacto);
            $fila++;
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
        <form name="updcont" action="update_contexcel.php" method="POST">
            <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
            <input name="filaresporg" type="hidden" value="<?php echo $filaresporg; ?>">
            <input name="filarespflo" type="hidden" value="<?php echo $filarespflo; ?>">
            <input name="filaop" type="hidden" value="<?php echo $filaop; ?>">
            <input name="filatec" type="hidden" value="<?php echo $filatec; ?>">
            <input name="fila24h" type="hidden" value="<?php echo $fila24h; ?>">
            <p>
                <label for="contoficiales"><strong><?php echo $txtcontof; ?></strong></label>
                <input id = "contoficiales" type="checkbox" name="formcont" value="SI" />
            </p>
        </form>
        <form name="detflota" action="update_flota.php" method="POST">
            <input name="origen" type="hidden" value="leerexcel">
            <input name="idflota" type="hidden" value="<?php echo $idflota; ?>">
        </form>
        <table>
            <tr>
                <td class="borde">
                    <a href='#' onclick="document.updcont.submit();">
                        <img src='imagenes/guardar.png' alt='Guardar' title='Guardar'>
                    </a><br>Guardar
                </td>
                <td class="borde">
                    <a href='#' onclick="document.detflota.submit();">
                        <img src='imagenes/atras.png' alt='<?php echo $botdetalle; ?>' title="<?php echo $botdetalle; ?>">
                    </a><br><?php echo $botdetalle . " de Flota"; ?>
                </td>
            </tr>
        </table>
        <h2><?php echo $h2organiza; ?></h2>
        <?php
        if ($row_flota["ORGANIZACION"] > 0){
        ?>
            <p class="KO">
                <img src="imagenes/atencion.png" alt="" title=""> &mdash;
                <?php echo sprintf($mensorg, $organizacion['ORGANIZACION']); ?>
            </p>
        <?php
        }
        ?>
        <table>
            <tr>
                <th><?php echo $thaccion; ?></th>
                <th><?php echo $thorganiza; ?></th>
                <th><?php echo $cp; ?></th>
                <th><?php echo $direccion; ?></th>
            </tr>
            <tr>
                <td class="centro">
                    <img src="<?php echo $imagen; ?>" alt="<?php echo $botaccion; ?>" title="<?php echo $botaccion; ?>">
                </td>
                <td><?php echo $organizacion['ORGANIZACION']; ?></td>
                <td><?php echo $organizacion['CP']; ?></td>
                <td><?php echo $organizacion['DOMICILIO']; ?></td>
            </tr>
        </table>

        <h2><?php echo $h2resporg; ?></h2>
        <?php
        if (count($resporg) > 0){
            // Buscamos el nombre del contacto en la BBDD:
            $sql_contacto = "SELECT * FROM contactos WHERE NOMBRE = '" . $resporg['NOMBRE'] . "'";
            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de Contacto: " . mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto > 0) {
                $contacto = mysql_fetch_array($res_contacto);
                $resporg['ID'] = $contacto['ID'];
                $imagen = "imagenes/update.png";
                $botaccion = $txtbotupdate;
            }
            else{
                $imagen = "imagenes/nueva.png";
                $botaccion = $txtbotnuevo;
            }
        ?>
            <table>
                 <tr>
                    <th><?php echo $thaccion; ?></th>
                    <th><?php echo $nomflota; ?></th>
                    <th>NIF</th>
                    <th><?php echo $cargo; ?></th>
                    <th><?php echo $telefono; ?></th>
                    <th><?php echo $mail; ?></th>
                </tr>
                <tr>
                    <td class="centro">
                        <img src="<?php echo $imagen; ?>" alt="<?php echo $botaccion; ?>" title="<?php echo $botaccion; ?>">
                    </td>
                    <td><?php echo $resporg["NOMBRE"]; ?></td>
                    <td><?php echo $resporg["NIF"]; ?></td>
                    <td><?php echo $resporg["CARGO"]; ?></td>
                    <td><?php echo $resporg["TELEFONO"]; ?></td>
                    <td><?php echo $resporg["MAIL"]; ?></td>
                </tr>
            </table>
        <?php
        }
        else{
        ?>
            <p class="error"><?php echo $nocontorg; ?></p>
        <?php
        }
        ?>

        <h2><?php echo $h2respflota; ?></h2>
        <?php
        if (count($respflo) > 0){
            // Buscamos el nombre del contacto en la BBDD:
            $sql_contacto = "SELECT * FROM contactos WHERE NOMBRE = '" . $respflo['NOMBRE'] . "'";
            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de Contacto: " . mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto > 0) {
                $contacto = mysql_fetch_array($res_contacto);
                $respflo['ID'] = $contacto['ID'];
                $imagen = "imagenes/update.png";
                $botaccion = $txtbotupdate;
            }
            else{
                $imagen = "imagenes/nueva.png";
                $botaccion = $txtbotnuevo;
            }
        ?>
            <table>
                 <tr>
                    <th><?php echo $thaccion; ?></th>
                    <th><?php echo $nomflota; ?></th>
                    <th>NIF</th>
                    <th><?php echo $cargo; ?></th>
                    <th><?php echo $telefono; ?></th>
                    <th><?php echo $mail; ?></th>
                </tr>
                <tr>
                    <td class="centro">
                        <img src="<?php echo $imagen; ?>" alt="<?php echo $botaccion; ?>" title="<?php echo $botaccion; ?>">
                    </td>
                    <td><?php echo $respflo["NOMBRE"]; ?></td>
                    <td><?php echo $respflo["NIF"]; ?></td>
                    <td><?php echo $respflo["CARGO"]; ?></td>
                    <td><?php echo $respflo["TELEFONO"]; ?></td>
                    <td><?php echo $respflo["MAIL"]; ?></td>
                </tr>
            </table>
        <?php
        }
        else{
        ?>
            <p class="error"><?php echo $nocontflota; ?></p>
        <?php
        }
        ?>

    <h2><?php echo $h2contop; ?></h2>
    <?php
    if (count($contop) > 0){
    ?>
        <table>
            <tr>
               <th><?php echo $thaccion; ?></th>
               <th><?php echo $nomflota; ?></th>
               <th>NIF</th>
               <th><?php echo $cargo; ?></th>
               <th><?php echo $telefono; ?></th>
               <th><?php echo $mail; ?></th>
           </tr>
           <?php
           $relleno = true;
           foreach ($contop as $contacto) {
               $relleno = !($relleno);
               // Buscamos el nombre del contacto en la BBDD:
               $sql_contacto = "SELECT * FROM contactos WHERE NOMBRE = '" . $contacto['NOMBRE'] . "'";
               $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de Contacto: " . mysql_error());
               $ncontacto = mysql_num_rows($res_contacto);
               if ($ncontacto > 0) {
                   $contbbdd = mysql_fetch_array($res_contacto);
                   $contacto['ID'] = $contbbdd['ID'];
                   $imagen = "imagenes/update.png";
                   $botaccion = $txtbotupdate;
               }
               else{
                   $imagen = "imagenes/nueva.png";
                   $botaccion = $txtbotnuevo;
               }
           ?>
           <tr <?php if ($relleno) {echo "class='filapar'" ;}?>>
               <td class="centro">
                   <img src="<?php echo $imagen; ?>" alt="<?php echo $botaccion; ?>" title="<?php echo $botaccion; ?>">
               </td>
               <td><?php echo $contacto['NOMBRE']; ?></td>
               <td><?php echo $contacto['NIF']; ?></td>
               <td><?php echo $contacto['CARGO']; ?></td>
               <td><?php echo $contacto['TELEFONO']; ?></td>
               <td><?php echo $contacto['MAIL']; ?></td>
           </tr>
           <?php
           }
           ?>
        </table>
    <?php
    }
    else {
    ?>
        <p class='error'><?php echo $errnoop; ?></p>
    <?php
    }
    ?>

    <h2><?php echo $h2contec; ?></h2>
    <?php
    if (count($contec) > 0){
    ?>
        <table>
            <tr>
               <th><?php echo $thaccion; ?></th>
               <th><?php echo $nomflota; ?></th>
               <th>NIF</th>
               <th><?php echo $cargo; ?></th>
               <th><?php echo $telefono; ?></th>
               <th><?php echo $mail; ?></th>
           </tr>
           <?php
           $relleno = true;
           foreach ($contec as $contacto) {
               $relleno = !($relleno);
               // Buscamos el nombre del contacto en la BBDD:
               $sql_contacto = "SELECT * FROM contactos WHERE NOMBRE = '" . $contacto['NOMBRE'] . "'";
               $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de Contacto: " . mysql_error());
               $ncontacto = mysql_num_rows($res_contacto);
               if ($ncontacto > 0) {
                   $contbbdd = mysql_fetch_array($res_contacto);
                   $contacto['ID'] = $contbbdd['ID'];
                   $imagen = "imagenes/update.png";
                   $botaccion = $txtbotupdate;
               }
               else{
                   $imagen = "imagenes/nueva.png";
                   $botaccion = $txtbotnuevo;
               }
           ?>
           <tr <?php if ($relleno) {echo "class='filapar'" ;}?>>
               <td class="centro">
                   <img src="<?php echo $imagen; ?>" alt="<?php echo $botaccion; ?>" title="<?php echo $botaccion; ?>">
               </td>
               <td><?php echo $contacto['NOMBRE']; ?></td>
               <td><?php echo $contacto['NIF']; ?></td>
               <td><?php echo $contacto['CARGO']; ?></td>
               <td><?php echo $contacto['TELEFONO']; ?></td>
               <td><?php echo $contacto['MAIL']; ?></td>
           </tr>
           <?php
           }
           ?>
        </table>
    <?php
    }
    else {
    ?>
        <p class='error'><?php echo $errnotec; ?></p>
    <?php
    }
    ?>

    <h2><?php echo $h2cont24h; ?></h2>
    <?php
    if (count($cont24h) > 0){
    ?>
        <table>
            <tr>
               <th><?php echo $thaccion; ?></th>
               <th><?php echo $nomflota; ?></th>
               <th><?php echo $telefono; ?></th>
               <th><?php echo $mail; ?></th>
           </tr>
           <?php
           $relleno = true;
           foreach ($cont24h as $contacto) {
               $relleno = !($relleno);
               // Buscamos el nombre del contacto en la BBDD:
               $sql_contacto = "SELECT * FROM contactos WHERE NOMBRE = '" . $contacto['NOMBRE'] . "'";
               $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de Contacto: " . mysql_error());
               $ncontacto = mysql_num_rows($res_contacto);
               if ($ncontacto > 0) {
                   $contbbdd = mysql_fetch_array($res_contacto);
                   $contacto['ID'] = $contbbdd['ID'];
                   $imagen = "imagenes/update.png";
                   $botaccion = $txtbotupdate;
               }
               else{
                   $imagen = "imagenes/nueva.png";
                   $botaccion = $txtbotnuevo;
               }
           ?>
           <tr <?php if ($relleno) {echo "class='filapar'" ;}?>>
               <td class="centro">
                   <img src="<?php echo $imagen; ?>" alt="<?php echo $botaccion; ?>" title="<?php echo $botaccion; ?>">
               </td>
               <td><?php echo $contacto['NOMBRE']; ?></td>
               <td><?php echo $contacto['TELEFONO']; ?></td>
               <td><?php echo $contacto['MAIL']; ?></td>
           </tr>
           <?php
           }
           ?>
        </table>
    <?php
    }
    else {
    ?>
        <p class='error'><?php echo $errno24h; ?></p>
    <?php
    }
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
