<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotabuz_$idioma.php";
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
        <script type="text/javascript" src="js/jquery.js"></script>
            <!-- Funciones JQUERY -->
            <script type="text/javascript">
                $(function(){
                    // Botón Nuevo:
                    $("a#newbuzon").click(function(){
                        $("form#buzonflota").submit();
                    });
                    // Botón Detalle Buzon:
                    $("a[name^=det]").click(function(){
                        var idbuzon = $(this).attr('id');
                        $("input#idbuzon").val(idbuzon);
                        $("form#buzonflota").attr('action', 'detalle_buzon.php');
                        $("form#buzonflota").submit();
                    });
                    // Botón editar:
                    $("a[name^=edi]").click(function(){
                        var idbuzon = $(this).attr('id');
                        $("input#idbuzon").val(idbuzon);
                        $("form#buzonflota").attr('action', 'editar_flotabuz.php');
                        $("form#buzonflota").submit();
                    });
                    // Botón borrar:
                    $("a[name^=del]").click(function(){
                        var idbuzon = $(this).attr('id');
                        $("input#idbuzon").val(idbuzon);
                        var confirmar = confirm("<?php echo $confborrar; ?>");
                        if (confirmar == true){
                            $("input#origen").val('borrar');
                            $("form#buzonflota").attr('action', 'update_buzon.php');
                            $("form#buzonflota").submit();
                        }
                        
                    });
                });
            </script>        
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
        <p class='error'><?php echo $errnflota; ?></p>
