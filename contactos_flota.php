<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/flotacon_$idioma.php";
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
    </head>
    <body>
<?php
if ($permiso == 2) {
    //datos de la tabla Flotas
    $sql_flota = "SELECT * FROM flotas WHERE ID = " . $idflota;
    $res_flota = mysql_query($sql_flota) or die("Error en la consulta de Flota: " . mysql_error());
    $nflota = mysql_num_rows($res_flota);
    if ($nflota > 0) {
        $row_flota = mysql_fetch_array($res_flota);
        // Datos de la Tabla Municipios:
        $sql_muni = "SELECT * FROM municipios WHERE INE = " . $row_flota['INE'];
        $res_muni = mysql_query($sql_muni) or die("Error en la consulta de Municipio: " . mysql_error());
        $nmuni = mysql_num_rows($res_muni);
        if ($nmuni > 0){
            $municipio = mysql_fetch_array($res_muni);
        }
        // Datos de la Tabla Organizaciones:
        $sql_org = "SELECT * FROM organizaciones WHERE ID = " . $row_flota['ORGANIZACION'];
        $res_org = mysql_query($sql_org) or die("Error en la consulta de Organización: " . mysql_error());
        $norg = mysql_num_rows($res_org);
        if ($norg > 0){
            $organiza = mysql_fetch_array($res_org);
        }
        // Datos de contactos
        $sql_contactos = "SELECT * FROM contactos_flotas WHERE FLOTA_ID = $idflota";
        $res_contactos = mysql_query($sql_contactos) or die("Error en la consulta de contacto: " . mysql_error());
        $ncontactos = mysql_num_rows($res_contactos);
        if ($ncontactos > 0){
            $responsable = array();
            $operativos = array();
            $tecnicos = array();
            $cont24h = array();
            for ($i = 0; $i < $ncontactos; $i++){
                $row_cont = mysql_fetch_array($res_contactos);
                switch ($row_cont['ROL']){
                    case 'RESPONSABLE':{
                        $responsable[$row_cont['ID']] = $row_cont['CONTACTO_ID'];
                        break;
                    }
                    case 'OPERATIVO':{
                        $operativos[$row_cont['ID']] = $row_cont['CONTACTO_ID'];
                        break;
                    }
                    case 'TECNICO':{
                        $tecnicos[$row_cont['ID']] = $row_cont['CONTACTO_ID'];
                        break;
                    }
                    case 'CONT24H':{
                        $cont24h[$row_cont['ID']] = $row_cont['CONTACTO_ID'];
                        break;
                    }
                }
            }
        }
    }
    if ($nflota > 0) {
?>
        <h1>Flota <?php echo $row_flota["FLOTA"]; ?> (<?php echo $row_flota["ACRONIMO"]; ?>)</h1>
        <table>
            <tr>
                <th class="t5c"><?php echo $thdomicilio; ?></th>
                <td class="t40p"><?php echo $row_flota["DOMICILIO"]; ?></td>
                <th class="t5c"><?php echo $thcp; ?></th>
                <td class="t5c"><?php echo $row_flota["CP"]; ?></td>
            </tr>
            <tr class="filapar">
                <th class="t5c"><?php echo $thciudad; ?></th>
                <td class="t40p"><?php echo $municipio["MUNICIPIO"]; ?></td>
                <th class="t5c"><?php echo $thprovincia; ?></th>
                <td class="t5c"><?php echo $municipio["PROVINCIA"]; ?></td>
            </tr>
        </table>
        <h2><?php echo $h2organiza; ?></h2>
        <table>
            <tr>
                <th><?php echo $thorganiza; ?></th>
                <td><?php echo $organiza['ORGANIZACION']; ?></td>
            </tr>
        </table>
        <form name="addcont" action="nuevo_contacto.php" method="post">
            <input type="hidden" name="idflota" value="<?php echo $idflota;?>">
            <input type="hidden" name="rol" value="Nada">
        </form>
        <h2>
            <?php echo $h2cont; ?> &mdash;
            <a href="#" onclick="document.addcont.submit();">
                <img src="imagenes/nueva.png" alt="<?php echo $botadd;?>" title="<?php echo $botadd;?>">
            </a>
        </h2>
        <?php
        if ($ncontactos > 0){
        ?>
            <form name="edicont" action="editar_contacto.php" method="post">
                <input type="hidden" name="idflota" value="<?php echo $idflota;?>">
                <input type="hidden" name="idcont" value="0">
                <input type="hidden" name="idcf" value="0">
                <input type="hidden" name="rol" value="Nada">
            </form>
            <form name="delcont" action="eliminar_contacto.php" method="post">
                <input type="hidden" name="idflota" value="<?php echo $idflota;?>">
                <input type="hidden" name="idcont" value="0">
                <input type="hidden" name="idcf" value="0">
                <input type="hidden" name="rol" value="Nada">
            </form>
            <h3>
                <?php echo $h3resp; ?>&mdash;
                <a href="#" onclick="document.addcont.rol.value='RESPONSABLE';document.addcont.submit();">
                    <img src="imagenes/nueva.png" alt="<?php echo $botadd;?>" title="<?php echo $botadd;?>">
                </a>
            </h3>
            <?php
            if (count($responsable) > 0){
                $i = 0;
            ?>
                <table>
                    <tr>
                        <th class="t5c"><?php echo $nomflota; ?></th>
                        <th class="t10c">DNI</th>
                        <th class="t5c"><?php echo $thcargo; ?></th>
                        <th class="t5c"><?php echo $thmail; ?></th>
                        <th class="t5c"><?php echo $thtelef; ?></th>
                        <th class="t10c"><?php echo $thacciones; ?></th>
                    </tr>
            <?php
                foreach ($responsable as $idcf => $idresp){
                    $sql_contacto = "SELECT * FROM contactos WHERE ID = " . $idresp;
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de responsable: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                    if ($ncontacto > 0){
                        $contacto = mysql_fetch_array($res_contacto);
                        $linkedi = "document.edicont.idcont.value=$idresp;document.edicont.idcf.value=$idcf;";
                        $linkedi .= "document.edicont.rol.value='RES';document.edicont.submit();";
                        $linkdel = "document.delcont.idcont.value=$idresp;document.delcont.idcf.value=$idcf;";
                        $linkdel .= "document.delcont.rol.value='RES';document.delcont.submit();";
                    }
            ?>
                    <tr>
                        <td><?php echo $contacto['NOMBRE']; ?></td>
                        <td><?php echo $contacto['NIF']; ?></td>
                        <td><?php echo $contacto['CARGO']; ?></td>
                        <td><?php echo $contacto['MAIL']; ?></td>
                        <td><?php echo $contacto['TELEFONO']; ?></td>
                        <td class="centro">
                            <a href="#" onclick="<?php echo $linkedi; ?>">
                                <img src="imagenes/editar.png" alt="Editar" title="Editar"></a>
                            &mdash;
                            <a href="#" onclick="<?php echo $linkdel; ?>">
                                <img src="imagenes/cancelar.png" alt="Eliminar" title="Eliminar">
                            </a>
                        </td>
                    </tr>
            <?php
                }
            ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class='error'><?php echo $errnoresp; ?>
            <?php
            }
            ?>
            <h3>
                <?php echo $h3opera; ?> &mdash;
                <a href="#" onclick="document.addcont.rol.value='OPERATIVO';document.addcont.submit();">
                    <img src="imagenes/nueva.png" alt="<?php echo $botadd;?>" title="<?php echo $botadd;?>">
                </a>
            </h3>
            <?php
            if (count($operativos) > 0){
                $i = 0;
            ?>
                <table>
                    <tr>
                        <th class="t5c"><?php echo $nomflota; ?></th>
                        <th class="t10c">DNI</th>
                        <th class="t5c"><?php echo $thcargo; ?></th>
                        <th class="t5c"><?php echo $thmail; ?></th>
                        <th class="t5c"><?php echo $thtelef; ?></th>
                        <th class="t10c"><?php echo $thacciones; ?></th>
                    </tr>
                <?php                
                foreach ($operativos as $idcf => $idop){
                    $sql_contacto = "SELECT * FROM contactos WHERE ID = $idop";
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de responsable: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                    if ($ncontacto > 0){
                        $contacto = mysql_fetch_array($res_contacto);
                        $linkedi = "document.edicont.idcont.value=$idop;document.edicont.idcf.value=$idcf;";
                        $linkedi .= "document.edicont.rol.value='OPE';document.edicont.submit();";
                        $linkdel = "document.delcont.idcont.value=$idop;document.delcont.idcf.value=$idcf;";
                        $linkdel .= "document.delcont.rol.value='OPE';document.delcont.submit();";
                    }
                ?>
                    <tr <?php if(($i % 2) == 1) {echo 'class="filapar"';}?>>
                        <td><?php echo $contacto['NOMBRE']; ?></td>
                        <td><?php echo $contacto['NIF']; ?></td>
                        <td><?php echo $contacto['CARGO']; ?></td>
                        <td><?php echo $contacto['MAIL']; ?></td>
                        <td><?php echo $contacto['TELEFONO']; ?></td>
                        <td class="centro">
                            <a href="#" onclick="<?php echo $linkedi; ?>">
                                <img src="imagenes/editar.png" alt="Editar" title="Editar"></a>
                            &mdash;
                            <a href="#" onclick="<?php echo $linkdel; ?>">
                                <img src="imagenes/cancelar.png" alt="Eliminar" title="Eliminar">
                            </a>
                        </td>
                    </tr>
                <?php
                    $i++;
                }
                ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class='error'><?php echo $errnoop; ?>
            <?php
            }            
            ?>
            <h3>
                <?php echo $h3tecnico; ?> &mdash;
                <a href="#" onclick="document.addcont.rol.value='TECNICO';document.addcont.submit();">
                    <img src="imagenes/nueva.png" alt="<?php echo $botadd;?>" title="<?php echo $botadd;?>">
                </a>
            </h3>
            <?php
            if (count($tecnicos) > 0){
                $i = 0;
            ?>
                <table>
                    <tr>
                        <th class="t5c"><?php echo $nomflota; ?></th>
                        <th class="t10c">DNI</th>
                        <th class="t5c"><?php echo $thcargo; ?></th>
                        <th class="t5c"><?php echo $thmail; ?></th>
                        <th class="t5c"><?php echo $thtelef; ?></th>
                        <th class="t10c"><?php echo $thacciones; ?></th>
                    </tr>
                <?php                
                foreach ($tecnicos as $idcf => $idtec){
                    $sql_contacto = "SELECT * FROM contactos WHERE ID = $idtec";
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de responsable: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                    if ($ncontacto > 0){
                        $contacto = mysql_fetch_array($res_contacto);
                        $linkedi = "document.edicont.idcont.value=$idtec;document.edicont.idcf.value=$idcf;";
                        $linkedi .= "document.edicont.rol.value='TEC';document.edicont.submit();";
                        $linkdel = "document.delcont.idcont.value=$idtec;document.delcont.idcf.value=$idcf;";
                        $linkdel .= "document.delcont.rol.value='TEC';document.delcont.submit();";
                    }
                ?>
                    <tr <?php if(($i % 2) == 1) {echo 'class="filapar"';}?>>
                        <td><?php echo $contacto['NOMBRE']; ?></td>
                        <td><?php echo $contacto['NIF']; ?></td>
                        <td><?php echo $contacto['CARGO']; ?></td>
                        <td><?php echo $contacto['MAIL']; ?></td>
                        <td><?php echo $contacto['TELEFONO']; ?></td>
                        <td class="centro">
                            <a href="#" onclick="<?php echo $linkedi; ?>">
                                <img src="imagenes/editar.png" alt="Editar" title="Editar"></a>
                            &mdash;
                            <a href="#" onclick="<?php echo $linkdel; ?>">
                                <img src="imagenes/cancelar.png" alt="Eliminar" title="Eliminar">
                            </a>
                        </td>
                    </tr>
                <?php
                    $i++;
                }
                ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class='error'><?php echo $errnotec; ?>
            <?php
            }            
            ?>
            <h3>
                <?php echo $h3cont24h; ?> &mdash;
                <a href="#" onclick="document.addcont.rol.value='CONT24H';document.addcont.submit();">
                    <img src="imagenes/nueva.png" alt="<?php echo $botadd;?>" title="<?php echo $botadd;?>">
                </a>
            </h3>
            <?php
            if (count($cont24h) > 0){
                $i = 0;
            ?>
                <table>
                    <tr>
                        <th class="t5c"><?php echo $nomflota; ?></th>
                        <th class="t10c">DNI</th>
                        <th class="t5c"><?php echo $thcargo; ?></th>
                        <th class="t5c"><?php echo $thmail; ?></th>
                        <th class="t5c"><?php echo $thtelef; ?></th>
                        <th class="t10c"><?php echo $thacciones; ?></th>
                    </tr>
                <?php                
                foreach ($cont24h as $idcf => $id24){
                    $sql_contacto = "SELECT * FROM contactos WHERE ID = $id24";
                    $res_contacto = mysql_query($sql_contacto) or die("Error en la consulta de responsable: " . mysql_error());
                    $ncontacto = mysql_num_rows($res_contacto);
                    if ($ncontacto > 0){
                        $contacto = mysql_fetch_array($res_contacto);
                        $linkedi = "document.edicont.idcont.value=$id24;document.edicont.idcf.value=$idcf;";
                        $linkedi .= "document.edicont.rol.value='CON';document.edicont.submit();";
                        $linkdel = "document.delcont.idcont.value=$id24;document.delcont.idcf.value=$idcf;";
                        $linkdel .= "document.delcont.rol.value='CON';document.delcont.submit();";
                    }
                ?>
                    <tr <?php if(($i % 2) == 1) {echo 'class="filapar"';}?>>
                        <td><?php echo $contacto['NOMBRE']; ?></td>
                        <td><?php echo $contacto['NIF']; ?></td>
                        <td><?php echo $contacto['CARGO']; ?></td>
                        <td><?php echo $contacto['MAIL']; ?></td>
                        <td><?php echo $contacto['TELEFONO']; ?></td>
                        <td class="centro">
                            <a href="#" onclick="<?php echo $linkedi; ?>">
                                <img src="imagenes/editar.png" alt="Editar" title="Editar"></a>
                            &mdash;
                            <a href="#" onclick="<?php echo $linkdel; ?>">
                                <img src="imagenes/cancelar.png" alt="Eliminar" title="Eliminar">
                            </a>
                        </td>
                    </tr>
                <?php
                    $i++;
                }
                ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class='error'><?php echo $errno24h; ?>
            <?php
            }
        }
        else{
        ?>
            <p class='error'><?php echo $errnocont; ?>
        <?php
        }
        ?>
<?php
    }
    else{
?>
        <p class='error'><?php echo $errnoflota; ?></p>
<?php
    }
?>
            <input type="hidden" name="detalle" value="">
            <input type="hidden" name="editar" value="">
            <input type="hidden" name="borrar" value="">
            <input type="hidden" name="nuevo" value="">
        </form>
        <table>
            <tr>
                <td class="borde">
                    <a href="#" onclick="document.detflota.submit();">
                        <img src='imagenes/atras.png' alt='<?php echo $volver;?>' title="<?php echo $volver;?>">
                    </a><br><?php echo $detalle." de Flota";?>
                </td>
                <td class="borde">
                    <a href="#" onclick="document.excflota.submit();">
                        <img src='imagenes/impexcel.png' alt='<?php echo $datexcel;?>' title="<?php echo $datexcel;?>">
                    </a><br><?php echo $datexcel;?>
                </td>
            </tr>
        </table>
        <form name="detflota" action="detalle_flota.php" method="POST">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
        </form>
        <form name="excflota" action="excel_flota.php" method="POST">
            <input type="hidden" name="accion" value="ACTCONT">
            <input type="hidden" name="idflota" value="<?php echo $idflota; ?>">
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