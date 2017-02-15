<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/contlimp_$idioma.php";
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
    //datos de la tabla Flotas
    $sql_contactos = "SELECT ID, NOMBRE FROM contactos ORDER BY NOMBRE ASC";
    $res_contactos = mysql_query($sql_contactos) or die($errsqlcont . ": " . mysql_error());
    $ncontactos = mysql_num_rows($res_contactos);
?>
    <h1><?php echo $h1; ?></h1>
    <?php
    if ($ncontactos == 0) {
        echo "<p class='error'>" . $errnocont . "</p>\n";
    }
    else {
        $contactos = array();
        for($i = 0; $i < $ncontactos; $i++){
            $row_contacto = mysql_fetch_array($res_contactos);
            $idcont = $row_contacto['ID'];
            $sql_contflotas = "SELECT * FROM contactos_flotas WHERE CONTACTO_ID = " . $idcont;
            $res_contflotas = mysql_query($sql_contflotas) or die($errsqlcf . " = " . $idcont . mysql_error());
            $ncontcf = mysql_num_rows($res_contflotas);
            $sql_resporg = "SELECT * FROM organizaciones WHERE RESPONSABLE = " . $idcont;
            $res_resporg = mysql_query($sql_resporg) or die($errresporg . " = " . $idcont . mysql_error());
            $nresporg = mysql_num_rows($res_resporg);
            $ncont = $ncontcf + $nresporg;
            if ($ncont == 0){
                $contactos[$i] = array(
                    'id' => $row_contacto['ID'], 'nombre' => $row_contacto['NOMBRE'],
                    'ncontcf' => $ncontcf, 'nresporg' => $nresporg, 'ncont' => $ncont
                );
            }
        }
        $linksubmit = "var confirma = confirm('" . sprintf($txtalert, count($contactos)) . "'); if (confirma) {document.limpcont.submit();}";
    ?>
        <h2><?php echo sprintf($h2res, count($contactos));?></h2>
        <form name="limpcont" method="POST" action="update_contacto.php">
            <input type="hidden" name="origen" value="limpiar">
        </form>
        <table>
            <tr>
                <td class="borde">
                    <a href='#' onclick="<?php echo $linksubmit; ?>"><img src='imagenes/no.png' alt='<?php echo $botlimpiar; ?>' title='<?php echo $botlimpiar; ?>'></a><br><?php echo $botlimpiar; ?>
                </td>
                <td class="borde">
                    <a href='flotas.php'><img src='imagenes/atras.png' alt='<?php echo $botvolver; ?>' title='<?php echo $botvolver; ?>'></a><br><?php echo $botvolver; ?>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <th>ID</th>
                <th><?php echo $thnombre; ?></th>
                <th><?php echo $thncontf; ?></th>
                <th><?php echo $thresporg; ?></th>
                <th><?php echo $thncont; ?></th>
            </tr>
            <?php
            $relleno = true;
            foreach ($contactos as $contacto) {
                $relleno = !($relleno);
            ?>
                <tr <?php if ($relleno) {echo "class='filapar'";} ?>>
                    <td><?php echo $contacto['id']; ?></td>
                    <td><?php echo $contacto['nombre']; ?></td>
                    <td><?php echo $contacto['ncontcf']; ?></td>
                    <td><?php echo $contacto['nresporg']; ?></td>
                    <td><?php echo $contacto['ncont']; ?></td>
                </tr>
            <?php
            }
            ?>
        </table>
<?php
    }
}
else{

?>
    <h1><?php echo $h1perm; ?></h1>
    <p class='error'><?php echo $permno; ?></p>
<?php
}
?>
</body>
</html>