<?php
    }
    else {
        $row_flota = mysql_fetch_array($res_flota);
        $usuario = $row_flota["LOGIN"];
    }
    // Buzones como propietaria
    $sql_prop = "SELECT flotas_buzons.ID AS ID, buzons.ID AS BUZON_ID, buzons.NOMBRE ";
    $sql_prop .= "FROM flotas_buzons, buzons WHERE (flotas_buzons.BUZON_ID = buzons.ID) ";
    $sql_prop .= "AND (flotas_buzons.FLOTA_ID = '$idflota') AND (flotas_buzons.ROL = 'P')";
    $res_prop = mysql_query($sql_prop) or die("Error en la consulta de Terminales" . mysql_error());
    $nprop = mysql_num_rows($res_prop);
    // Buzones como asociada
    $sql_asoc = "SELECT flotas_buzons.ID AS ID, buzons.ID AS BUZON_ID, buzons.NOMBRE ";
    $sql_asoc .= "FROM flotas_buzons, buzons WHERE (flotas_buzons.BUZON_ID = buzons.ID) ";
    $sql_asoc .= "AND (flotas_buzons.FLOTA_ID = '$idflota') AND (flotas_buzons.ROL = 'A')";
    $res_asoc = mysql_query($sql_asoc) or die("Error en la consulta de Terminales" . mysql_error());
    $nasoc = mysql_num_rows($res_asoc);
    //datos de la tabla Municipio
    // INE
    $ine = $row_flota["INE"];
    $sql_mun = "SELECT * FROM municipios WHERE INE='$ine'";
    $res_mun = mysql_query($sql_mun) or die("Error en la consulta de Municipio" . mysql_error());
    $nmun = mysql_num_rows($res_mun);
    if ($nmun == 0) {
?>
        <p class='error'><?php echo $errnomun; ?></p>
<?php
    }
    else {
        $row_mun = mysql_fetch_array($res_mun);
        $municipio = $row_mun["MUNICIPIO"];
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
        <h1>Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
        <h2><?php echo $h2admin; ?></h2>
        <table>
            <tr>
                <th class="t40p"><?php echo $nomflota; ?></th>
                <th class="t5c"><?php echo $acroflota; ?></th>
                <th class="t5c"><?php echo $ciudad; ?></th>
                <th class="t5c"><?php echo $provincia; ?></th>
            </tr>
            <tr>
                <td><?php echo $row_flota["FLOTA"]; ?></td>
                <td><?php echo $row_flota["ACRONIMO"]; ?></td>
                <td><?php echo $row_mun["MUNICIPIO"]; ?></td>
                <td><?php echo $row_mun["PROVINCIA"]; ?></td>
            </tr>
        </table>
        <form name="buzonflota" id="buzonflota" method="POST" action="nuevo_flotabuz.php">
            <input type="hidden" name="buzon_id" id="idbuzon" value="0">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <h2><?php echo $h2clientes; ?></h2>
            <h3><?php echo $h3prop; ?></h3>
            <?php
            if ($nprop > 0){
            ?>
                <table>
                    <tr>
                        <th class="t5c"><?php echo $thacc; ?></th>
                        <th class="t5c"><?php echo $thbuzon; ?></th>
                    </tr>
                <?php
                for ($i=0 ; $i < $nprop ; $i++ ){
                    $buzon = mysql_fetch_array($res_prop);                    
                ?>
                    <tr <?php if (($i % 2) > 0) {echo "class = 'filapar'";}?>>
                        <td class="centro">
                            <a href='#' name="edi-<?php echo $buzon["ID"]; ?>" id='<?php echo $buzon["ID"]; ?>'>
                                <img src='imagenes/editar.png' alt="<?php echo $botedi;?>" title="<?php echo $botedi;?>"></a>
                            &mdash;
                            <a href='#' name="del-<?php echo $buzon["ID"]; ?>" id='<?php echo $buzon["ID"]; ?>'>
                                <img src='imagenes/cancelar.png' alt="<?php echo $botborrar;?>" title="<?php echo $botborrar;?>"></a>
                            &mdash;
                            <a href='#' name="det-<?php echo $buzon["BUZON_ID"]; ?>" id='<?php echo $buzon["BUZON_ID"]; ?>'>
                                <img src='imagenes/consulta.png' alt="<?php echo $botdet;?>" title="<?php echo $botdet;?>">
                            </a>                            
                        </td>
                        <td class="centro"><?php echo $buzon["NOMBRE"]; ?></td>
                    </tr>
                <?php
                }
                ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class="error">Error: <strong><?php echo $errnoprop; ?></strong></p>
            <?php
            }
            ?>
            <h3><?php echo $h3asoc; ?></h3>
            <?php
            if ($nasoc > 0){
            ?>
                <table>
                    <tr>
                        <th class="t5c"><?php echo $thacc; ?></th>
                        <th class="t5c"><?php echo $thbuzon; ?></th>
                    </tr>
                <?php
                for ($i=0 ; $i < $nasoc ; $i++ ){
                    $buzon = mysql_fetch_array($res_asoc);
                ?>
                    <tr <?php if (($i % 2) > 0) {echo "class = 'filapar'";}?>>
                        <td class="centro">
                            <a href='#' name="edi-<?php echo $buzon["ID"]; ?>" id='<?php echo $buzon["ID"]; ?>'>
                                <img src='imagenes/editar.png' alt="<?php echo $botedi;?>" title="<?php echo $botedi;?>"></a>
                            &mdash;
                            <a href='#' name="del-<?php echo $buzon["ID"]; ?>" id='<?php echo $buzon["ID"]; ?>'>
                                <img src='imagenes/cancelar.png' alt="<?php echo $botborrar;?>" title="<?php echo $botborrar;?>"></a>
                            &mdash;
                            <a href='#' name="det-<?php echo $buzon["BUZON_ID"]; ?>" id='<?php echo $buzon["BUZON_ID"]; ?>'>
                                <img src='imagenes/consulta.png' alt="<?php echo $botdet;?>" title="<?php echo $botdet;?>">
                            </a>                            
                        </td>
                        <td class="centro"><?php echo $buzon["NOMBRE"]; ?></td>
                    </tr>
                <?php
                }
                ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class="error">Error: <strong><?php echo $errnoasoc; ?></strong></p>
            <?php
            }
            ?>
        </form>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
            <table>
                <tr>
                    <td class="borde">
                        <a href="#" id="newbuzon">
                            <img src="imagenes/nuevo.png" alt="<?php echo $botnuevo; ?>" title="<?php echo $botnuevo; ?>" />
                        </a>
                        <br><?php echo $botnuevo; ?> a Flota
                    </td>
                    <td class="borde">
                        <input type='image' name='action' src='imagenes/atras.png' alt='<?php echo $detalle; ?>' title="<?php echo $detalle; ?>"><br><?php echo $detalle; ?> de Flota
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