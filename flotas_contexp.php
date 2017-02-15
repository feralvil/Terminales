<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotainf_$idioma.php";
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
<html>
    <head>
        <title>Relación de Contactos y Flotas</title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
        if ($permiso == 2){
            // Consulta total de flotas
            $sql_flotassel = "SELECT ID, FLOTA FROM FLOTAS ORDER BY FLOTA ASC";
            $res_flotassel = mysql_db_query($base_datos, $sql_flotassel) or die("Error en la consulta total de flotas:" . mysql_error());
            $nflotassel = mysql_num_rows($res_flotassel);
            $sql_flotas = "SELECT * FROM flotas WHERE 1";
            if ((isset($_POST['flotassel'])) && ($_POST['flotassel'] > 0)){
                $sql_flotas .= " AND (flotas.ID = ". $_POST['flotassel'] .")";
            }
            $sql_flotas .= " ORDER BY flotas.FLOTA ASC";
            $res_flotas = mysql_db_query($base_datos, $sql_flotas) or die("Error en la consulta de flotas:" . mysql_error());
                $nflotas = mysql_num_rows($res_flotas);
                if (isset ($update)){
                    if ($update == "KO"){
                    $clase = "flashko";
                    $imagen = "imagenes/cancelar.png";
                    $alt = "Error";
                }
                if ($update == "OK"){
                    $clase = "flashok";
                    $imagen = "imagenes/okm.png";
                    $alt = "OK";
                }
            ?>
                <p class="<?php echo $clase;?>">
                    <img src="<?php echo $imagen;?>" alt="<?php echo $alt;?>" title="<?php echo $alt;?>"> &mdash; <?php echo $mensflash;?>
                </p>
        <?php
        }
        ?>
            <h1>Relación de contactos y Flotas</h1>
            <h2>Criterios de búsqueda:</h2>
            <form name="formFlotas" id="formFlotas" action="flotas_contexp.php" method="post">
            <table>
                <tr>
                    <td class="borde">
                        <select name="flotassel" id="selFlota" onchange="formFlotas.submit();">
                            <option value="0">Seleccionar Flota</option>
                            <?php 
                            for ($i = 0; $i < $nflotassel; $i++){
                                $flotasel = mysql_fetch_array($res_flotassel);
                            ?>
                                <option value="<?php echo $flotasel['ID'];?>" <?php if($_POST['flotassel'] == $flotasel['ID']){echo 'selected';} ?>>
                                    <?php echo $flotasel['FLOTA'];?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>                
                </tr>
            </table>
            </form>
            <h2>Resultado de la busqueda</h2>
            <?php
            if ($nflotas > 0){
            ?>
                <form name="xlscontactos" method="post" action="xlscontexp.php" target="_blank">
                    <input type="hidden" name="idflota" value="<?php echo $flotassel;?>">
                    <input type="hidden" name="contactsel" value="<?php echo $contactsel;?>">
                <table>
                    <tr>
                        <td class="borde">Número de flotas: <?php echo $nflotas; ?></td>
                        <td class="borde">
                            <input type="image" src="imagenes/xls.png" alt="Exportar a Excel" title="Exportar a Excel">
                        </td>
                    </tr>
                </table>
                </form>
                <table>
                    <tr>
                        <th>Flota</th>
                        <th>Acrónimo</th>
                        <th>Rol</th>
                        <th>Nombre</th>
                        <th>Cargo</th>
                        <th>Mail</th>
                    </tr>
                    <?php
                    $idcf = 0;
                    for ($i = 0; $i < $nflotas ; $i++){
                        $flota = mysql_fetch_array($res_flotas);
                        $idflota = $flota['ID'];
                        $sql_contflota = "SELECT * FROM contactos_flotas WHERE (FLOTA_ID = " . $idflota .")";
                        $res_contflota = mysql_db_query($base_datos, $sql_contflota) or die("Error en la consulta de Contactos de Flota:" . mysql_error() . $sql_contflota);
                        $ncontflota = mysql_num_rows($res_contflota);
                        if ($ncontflota > 0){
                            $contactos_flota = array();
                            $responsables = array();
                            $operativos = array();
                            for ($j = 0; $j < $ncontflota; $j++){
                                $contflota = mysql_fetch_array($res_contflota);
                                if (($contflota['ROL'] == 'RESPONSABLE') || ($contflota['ROL'] == 'OPERATIVO')){
                                    $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $contflota['CONTACTO_ID'];
                                    $res_contacto = mysql_db_query($base_datos, $sql_contacto) or die("Error en la consulta de Contacto:" . mysql_error());
                                    $ncontacto = mysql_num_rows($res_contacto);
                                    if ($ncontacto > 0){
                                        $contacto = mysql_fetch_array($res_contacto);
                                        if ($contflota['ROL'] == 'RESPONSABLE'){
                                            $responsables[$contflota['CONTACTO_ID']] = $contacto;
                                        }
                                        elseif ($contflota['ROL'] == 'OPERATIVO'){
                                            $operativos[$contflota['CONTACTO_ID']] = $contacto;
                                        }
                                    }
                                }
                            }
                            if (count($responsables) > 0){
                                $contactos_flota['RESPONSABLE'] = $responsables;
                            }
                            if (count($operativos) > 0){
                                $contactos_flota['OPERATIVO'] = $operativos;
                            }
                        }
                        $idcontactos = array();
                        foreach ($contactos_flota as $tipocont => $tipo_contactos){
                            foreach ($tipo_contactos as $idcontacto => $contacto){
                                if (!in_array($idcontacto, $idcontactos)){
                                    $idcontactos[] = $idcontacto; 
                                    $idcf++;
                                    
                    ?>
                                    <tr <?php if (($idcf % 2) == 0) {echo " class='filapar'";}?>>
                                        <td><?php echo $flota['FLOTA']; ?></td>
                                        <td><?php echo $idcontacto; ?></td>
                                        <td><?php echo $tipocont; ?></td>
                                        <td><?php echo $contacto['NOMBRE']; ?></td>
                                        <td><?php echo $contacto['CARGO']; ?></td>
                                        <td><?php echo $contacto['MAIL']; ?></td>
                                    </tr>
                    <?php
                                }
                            }
                        }
                    }
                    ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class="error">No se han encontrado flotas con los criterios seleccionados</p>
        <?php
            }
        }
        else{
        ?>
            <h1>Permiso denegado</h1>
            <p class="error">No tiene permiso para acceder a esta información</p>
        <?php
        }
        ?>
        
    </body>
</html>