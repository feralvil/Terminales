<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/organizacon_$idioma.php";
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
        <title><?php echo $titedi; ?></title>
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
        <form name="contorganiza" action="contactos_organiza.php" method="POST">
            <input type="hidden" name="idorg" value="<?php echo $idorg; ?>">
        </form>
        <?php
        if ($permiso == 2) {
            //datos de la tabla Flotas
            $roltxt = $rolestxt[$rol];
            $sql_org = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
            $res_org = mysql_query($sql_org) or die("Error en la consulta de Flota: " . mysql_error());
            $norg = mysql_num_rows($res_org);
            if ($norg > 0) {
                $organiza = mysql_fetch_array($res_org);
        ?>    
            <h1><?php echo $h1del; ?> <?php echo $organiza['ORGANIZACION']; ?></h1>
            <form name="updatecont" action="update_contorg.php" method="post">
                <input type="hidden" name="origen" value="borrar">
                <input type="hidden" name="idorg" value="<?php echo $idorg;?>">
                <input type="hidden" name="idcont" value="<?php echo $idcont;?>">
            <?php
                $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $idcont;
                $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de Contacto: " . mysql_error());
                $ncontacto = mysql_num_rows($res_contacto);
                if ($ncontacto > 0){
                    $contacto = mysql_fetch_array($res_contacto);
            ?>
                    <div class="centro">
                        <p><img src='imagenes/important.png' alt='Error' title="Error"></p>
                        <p class="error">
                            <strong>
                                <?php 
                                echo sprintf($txtmens, $contacto['NOMBRE']) . ' ' . $organiza['ORGANIZACION'];
                                ?>                                
                            </strong>
                            <br>
                            <?php echo $nota;?>
                        </p>
                    </div>
            <?php
                }
                else{
            ?>
                    <p class="error"><?php echo $errnocont; ?></p>    
        <?php
                }
            }
            else{
        ?>
                <p class="error"><?php echo $errnorg; ?></p>
        <?php
            }
        ?>
            <table>
                <tr>
                    <td class="borde">
                        <a href="#" onclick="document.contorganiza.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $botcontactos;?>' title="<?php echo $botcontactos;?>">
                        </a><br><?php echo $botcontactos;?>
                    </td>
                    <td class="borde">
                        <input type="image" src="imagenes/no.png" alt='<?php echo $boteliminar;?>' title="<?php echo $boteliminar;?>">
                        <br><?php echo $boteliminar;?>
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