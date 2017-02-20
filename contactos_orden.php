<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contorden_$idioma.php";
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
if ($permiso > 0){
    $sql_flotas = "SELECT ID, FLOTA FROM flotas ORDER BY flotas.FLOTA ASC";
    $res_flotas = mysql_query($sql_flotas) or die(mysql_error());
    $nflotas = mysql_num_rows($res_flotas);
?>
    <h1><?php echo $h1; ?></h1>
    <form action="contactos_orden.php" name="formflotas" method="POST">
        <h2>
            <?php echo $h2selflota; ?> &mdash;
            <a href="contactos_orden.php">
                <img src="imagenes/update.png" alt="<?php echo $resetcrit;?>" title="<?php echo $resetcrit;?>">
            </a>
        </h2>
        <table>
            <tr>
                <td>
                    <label for="selflota">Seleccionar Flota: </label>
                    <select name='idflota' id="selflota" onChange='document.formflotas.submit();'>
                        <option value='00' <?php if (($idflota == "00") || ($idflota == "")) echo ' selected'; ?>>
                            Seleccionar
                        </option>
                        <?php
                        for ($i = 0; $i < $nflotas; $i++){
                            $row_flota = mysql_fetch_array($res_flotas);
                        ?>
                            <option value="<?php echo $row_flota['ID'];?>" <?php if ($idflota == $row_flota['ID']) echo ' selected'; ?>>
                                <?php echo $row_flota['FLOTA'];?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
    </form>
    <h2><?php echo $h2contflota; ?></h2>
    <?php
    if ($idflota > 0){
        // Datos de contactos
        $sql_contactos = "SELECT * FROM contactos_flotas WHERE FLOTA_ID = $idflota ORDER BY ROL ASC, ORDEN ASC";
        $res_contactos = mysql_query($sql_contactos) or die("Error en la consulta de contacto: " . mysql_error());
        $ncontactos = mysql_num_rows($res_contactos);
        $idresp = 0;
        $operativos = array();
        $tecnicos = array();
        $cont24h = array();
        if ($ncontactos > 0){
            for ($i = 0; $i < $ncontactos; $i++){
                $row_cont = mysql_fetch_array($res_contactos);
                switch ($row_cont['ROL']){
                    case 'RESPONSABLE':{
                        $idresp = $row_cont['CONTACTO_ID'];
                        break;
                    }
                    case 'OPERATIVO':{
                        $operativos[$row_cont['CONTACTO_ID']] = $row_cont['ORDEN'];
                        break;
                    }
                    case 'TECNICO':{
                        $tecnicos[$row_cont['CONTACTO_ID']] = $row_cont['ORDEN'];
                        break;
                    }
                    case 'CONT24H':{
                        $cont24h[$row_cont['CONTACTO_ID']] = $row_cont['ORDEN'];
                        break;
                    }
                }
            }
        }
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
        <h3><?php echo $h3resp; ?></h3>
        <?php
        if ($idresp > 0){
            $sql_contacto = "SELECT * FROM contactos WHERE ID = $idresp";
            $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de responsable de flota: " . mysql_error());
            $ncontacto = mysql_num_rows($res_contacto);
            if ($ncontacto > 0){
                $contacto = mysql_fetch_array($res_contacto);
        ?>
                <table>
                    <tr>
                        <th class="t4c"><?php echo $thnombre; ?></th>
                        <th class="t10c">DNI</th>
                        <th class="t4c"><?php echo $thcargo; ?></th>
                        <th class="t5c"><?php echo $thmail; ?></th>
                        <th class="t5c"><?php echo $thtelefono; ?></th>
                    </tr>
                    <tr>
                        <td><?php echo $contacto['NOMBRE']; ?></td>
                        <td><?php echo $contacto["NIF"]; ?></td>
                        <td><?php echo $contacto["CARGO"]; ?></td>
                        <td><?php echo $contacto["MAIL"]; ?></td>
                        <td><?php echo $contacto["TELEFONO"]; ?></td>
                    </tr>
                </table>

        <?php
            }
            else{
        ?>
                <p class='error'><?php echo $errresp; ?></p>
        <?php
            }
        }
        else{
        ?>
            <p class='error'><?php echo $errnoresp; ?></p>
        <?php
        }
        ?>
        <h3><?php echo $h3operativo; ?></h3>
        <?php
        if (count($operativos) > 0){
        ?>
            <table>
                <tr>
                    <th class="t4c"><?php echo $thnombre; ?></th>
                    <th class="t10c">DNI</th>
                    <th class="t4c"><?php echo $thcargo; ?></th>
                    <th class="t5c"><?php echo $thmail; ?></th>
                    <th class="t5c"><?php echo $thtelefono; ?></th>
                    <th class="t4c"><?php echo $thorden; ?></th>
                </tr>
                <?php
                $relleno = false;
                foreach ($operativos as $idcont => $orden) {
                    $sql_contacto = "SELECT * FROM contactos WHERE ID = $idcont";
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto operativo: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                ?>
                    <tr <?php if ($relleno) {echo "class='filapar'";} ?>>
                        <?php
                        if ($ncontacto > 0){
                            $contacto = mysql_fetch_array($res_contacto);
                        ?>
                            <td><?php echo $contacto['NOMBRE']; ?></td>
                            <td><?php echo $contacto["NIF"]; ?></td>
                            <td><?php echo $contacto["CARGO"]; ?></td>
                            <td><?php echo $contacto["MAIL"]; ?></td>
                            <td><?php echo $contacto["TELEFONO"]; ?></td>
                            <td><?php echo $orden; ?></td>
                        <?php
                        }
                        else{
                        ?>
                            <td colspan="6">
                                <span class='error'><?php echo $erroper; ?></span>
                            </td>
                        <?php
                        }
                        ?>
                    </tr>
                <?php
                    $relleno = !($relleno);
                }
                ?>
            </table>
        <?php
        }
        else{
        ?>
            <p class='error'><?php echo $errnooper; ?></p>
        <?php
        }
        ?>
        <h3><?php echo $h3tecnico; ?></h3>
        <?php
        if (count($tecnicos) > 0){
        ?>
            <table>
                <tr>
                    <th class="t4c"><?php echo $thnombre; ?></th>
                    <th class="t10c">DNI</th>
                    <th class="t4c"><?php echo $thcargo; ?></th>
                    <th class="t5c"><?php echo $thmail; ?></th>
                    <th class="t5c"><?php echo $thtelefono; ?></th>
                    <th class="t4c"><?php echo $thorden; ?></th>
                </tr>
                <?php
                $relleno = false;
                foreach ($tecnicos as $idcont => $orden) {
                    $sql_contacto = "SELECT * FROM contactos WHERE ID = $idcont";
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto técnico: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                ?>
                    <tr <?php if ($relleno) {echo "class='filapar'";} ?>>
                        <?php
                        if ($ncontacto > 0){
                            $contacto = mysql_fetch_array($res_contacto);
                        ?>
                            <td><?php echo $contacto['NOMBRE']; ?></td>
                            <td><?php echo $contacto["NIF"]; ?></td>
                            <td><?php echo $contacto["CARGO"]; ?></td>
                            <td><?php echo $contacto["MAIL"]; ?></td>
                            <td><?php echo $contacto["TELEFONO"]; ?></td>
                            <td><?php echo $orden; ?></td>
                        <?php
                        }
                        else{
                        ?>
                            <td colspan="6">
                                <span class='error'><?php echo $erroper; ?></span>
                            </td>
                        <?php
                        }
                        ?>
                    </tr>
                <?php
                    $relleno = !($relleno);
                }
                ?>
            </table>
        <?php
        }
        else{
        ?>
            <p class='error'><?php echo $errnooper; ?></p>
        <?php
        }
        ?>
        <h3><?php echo $h3cont24h; ?></h3>
        <?php
        if (count($cont24h) > 0){
        ?>
            <table>
                <tr>
                    <th class="t4c"><?php echo $thnombre; ?></th>
                    <th class="t10c">DNI</th>
                    <th class="t4c"><?php echo $thcargo; ?></th>
                    <th class="t5c"><?php echo $thmail; ?></th>
                    <th class="t5c"><?php echo $thtelefono; ?></th>
                    <th class="t4c"><?php echo $thorden; ?></th>
                </tr>
                <?php
                $relleno = false;
                foreach ($cont24h as $idcont => $orden) {
                    $sql_contacto = "SELECT * FROM contactos WHERE ID = $idcont";
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de contacto técnico: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                ?>
                    <tr <?php if ($relleno) {echo "class='filapar'";} ?>>
                        <?php
                        if ($ncontacto > 0){
                            $contacto = mysql_fetch_array($res_contacto);
                        ?>
                            <td><?php echo $contacto['NOMBRE']; ?></td>
                            <td><?php echo $contacto["NIF"]; ?></td>
                            <td><?php echo $contacto["CARGO"]; ?></td>
                            <td><?php echo $contacto["MAIL"]; ?></td>
                            <td><?php echo $contacto["TELEFONO"]; ?></td>
                            <td><?php echo $orden; ?></td>
                        <?php
                        }
                        else{
                        ?>
                            <td colspan="6">
                                <span class='error'><?php echo $err24h; ?></span>
                            </td>
                        <?php
                        }
                        ?>
                    </tr>
                <?php
                    $relleno = !($relleno);
                }
                ?>
            </table>
        <?php
        }
        else{
        ?>
            <p class='error'><?php echo $errno24h; ?></p>
        <?php
        }
        ?>
        <form name="contorden" action="update_contacto.php" method="POST">
            <input type="hidden" name="origen" value="ordenar">
            <input type="hidden" name="idflota" value="<?php echo $idflota;?>">
            <table>
                <tr>
                    <td class="borde">
                        <input type="image" src="imagenes/pencil.png" alt='<?php echo $botorden; ?>' title='<?php echo $botorden; ?>'/><br><?php echo $botorden; ?>
                    </td>
                    <td class="borde">
                        <a href='flotas.php'><img src='imagenes/atras.png' alt='<?php echo $botvolver; ?>' title='<?php echo $botvolver; ?>'></a><br><?php echo $botvolver; ?>
                    </td>
                </tr>
            </table>
        </form>

    <?php
    }
    else{
    ?>
        <p class='error'><?php echo $errnoflota; ?></p>
    <?php
    }
    ?>
<?php
}
else {
?>
    <h1><?php echo $h1perm; ?></h1>
    <p class='error'><?php echo $permno; ?></p>
<?php
}
?>
</body>
</html>
