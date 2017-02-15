<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/buzones_$idioma.php";
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
<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
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
        else{
?>
            <script type="text/javascript" src="js/jquery.js"></script>
            <!-- Funciones JQUERY -->
            <script type="text/javascript">
                $(function(){
                   $("select#selbuzon").change(function(){
                       $("form#formbuzones").submit();
                   });
                   // Botón detalle:
                    $("a[name^=det]").click(function(){
                        var idbuzon = $(this).attr('id');
                        $("input#idbuzon").val(idbuzon);
                        $("form#accion").attr('action', 'detalle_buzon.php');
                        $("form#accion").submit();
                    });
                   // Botón editar:
                    $("a[name^=edi]").click(function(){
                        var idbuzon = $(this).attr('id');
                        $("input#idbuzon").val(idbuzon);
                        $("form#accion").attr('action', 'editar_buzon.php');
                        $("form#accion").submit();
                    });                    
                   // Botón borrar:
                    $("a[name^=del]").click(function(){
                        var idbuzon = $(this).attr('id');
                        $("input#idbuzon").val(idbuzon);
                        var confirmar = confirm("<?php echo $confborrar; ?>");
                        if (confirmar == true){
                            $("input#origen").val('borrar');
                            $("form#accion").attr('action', 'update_buzon.php');
                            $("form#accion").submit();
                        }
                        
                    });
                });
            </script>
<?php
        }
?>
            
    </head>
    <body>
<?php
    if ($permiso == 2){
        // Consultas de Buzones
        // Para el Select
        $sql_buzons = "SELECT * FROM buzons WHERE 1";
        $sql_select = $sql_buzons . " ORDER BY NOMBRE ASC";
        $res_select = mysql_query($sql_select) or die("Error en la consulta total de buzones: " . mysql_error());
        $nselect = mysql_num_rows($res_select);
        if (($selbuzon != "NN") && ($selbuzon != "")){
            $sql_buzons .= " AND (ID = '" . $selbuzon . "')";
        }
        $sql_buzons .= " ORDER BY NOMBRE ASC";
        $res_buzons = mysql_query($sql_buzons) or die("Error en la consulta de buzones: " . mysql_error());
        $nbuzons = mysql_num_rows($res_buzons);
        $buzones = array();
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
        <h1><?php echo $h1; ?></h1>
        <h4><?php echo $h4crit; ?></h4>
        <form id="formbuzones" method="POST" action="buzones.php">
            <select id="selbuzon" name="selbuzon">
                <option value='NN' <?php if (($selbuzon == "NN") || ($selbuzon == "")) echo ' selected'; ?>><?php echo $txtselbuzon; ?></option>
                <?php
                for ($i = 0; $i < $nselect; $i++){
                    $buz_select = mysql_fetch_array($res_select);
                ?>
                    <option value='<?php echo $buz_select['ID']; ?>' <?php if ($selbuzon == $buz_select['ID']) {echo ' selected';} ?>>
                        <?php echo $buz_select['NOMBRE']; ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </form>
        <h4><?php echo $h4res; ?></h4>
        <table>
            <tr class="borde">
                <td class="borde">
                    <?php echo $nreg. ": <b>" . $nbuzons . "</b>"; ?>
                </td>
                <td class="borde">
                    <a href="nuevo_buzon.php"><img src="imagenes/nueva.png" alt="<?php echo $newbuzon; ?>" title="<?php echo $newbuzon; ?>"></a> &mdash; <?php echo $newbuzon; ?>
                </td>
            </tr>
        </table>
<?php
        if ($nbuzons > 0){
?>
            <form id="accion" method="post" action="buzones.php">
                <input type="hidden" name="buzon_id" id="idbuzon" value="0">
                <input type="hidden" name="origen" id="origen" value="nada">
            </form>
            <table>
                <tr>
                    <th><?php echo $thacc; ?></th>
                    <th><?php echo $thacro; ?></th>
                    <th><?php echo $thnombre; ?></th>
                    <th><?php echo $thprop; ?></th>
                    <th><?php echo $thasoc; ?></th>
                </tr>
                <?php
                for ($i = 0; $i < $nbuzons; $i++){
                    $buzon = mysql_fetch_array($res_buzons);
                    $buzon_id = $buzon['ID'];
                    $sql_flotb = "SELECT COUNT(*) FROM flotas_buzons WHERE BUZON_ID = '" . $buzon_id . "'";
                    $sql_prop = $sql_flotb . " AND ROL = 'P'";
                    $res_prop = mysql_query($sql_prop) or die("Error en la consulta de flotas propietarias: " . mysql_error());
                    $row_prop = mysql_fetch_array($res_prop);
                    $nprop = $row_prop[0];
                    $sql_asoc = $sql_flotb . " AND ROL = 'A'";
                    $res_asoc = mysql_query($sql_asoc) or die("Error en la consulta de flotas asociadas: " . mysql_error());
                    $row_asoc = mysql_fetch_array($res_asoc);
                    $nasoc = $row_asoc[0];
                ?>
                    <tr <?php if (($i % 2) === 1) {echo "class='filapar'";}?>>
                        <td class="centro">
                            <a href='#' name="det-<?php echo $buzon_id; ?>" id='<?php echo $buzon_id; ?>'>
                                <img src='imagenes/consulta.png' alt="<?php echo $botdet;?>" title="<?php echo $botdet;?>"></a>
                            &mdash;
                            <a href='#' name="edi-<?php echo $buzon_id; ?>" id='<?php echo $buzon_id; ?>'>
                                <img src='imagenes/editar.png' alt="<?php echo $botedi;?>" title="<?php echo $botedi;?>"></a> 
                            &mdash;
                            <a href='#' name="del-<?php echo $buzon_id; ?>" id='<?php echo $buzon_id; ?>'>
                                <img src='imagenes/cancelar.png' alt="<?php echo $botborrar;?>" title="<?php echo $botborrar;?>">
                            </a>
                        </td>
                        <td><?php echo $buzon["ACRONIMO"]; ?></td>
                        <td><?php echo $buzon["NOMBRE"]; ?></td>
                        <td><?php echo $nprop; ?></td>
                        <td><?php echo $nasoc; ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
<?php
        }
        else{
?>
            <p class='error'><?php echo $errnores ?></p>
<?php
        }
    }
    else{
?>
        <h1><?php echo $h1perm ?></h1>
        <p class='error'><?php echo $permno ?></p>
<?php
    }
?>
    </body>
</html>
