<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/buzondet_$idioma.php";
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
                    // Cambiamos el formulario para ir a editar
                    $('a#botedit').click(function(){
                        $('form#formbuzon').attr('action', 'editar_buzon.php');
                        $('form#formbuzon').submit();
                    });                    
                   // Botón borrar:
                    $("a#botdel").click(function(){
                        var confirmar = confirm("<?php echo $confborrar; ?>");
                        if (confirmar == true){
                            $("input#origen").val('borrar');
                            $("form#formbuzon").attr('action', 'update_buzon.php');
                            $("form#formbuzon").submit();
                        }                        
                    });
                   // Botón editar flota:
                    $("a[name^=edi]").click(function(){
                        var buzflota_id = $(this).attr('id');
                        $("input#buzflota_id").val(buzflota_id);
                        $("form#formbuzon").attr('action', 'editar_buzflot.php');
                        $("form#formbuzon").submit();
                    });                    
                   // Botón borrar flota:
                    $("a[name^=del]").click(function(){
                        var buzflota_id = $(this).attr('id');
                        $("input#buzflota_id").val(buzflota_id);
                        var confirmar = confirm("<?php echo $confborrar; ?>");
                        if (confirmar == true){
                            $("input#origen").val('borrar');
                            $("form#formbuzon").attr('action', 'update_buzflot.php');
                            $("form#formbuzon").submit();
                        }
                        
                    });                    
                   // Botón detalle flota:
                    $("a[name^=det]").click(function(){
                        var flota_id = $(this).attr('id');
                        $("input#idflota").val(flota_id);
                        $("form#formflota").submit();
                        
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
        $sql_buzon = "SELECT * FROM buzons WHERE ID = " . $buzon_id;
        $res_buzon = mysql_query($sql_buzon) or die("Error en la consulta del buzón: " . mysql_error());
        $nbuzon = mysql_num_rows($res_buzon);
        if ($nbuzon > 0){
            $buzon = mysql_fetch_array($res_buzon);
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
            <h2><?php echo $h2buzon; ?></h2>
            <table>
                <tr>
                    <th class="t30p"><?php echo $thacro; ?></th>
                    <th class="t2c"><?php echo $thnombre; ?></th>
                    <th class="t5c"><?php echo $thactivo; ?></th>
                </tr>
                <tr>
                    <td><?php echo $buzon['ACRONIMO']; ?></td>
                    <td><?php echo $buzon['NOMBRE']; ?></td>
                    <td><?php echo $buzon['ACTIVO']; ?></td>
                </tr>
            </table>
            <h2><?php echo sprintf($h2flotas, $h2pert); ?></h2>
            <?php
                // Flotas pertenecientes:
                $sql_buzflot = "SELECT flotas.FLOTA, flotas.ID AS IDFLOTA, flotas_buzons.ROL, flotas_buzons.ID, flotas.RANGO";
                $sql_buzflot .= " FROM flotas, flotas_buzons WHERE (flotas_buzons.BUZON_ID = " . $buzon_id . ")";
                $sql_buzflot .= " AND (flotas.ID = flotas_buzons.FLOTA_ID) AND (flotas_buzons.ROL = 'P')";
                $res_buzflot = mysql_query($sql_buzflot) or die("Error en la consulta de flotas pertenecientes al buzón: " . mysql_error());
                $nbuzflot = mysql_num_rows($res_buzflot);
                if ($nbuzflot > 0){
            ?>
                    <table>
                        <tr>
                            <th class="t5c"><?php echo $thacciones; ?></th>
                            <th class="t40p"><?php echo $thflota; ?></th>
                            <th class="t40p"><?php echo $thrango; ?></th>
                        </tr>
                        <?php
                        for ($i = 0; $i < $nbuzflot; $i++){
                            $buzflot = mysql_fetch_array($res_buzflot);
                        ?>
                            <tr <?php if (($i % 2) === 1) {echo "class='filapar'";}?>>
                                <td class="centro">
                                    <a href='#' name="edi-<?php echo $buzflot['ID']; ?>" id='<?php echo $buzflot['ID']; ?>'>
                                        <img src='imagenes/editar.png' alt="<?php echo $botediflo;?>" title="<?php echo $botediflo;?>"></a>
                                    &mdash;
                                    <a href='#' name="del-<?php echo $buzflot['ID']; ?>" id='<?php echo $buzflot['ID']; ?>'>
                                        <img src='imagenes/cancelar.png' alt="<?php echo $botdelflo;?>" title="<?php echo $botdelflo;?>"></a>
                                    &mdash;
                                    <a href='#' name="det-<?php echo $buzflot['IDFLOTA']; ?>" id='<?php echo $buzflot['IDFLOTA']; ?>'>
                                        <img src='imagenes/consulta.png' alt="<?php echo $botdelflo;?>" title="<?php echo $botdelflo;?>">
                                    </a>
                                </td>
                                <td><?php echo $buzflot['FLOTA']; ?></td>
                                <td><?php echo $buzflot['RANGO']; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
            <?php
                }
                else{
            ?>
                    <p class='error'><?php echo $errnoflotpert; ?></p>
            <?php
                }
            ?>
            <h2><?php echo sprintf($h2flotas, $h2asoc); ?></h2>
            <?php
                // Flotas asociadas:
                $sql_buzflot = "SELECT flotas.FLOTA, flotas.ID AS IDFLOTA, flotas_buzons.ROL, flotas_buzons.ID, flotas.RANGO";
                $sql_buzflot .= " FROM flotas, flotas_buzons WHERE (flotas_buzons.BUZON_ID = " . $buzon_id . ")";
                $sql_buzflot .= " AND (flotas.ID = flotas_buzons.FLOTA_ID) AND (flotas_buzons.ROL = 'A')";
                $res_buzflot = mysql_query($sql_buzflot) or die("Error en la consulta de las flotas asociadas al buzón: " . mysql_error());
                $nbuzflot = mysql_num_rows($res_buzflot);
                if ($nbuzflot > 0){
            ?>
                    <table>
                        <tr>
                            <th class="t5c"><?php echo $thacciones; ?></th>
                            <th class="t40p"><?php echo $thflota; ?></th>
                            <th class="t40p"><?php echo $thrango; ?></th>
                        </tr>
                        <?php
                        for ($i = 0; $i < $nbuzflot; $i++){
                            $buzflot = mysql_fetch_array($res_buzflot);
                        ?>
                            <tr <?php if (($i % 2) === 1) {echo "class='filapar'";}?>>
                                <td class="centro">
                                    <a href='#' name="edi-<?php echo $buzflot['ID']; ?>" id='<?php echo $buzflot['ID']; ?>'>
                                        <img src='imagenes/editar.png' alt="<?php echo $botediflo;?>" title="<?php echo $botediflo;?>"></a>
                                    &mdash;
                                    <a href='#' name="del-<?php echo $buzflot['ID']; ?>" id='<?php echo $buzflot['ID']; ?>'>
                                        <img src='imagenes/cancelar.png' alt="<?php echo $botdelflo;?>" title="<?php echo $botdelflo;?>">
                                    </a>
                                </td>
                                <td><?php echo $buzflot['FLOTA']; ?></td>
                                <td><?php echo $buzflot['RANGO']; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
            <?php
                }
                else{
            ?>
                    <p class='error'><?php echo $errnoflotpert; ?></p>
            <?php
                }
            ?>
            <form id="formbuzon" method="POST" action="nuevo_buzflot.php">
                <input type="hidden" name="buzon_id" id="idbuzon" value="<?php echo $buzon_id; ?>">                
                <input type="hidden" name="buzflota_id" id="buzflota_id" value="nada">
                <input type="hidden" name="origen" id="origen" value="nada">
                <table>
                    <tr class="borde">
                        <td class="borde">
                            <input type='image' name='action' src='imagenes/nuevo.png' alt='<?php echo $botadd; ?>' title="<?php echo $botadd; ?>">
                            <br><?php echo $botadd; ?>
                        </td>
                        <td class="borde">
                            <a href='#' id='botedit'>
                                <img src='imagenes/pencil.png' alt='<?php echo $botedit; ?>' title="<?php echo $botedit; ?>">
                            </a>
                            <br><?php echo $botedit; ?>
                        </td>
                        <td class="borde">
                            <a href='#' id='botdel'>
                                <img src='imagenes/no.png' alt='<?php echo $botdel; ?>' title="<?php echo $botdel; ?>">
                            </a>
                            <br><?php echo $botdel; ?>
                        </td>
                        <td class="borde">
                            <a href='buzones.php'>
                                <img src='imagenes/atras.png' alt='<?php echo $botvolver; ?>' title="<?php echo $botvolver; ?>">
                            </a>
                            <br><?php echo $botvolver; ?>
                        </td>
                    </tr>
                </table>
            </form>
            <form id="formflota" name="formflota" method="post" action="detalle_flota.php">
                <input type="hidden" name="idflota" id="idflota" value="nada">
            </form>
<?php
        }
        else{
?>
            <h1><?php echo $h1; ?></h1>
            <p class='error'><?php echo $errnobuzon; ?></p>
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
