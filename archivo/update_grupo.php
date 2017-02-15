<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/gruposupd_$idioma.php";
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
        if ($permiso == 2){
            $res_update = false;
            if ($origen == "nuevo"){
                $enlaceok = "detalle_grupo.php";
                $enlacefail = "nuevo_grupo.php";
                $h1 = $titulo = $titnew;
                $error = $errornew;
                $mensaje = $mensnew;
                $namehid = "gissi";
                if ($gissi == ""){
                    $error .= " " . $errgissivac;
                }
                else {
                    $sql_check = "SELECT * FROM grupos WHERE GISSI = " . $gissi;
                    $res_check =  mysql_query($sql_check) or die ("Error al comprobar el GISSI: " . mysql_error($link));
                    $ncheck = mysql_num_rows($res_check);
                    if ($ncheck > 0){
                        $error .= $errchecknew;
                    }
                    else{
                        $sql_update = "INSERT INTO grupos (GISSI, MNEMONICO, TIPO, DESCRIPCION)";
                        $sql_update .= " VALUES ('" . $gissi . "', '" . $mnemonico . "', '" . $tipo . "', '" . $descripcion . "')";
                        $res_update = mysql_query($sql_update) or die ($error . " " . mysql_error($link));
                        $valuehid = $gissi;
                    }
                }
            }
            if ($origen == "editar"){
                $enlaceok = "detalle_grupo.php";
                $enlacefail = "editar_grupo.php";
                $h1 = $titulo = $tiedi;
                $error = $erroredi;
                $mensaje = $mensedi;
                $namehid = "gissi";
                $valuehid = $gissi;
                if ($gissi == ""){
                    $error .= " " . $errgissivac;
                }
                else{
                    $sql_check = "SELECT * FROM grupos WHERE (GISSI = " . $gissi .")";
                    $res_check =  mysql_query($sql_check) or die ("Error al comprobar el GISSI: " . mysql_error($link));
                    $ncheck = mysql_num_rows($res_check);
                    if ($ncheck == 0){
                        $error .= $errcheckgissi;
                    }
                    else{
                        $sql_update = "UPDATE grupos SET MNEMONICO = '" . $mnemonico . "', TIPO = '" . $tipo . "', ";
                        $sql_update .= "DESCRIPCION = '" . $descripcion . "' WHERE (GISSI = " . $gissi . ")";
                        $res_update = mysql_query($sql_update) or die ($error . " " . mysql_error($link));
                    }
                }
            }
            if ($origen == "eliminar"){
                $enlaceok = "grupos.php";
                $enlacefail = "detalle_grupo.php";
                $h1 = $titulo = $tidel;
                $error = $errordel;
                $mensaje = sprintf($mensdel, $gissi);
                $namehid = "flota";
                $valuehid = 0;
                if ($gissi == ""){
                    $error .= " " . $errgissivac;
                }
                else{
                    $sql_update = "DELETE FROM grupos_flotas WHERE GISSI = " . $gissi;
                    $res_update = mysql_query($sql_update) or die ($errdelgrupos . " " . mysql_error($link));
                    $sql_update = "DELETE FROM grupos WHERE GISSI = " . $gissi;
                    $res_update = mysql_query($sql_update) or die ($error . " " . mysql_error($link));
                }
            }
            if ($origen == "addflota"){
                $enlaceok = "grupos_flota.php";
                $enlacefail = "grupos_flonew.php";
                $namehid = "idflota";
                $valuehid = $idflota;
                $error = $erraddflota;
                $mensaje = sprintf($mensaddflota, $gissi);
                if ($gissi == ""){
                    $error .= " " . $errgissivac;
                }
                else{
                    $sql_check = "SELECT * FROM grupos WHERE GISSI = " . $gissi;
                    $res_check =  mysql_query($sql_check) or die ("Error al comprobar el GISSI: " . mysql_error($link));
                    $ncheck = mysql_num_rows($res_check);
                    if ($ncheck == 0){
                        $error .= $errcheckgissi;
                    }
                    else{
                        if (($carpexist != "") && ($carpexist != "NN")){
                            $carpetacomp = explode(";", $carpexist);
                            $carpeta = $carpetacomp[0];
                            $nomcarpeta = $carpetacomp[1];
                        }
                        else {
                            $carpeta = $carpnew;
                            $nomcarpeta = $nomnew;
                        }
                        $sql_check = "SELECT * FROM grupos_flotas WHERE (CARPETA = " . $carpeta . ")";
                        $sql_check .= "AND (GISSI = " . $gissi . ") AND (FLOTA = " . $idflota . ")";
                        $res_check =  mysql_query($sql_check) or die ("Error al comprobar la carpeta: ". $sql_check . " "  . mysql_error($link));
                        $ncheck = mysql_num_rows($res_check);
                        if ($ncheck > 0){
                            $error .= $errcheckcarp;
                        }
                        else{
                            $sql_update = "INSERT INTO grupos_flotas (FLOTA, GISSI, CARPETA, NOMBRE)";
                            $sql_update .= " VALUES ('" . $idflota . "', '" . $gissi . "', '" . $carpeta . "', '" . $nomcarpeta . "')";
                            $res_update = mysql_query($sql_update) or die ($error . " " . $sql_update . " " . mysql_error($link));
                        }
                    }
                }
            }
            if ($origen == "delflota"){
                $enlaceok = "grupos_flota.php";
                $enlacefail = "grupos_flodel.php";
                $namehid = "idflota";
                $valuehid = $idflota;
                $error = $errdelflota;
                $mensaje = $mensdelflota;
                if (($idgrupflo != "") && ($idgrupflo == "NN")){
                    $error .= $erridvac;
                }
                else{
                    $sql_check = "SELECT * FROM grupos_flotas WHERE (ID = " . $idgrupflo . ")";
                    $res_check =  mysql_query($sql_check) or die ("Error al comprobar la carpeta: ". $sql_check . " "  . mysql_error($link));
                    $ncheck = mysql_num_rows($res_check);
                    if ($ncheck > 0){
                        $sql_update = "DELETE FROM grupos_flotas WHERE (ID = " . $idgrupflo . ")";
                        $res_update =  mysql_query($sql_update) or die ("Error al eliminar el grupo de la carpeta: ". $sql_update . " "  . mysql_error($link));
                    }
                    else{
                        $error .= $errcheckid;
                    }
                }
            }
            if ($origen == "impexcel"){
                $enlaceok = "grupos_flota.php";
                $enlacefail = "grupos_flota.php";
                $namehid = "idflota";
                $valuehid = $idflota;
                $error = $errimpgrupos;
                $mensaje = $mensimpgrupos;
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
                $nomHoja = "(3) GSSI-TEL";

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

                // Borramos los grupos de la base de datos:
                $sql_update = "DELETE FROM grupos_flotas WHERE FLOTA = " . $idflota;
                $res_update = mysql_query($sql_update) or die ($error . " " . $errdelgrupimp . mysql_error($link));

                // Leemos los datos:
                $grupos = array();
                $i = 0;
                $ngrupos = 0;
                $nmaxgc = 0;
                $ncarpetas = 0;
                $columna = 0;
                while ($columna < $maxcolumnas){
                    $leido = false;
                    $fila = $fila_inicio;
                    if ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue() != ""){
                        $i++;
                        $grupos[$i]['CARPETA'] = 'CARPETA ' . $i;
                        $fila++;
                        $grupos[$i]['NOMBRE'] = mysql_real_escape_string($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue());
                        $fila++;
                        $fila++;
                        $listagissi = array();
                        $ngrupcarpeta = 0;
                        while ((!$leido) && ($fila < $maxfilas)){
                            $gissi = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($columna, $fila)->getValue();
                            $mnemo = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(($columna + 1), $fila)->getValue();
                            $mnemo = mysql_real_escape_string($mnemo);
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
                $newgrupos = 0;
                $newgf = 0;
                foreach ($grupos as $grupo){
                    $sql_gruposflotas = "INSERT INTO grupos_flotas (FLOTA, GISSI, CARPETA, NOMBRE) VALUES ";
                    $sql_insgrupo = "INSERT INTO grupos (GISSI, MNEMONICO, TIPO, DESCRIPCION) VALUES ";
                    $leninsgrupo = strlen($sql_insgrupo);
                    $carpeta = substr($grupo['CARPETA'], -1);
                    $nombre = $grupo['NOMBRE'];
                    $ngins = 0;
                    foreach ($grupo['GISSI'] as $grup_issi){
                        $gissi = $grup_issi['GISSI'];
                        $mnemo = $grup_issi['MNEMONICO'];
                        //datos de la tabla grupos
                        $sql_grupos = "SELECT * FROM grupos WHERE GISSI='$gissi'";
                        $res_grupos = mysql_query($sql_grupos) or die("Error en la consulta de Grupos: " . mysql_error());
                        $ngrupos = mysql_num_rows($res_grupos);
                        if ($ngrupos == 0){
                            $ngins++;
                            if ($carpeta == 1){
                                $tipo = "F-INTRA";
                            }
                            elseif ($carpeta = 2){
                                $tipo = "T-COM";
                            }
                            else{
                                $tipo = "I-EXCEL";
                            }
                            $sql_insgrupo .= "('$gissi', '$mnemo', '$tipo', 'IMPORTADO DESDE EXCEL'), ";
                            // Seguir aquí:
                        }
                        $sql_gruposflotas .= "($idflota, $gissi, $carpeta, '$nombre'), ";
                        $newgf++;
                    }
                    if ($ngins > 0){
                        $sql_insgrupo = substr($sql_insgrupo, 0, strlen($sql_insgrupo) - 2) . ";";
                        //$txt_sqlinsg .= $sql_insgrupo . "<br>";
                        $newgrupos = $newgrupos + $ngins;
                        $res_insgrupos = mysql_query($sql_insgrupo) or die("Error al guardar los Grupos: " . mysql_error());
                    }
                    else {
                        $sql_insgrupo = substr($sql_insgrupo, 0, strlen($sql_insgrupo) - $leninsgrupo);
                    }
                    $sql_gruposflotas = substr($sql_gruposflotas, 0, strlen($sql_gruposflotas) - 2) . ";";
                    $res_update = mysql_query($sql_gruposflotas) or die("Error al guardar los Grupos de Flotas: " . mysql_error());
                    //$txt_sqlinsgf .= $sql_gruposflotas . "<br>";
                }
            }
            if ($res_update){
                $enlace = $enlaceok;
                $mensflash = $mensaje;
                $update = "OK";
            }
            else{
                $enlace = $enlacefail;
                $mensflash = $error.  mysql_error();
                $update = "KO";
            }
        ?>
            <h1><?php echo $titulo; ?></h1>
            <form name="formupd" action="<?php echo $enlace;?>" method="POST">
                <input name="<?php echo $namehid;?>" type="hidden" value="<?php echo $valuehid;?>">
                <input name="update" type="hidden" value="<?php echo $update;?>">
                <input name="mensflash" type="hidden" value="<?php echo $mensflash;?>">
            </form>
            </pre>
             <script language="javascript" type="text/javascript">
                document.formupd2.submit();
             </script>
             <noscript>
                    <input type="submit" value="verify submit">
             </noscript>

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
