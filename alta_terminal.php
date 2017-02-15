<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/termayb_$idioma.php";
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
        echo 'Error al seleccionar la Base de Datos: '. mysql_error();
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
        <title><?php echo $titalta; ?> del Terminal COMDES</title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
<?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($flota_usu == 0){
?>
            <script type="text/javascript">
                window.top.location.href = "https://comdes.gva.es/cvcomdes/";
            </script>
<?php
        }
?>
    </head>
<?php
if ($permiso == 2) {
    //datos de la tabla terminales
    $sql_terminal = "SELECT * FROM terminales WHERE ID='$idterm'";
    $res_terminal = mysql_query($sql_terminal) or die("Error en la consulta de terminal: " . mysql_error());
    $nterminal = mysql_num_rows($res_terminal);
    if ($nterminal == 0) {
        echo "<p class='error'>No hay resultados en la consulta del Terminal</p>\n";
    }
    else {
        $row_terminal = mysql_fetch_array($res_terminal);
        $id_flota = $row_terminal["FLOTA"];
    }
    //datos de la tabla flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID='$id_flota'";
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de terminal: " . mysql_error());
    $nflota = mysql_num_rows($res_terminal);
    if ($nflota == 0) {
        echo "<p class='error'>No hay resultados en la consulta de la Flota</p>\n";
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
    }
?>
    <body>
        <h1><?php echo $titalta; ?> del Terminal TEI: <?php echo $row_terminal["TEI"]; ?> / ISSI: <?php echo $row_terminal["ISSI"]; ?> de la Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
        <form action="update_terminal.php" method="POST" name="formterminal">
<?php
        if ($row_terminal["ESTADO"] == 'A') {
?>
            <h2>Terminal de <?php echo $titalta; ?></h2>
            <div id="resultado">
                <p><img src='imagenes/error.png' alt='Error'></p>
                <p class="error"><?php echo $erralta; ?></p>
                <p>
                    <a href='#' onclick="document.formcancel.submit();">
                        <img src='imagenes/back.png' alt='Error'>
                    </a><br>Volver
                </p>
            </div>
<?php
        }
        else {
            $row_flota = mysql_fetch_array($res_flota);
?>
            <h2>Confirmar <?php echo $titalta; ?></h2>
            <input type="hidden" name="idterm" value="<?php echo $idterm; ?>">
            <input type="hidden" name="origen" value="alta">
            <div class="centro">
                <p><img src='imagenes/important.png' alt='Error'></p>
                <p class="error"><?php echo $mensalta; ?></p>
                <table>
                    <tr>
                        <td class="borde">
                            <input type='image' name='action' src='imagenes/ok.png' alt='<?php echo $botacept ?>' title='<?php echo $botacept ?>'><br><?php echo $botacept ?>
                        </td>
                        <td class="borde">
                            <a href='#' onclick="document.formcancel.submit();">
                                <img src='imagenes/no.png' alt='<?php echo $botcancel ?>' title='<?php echo $botcancel ?>'>
                            </a><br><?php echo $botcancel ?>
                        </td>
                    </tr>
                </table>
            </div>

<?php
        }
?>
        </form>
        <form name="formcancel" action="detalle_terminal.php" action="POST">
            <input type="hidden" name="idterm" value="<?php echo $idterm; ?>">
        </form>
<?php
    }
    else {
?>
        <h1>Acceso denegado</h1>
        <p class='error'><?php echo $permno; ?></p>
<?php
    }
?>
    </body>
</html>
