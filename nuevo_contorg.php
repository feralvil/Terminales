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
        <title><?php echo $titadd; ?></title>
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
        <form name="contflota" action="contactos_organiza.php" method="POST">
            <input type="hidden" name="idorg" value="<?php echo $idorg; ?>">
        </form>
        <?php
        if ($permiso == 2) {
            //datos de la tabla Flotas
           //datos de la tabla Flotas
            $roltxt = $rolestxt[$rol];
            $sql_org = "SELECT * FROM organizaciones WHERE ID = " . $idorg;
            $res_org = mysql_query($sql_org) or die("Error en la consulta de Flota: " . mysql_error());
            $norg = mysql_num_rows($res_org);
            if ($norg > 0) {
                $organiza = mysql_fetch_array($res_org);
        ?>    
            <h1><?php echo $h1add;?> <?php echo $organiza['ORGANIZACION']; ?>)</h1>
            <h2><?php echo $h2contexist; ?></h2>
            <form name="updateexist" action="update_contorg.php" method="post">
                <input type="hidden" name="origen" value="addexist">
                <input type="hidden" name="idorg" value="<?php echo $idorg;?>">
                <h3><?php echo $h3exist; ?></h3>
            <?php
                $sql_contactos = "SELECT * FROM contactos ORDER BY NOMBRE ASC";
                $res_contactos = mysql_query($sql_contactos) or die("Error en la consulta de Contactos: " . mysql_error());
                $ncontactos = mysql_num_rows($res_contactos);
                if ($ncontactos > 0){                    
            ?>               
                    <select name="idcont">
                        <option value="0">Seleccionar</option>
                        <?php
                        for ($i = 0; $i < $ncontactos; $i++){
                            $contacto = mysql_fetch_array($res_contactos);
                        ?>
                        <option value="<?php echo $contacto['ID'];?>">
                                <?php echo $contacto['NOMBRE'];?>
                        </option>
                        <?php
                        }
                        ?>
                    </select>
                    <?php                        
                    if ($rol == "Nada"){
                    ?>
                        <h3><?php echo $h3rol; ?></h3>
                        <select name="rol" id="selrol">
                        <?php
                        foreach ($rolesnew as $rolv => $roltxt){                                        
                        ?>
                            <option value="<?php echo $rolv;?>"><?php echo $roltxt;?></option>
                        <?php
                        }
                        ?>
                        </select>                
                    <?php
                    }
                    else{
                    ?>
                        <input type="hidden" name="rol" value="<?php echo $rol;?>">                            
                    <?php
                    }
                    ?>
            <?php
                }
                else{
            ?>
                    <p class="error"><?php echo $errnocontexist; ?></p>
            <?php
                }
            ?>
            <table>
                <tr>
                    <td class="borde">
                        <a href="#" onclick="document.contflota.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $botcontactos;?>' title="<?php echo $botcontactos;?>">
                        </a><br><?php echo $botcontactos;?>
                    </td>
                    <td class="borde">
                        <input type="image" src="imagenes/guardar.png" alt='<?php echo $botguardar;?>' title="<?php echo $botguardar;?>">
                        <br><?php echo $botguardar;?>
                    </td>
                </tr>
            </table>
            </form>
            <h2><?php echo $h2contnew; ?></h2>
            <form name="updatenew" action="update_contorg.php" method="post">
                <input type="hidden" name="origen" value="addnew">
                <input type="hidden" name="idorg" value="<?php echo $idorg;?>">
                <h3><?php echo $h3cont; ?></h3>
                <table>
                    <tr>
                        <th class="t4c"><?php echo $thnombre; ?></th>
                        <th class="t10c">DNI</th>
                        <th class="t4c"><?php echo $thcargo; ?></th>
                        <th class="t5c"><?php echo $thmail; ?></th>
                        <th class="t5c"><?php echo $thtelef; ?></th>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="nombre" value="" size="30">
                        </td>
                        <td>
                            <input type="text" name="nif" value="" size="10">
                        </td>
                        <td>
                            <input type="text" name="cargo" value="" size="30">
                        </td>
                        <td>
                            <input type="text" name="mail" value="" size="30">
                        </td>
                        <td>
                            <input type="text" name="telefono" value="" size="20">
                        </td>
                    </tr>
                </table>
        <?php
            }
            else{
        ?>
                <p class="error"><?php echo $errnoflota; ?></p>
        <?php
            }
        ?>
            <table>
                <tr>
                    <td class="borde">
                        <a href="#" onclick="document.contflota.submit();">
                            <img src='imagenes/atras.png' alt='<?php echo $botcontactos;?>' title="<?php echo $botcontactos;?>">
                        </a><br><?php echo $botcontactos;?>
                    </td>
                    <td class="borde">
                        <input type="image" src="imagenes/guardar.png" alt='<?php echo $botguardar;?>' title="<?php echo $botguardar;?>">
                        <br><?php echo $botguardar;?>
                    </td>
                    <td class="borde">
                        <a href="#" onclick="document.updatecont.reset();">
                            <img src='imagenes/no.png' alt='<?php echo $botcancel;?>' title="<?php echo $botcancel;?>">
                        </a><br><?php echo $botcancel;?>
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