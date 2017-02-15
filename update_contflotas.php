<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contflotasupd_$idioma.php";
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
            $res_update = true;
            $error = $errimp;
            $mensaje = $mensimp;
            $enlaceok = "flotas_contactos.php";
            $enlacefail = "flotas_contactos.php";
            if ($origen == "importar"){
                $sql_flotas = "SELECT * FROM flotas ORDER BY flotas.FLOTA ASC";
                $res_flotas = mysql_db_query($base_datos, $sql_flotas) or die("Error en la consulta de flotas:" . mysql_error());
                $nflotas = mysql_num_rows($res_flotas);
                $idcf = 0;
                if ($nflotas > 0){
                    for ($i = 0; $i < $nflotas ; $i++){
                        if ($res_update){
                            $flota = mysql_fetch_array($res_flotas);
                            $idflota = $flota['ID'];
                            $contactos = array(
                                'RESPONSABLE' => $flota['RESPONSABLE'], 'CONTACTO1' => $flota['CONTACTO1'], 
                                'CONTACTO2' => $flota['CONTACTO2'], 'CONTACTO2' => $flota['CONTACTO2'], 
                                'INCID1' => $flota['INCID1'], 'INCID2' => $flota['INCID2'],
                                'INCID3' => $flota['INCID3'], 'INCID4' => $flota['INCID4']
                            );
                            $contactos_flota = array();
                            $numcf = 0;
                            $sql_values = "";
                            foreach ($contactos as $tipocont => $idcont){                            
                                if ($idcont > 0){
                                    $contactos_flota[$tipocont] = $idcont;
                                    $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $idcont;
                                    $res_contacto = mysql_db_query($base_datos, $sql_contacto) or die("Error en la consulta de Contacto:" .        mysql_error());
                                    $ncontacto = mysql_num_rows($res_contacto);
                                    if ($ncontacto > 0){
                                        $numcf++;
                                        $idcf++;
                                        $contacto = mysql_fetch_array($res_contacto);
                                        $rolcontacto = $tipocont;
                                        if ($tipocont == "RESPONSABLE"){
                                            $sql_values .= "($idcont, $idflota, 'RESPONSABLE'), ";
                                            $sql_values .= "($idcont, $idflota, 'OPERATIVO'), ";
                                        }
                                        else{
                                            // Aquí
                                            $posicion = strpos($tipocont, "CONTACTO");
                                            if ($posicion === FALSE){
                                                $sql_values .= "($idcont, $idflota, 'CONT24H'), ";
                                            }
                                            else{
                                                $ncontacto = substr($tipocont, -1);
                                                $sql_values .= "($idcont, $idflota, 'OPERATIVO'), ";
                                                $sql_values .= "($idcont, $idflota, 'TECNICO'), ";
                                            }                                        
                                        }
                                        
                                    }
                                }
                            }
                            if ($numcf > 0){
                                $sql_update = "INSERT INTO contactos_flotas (CONTACTO_ID, FLOTA_ID, ROL) VALUES ";
                                $sql_values = substr($sql_values, 0, strlen($sql_values) - 2);
                                $sql_update .= $sql_values . ";";
                                $res_update = mysql_db_query($base_datos, $sql_update) or die("Error en la actualización de la flota " . $flota['FLOTA'] . ': ' . mysql_error());;
                                $texto_sql .= "<p>" . $sql_update . "</p>";
                            }
                        }
                    }
                }
                else {
                    $error .= $errnoflotas;
                }
            }            
            if ($res_update){
                $enlace = $enlaceok;
                $mensflash = $idcf . " " .$mensaje;
                $update = "OK";
            }
            else{
                $enlace = $enlacefail;
                $mensflash = $error . mysql_error();
                $update = "KO";
            }
        ?>
            <h1><?php echo $h1; ?></h1>
            <form name="formupd" action="<?php echo $enlace;?>" method="POST">
                <input name="update" type="hidden" value="<?php echo $update;?>">
                <input name="mensflash" type="hidden" value="<?php echo $mensflash;?>">
            </form>
            <script language="javascript" type="text/javascript">
                document.formupd.submit();
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