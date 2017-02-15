<?php
// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/grupos_$idioma.php";
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
<html lang="es">
    <head>
        <title><?php echo $titulo; ?></title>
        <link rel="StyleSheet" type="text/css" href="estilo.css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <?php
        // Si la sesión de Joomla ha caducado, recargamos la página principal
        if ($flota_usu = 0){
        ?>
            <script type="text/javascript">
                window.top.location.href = "https://intranet.comdes.gva.es/cvcomdes/";
            </script>
        <?php
        }
        ?>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/grupos.js"></script>
    </head>
    <body>
        <?php
        if ($permiso == 2){
            $sql_grupos = "SELECT * FROM grupos WHERE 1";
            // Selección de GISSI;
            if ((isset($gissi)) && ($gissi != "")){
                $sql_grupos .= " AND GISSI LIKE '%" . $gissi . "%'";
            }
            // Selección de MNEMONICO;
            if ((isset($mnemonico)) && ($mnemonico != "")){
                $sql_grupos .= " AND MNEMONICO LIKE '%" . $mnemonico . "%'";
            }
            // Selección de Tipo;
            if ((isset($tipo)) && ($tipo != "NN")){
                $sql_grupos .= " AND TIPO =  '" . $tipo . "'";
            }
            // Número de Página:
            if (!(isset($tampag))){
                $tampag = 20;
            }

            // Número de Página:
            if ((isset($pagina)) && ($pagina != "")){
                $inicio = ($pagina - 1) * $tampag;
            }
            else{
                $inicio = 0;
                $pagina = 1;
            }
            //$sql_grupos .= "SELECT * FROM grupos ORDER BY GSSI ASC";
            $sql_grupos .= " ORDER BY GISSI ASC";
            // Consulta Total
            $res_totgrupos = mysql_query($sql_grupos) or die("Error al buscar todos los grupos: " . mysql_error());
            $ntotgrupos = mysql_num_rows($res_totgrupos);
            $npaginas = ceil($ntotgrupos / $tampag);
            $sql_grupos .= " LIMIT " . $inicio .", " . $tampag . ";";
            $res_grupos = mysql_query($sql_grupos) or die("Error al buscar los grupos: " . mysql_error());
            $ngrupos = mysql_num_rows($res_grupos);
            $sql_tipos = "SELECT DISTINCT TIPO FROM grupos ORDER BY TIPO";
            $res_tipos = mysql_query($sql_tipos) or die("Error al buscar los tipos de grupo: " . mysql_error());
            $ntipos = mysql_num_rows($res_tipos);
        ?>
            <form name="formtab" action="grupos.php" method="POST" target="_blank">
                <h1>
                    <?php echo $h1;?> &mdash;
                    <input type='image' name='action' src='imagenes/newtab.png' alt='<?php echo $bottab;?>' title="<?php echo $bottab;?>">
                </h1>
            </form>
            <?php

            ############# Enlaces para la exportación #######
            $linkpdf = "document.exportar.action='pdfflota.php';document.exportar.submit();";
            $linkxls = "document.exportar.action='xlsflota.php';document.exportar.submit();";

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
            <form name="formgrupos" action="grupos.php" method="post">
                <h4><?php echo $h4crit;?></h4>
                <table>
                    <tr>
                        <td>
                            <label for="inputgissi">GSSI:</label>
                            <input type="text" name="gissi" id="inputgissi" value="<?php echo $gissi;?>">
                            <input type="image" src="imagenes/consulta.png" alt="Buscar" title="Buscar">
                        </td>
                        <td>
                            <label for="inputmnemo"><?php echo $thmnemo;?>:</label>
                            <input type="text" name="mnemonico" id="inputmnemo" value="<?php echo $mnemonico;?>">
                            <input type="image" src="imagenes/consulta.png" alt="Buscar" title="Buscar">
                        </td>
                        <td>
                            <label for="inputtipo"><?php echo $thtipo;?>:</label>
                            <select name="tipo" id="inputtipo">
                                <option value="NN">Seleccionar <?php echo $thtipo;?></option>
                                <?php
                                for ($i = 0; $i < $ntipos; $i++){
                                    $tiposel = mysql_fetch_array($res_tipos);
                                ?>
                                    <option value="<?php echo $tiposel['TIPO'];?>" <?php if($tipo == $tiposel['TIPO']) {echo "selected";} ?>>
                                        <?php echo $tiposel['TIPO'];?>
                                    </option>
                                <?php
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <h4><?php echo $h4res;?></h4>
                <table>
                    <tr>
                        <td class="borde">
                            <?php echo $nreg; ?> <?php echo ($inicio + 1); ?> a <?php echo ($inicio + $tampag); ?> de <b><?php echo $ntotgrupos; ?></b>.
                        </td>
                        <?php
                        if ($ngrupos > 0){
                        ?>
                            <td class="borde">
                                Mostrar:
                                <select name="tampag">
                                    <option value='20' <?php if (($tampag == "20") || ($tampag == "")) {echo 'selected';}?>>20</option>
                                    <option value='50' <?php if ($tampag == "50") {echo 'selected';}?>>50</option>
                                    <option value='100' <?php if ($tampag == "100") {echo 'selected';}?>>100</option>
                                    <option value='<?php echo $ntotgrupos;?>' <?php if ($tampag == $ntotgrupos) {echo 'selected';}?>>Todos</option>
                                </select> <?php echo $regpag; ?>
                            </td>
                        <?php
                        }
                        if ($npaginas > 1){
                        ?>
                            <td class="borde">
                                <?php echo $txtpag; ?>:
                                <select name="pagina">
                                <?php
                                for ($i = 1; $i <= $npaginas; $i++){
                                ?>
                                    <option value="<?php echo $i;?>" <?php if ($pagina == $i) {echo 'selected';} ?>>
                                        <?php echo $i;?>
                                    </option>
                                <?php
                                }
                                ?>
                                </select> de <b><?php echo $npaginas; ?></b>
                            </td>
                        <?php
                        }
                        ?>
                        <td class="borde">
                            <a href="nuevo_grupo.php" title="<?php echo $newgrupo;?>">
                                <img src="imagenes/nueva.png" alt="<?php echo $newgrupo; ?>"></a>
                            &mdash;
                            <a href="grupos.php" title="<?php echo $botreset;?>">
                                <img src="imagenes/update.png" alt="<?php echo $botreset; ?>"></a>
                            <?php
                            if ($ngrupos > 0){
                            ?>
                                &mdash;
                                <a href="xlsgrupos.php" title="Exportar a Excel" target="_blank">
                                    <img src="imagenes/xls.png" alt="Exportar a Excel"></a>
                                &mdash;
                                <a href="pdfgrupos.php" title="Exportar a PDF" target="_blank">
                                    <img src="imagenes/pdf.png" alt="Exportar a PDF"></a>
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </form>
            <?php
            if ($ngrupos > 0){
            ?>
                <form name="formaccion" id="formaccion" action="detalle_grupo.php" method="post">
                    <input type="hidden" name="gissi" id="gissi" value="0">
                </form>
                <table>
                    <tr>
                        <th><?php echo $thacciones;?></th>
                        <th>GSSI</th>
                        <th><?php echo $thmnemo;?></th>
                        <th><?php echo $thtipo;?></th>
                        <th><?php echo $thflotas;?></th>
                    </tr>
                    <?php
                    for ($i = 0; $i < $ngrupos; $i++){
                        $row_grupo = mysql_fetch_array($res_grupos);
                        $sql_flotas = "SELECT * FROM grupos_flotas WHERE (GISSI = " . $row_grupo['GISSI'] . ")";
                        $res_flotas = mysql_query($sql_flotas) or die("Error al buscar las flotas: " . mysql_error());
                        $nflotas = mysql_num_rows($res_flotas);
                    ?>
                        <tr <?php if(($i % 2) == 1){echo "class='filapar'";} ?>>
                            <td class="centro">
                                <a href="#" id="det-<?php echo $row_grupo['GISSI'];?>">
                                    <img src="imagenes/consulta.png" title="<?php echo $botdet;?>"></a>&mdash;
                                <a href="#" id="edi-<?php echo $row_grupo['GISSI'];?>">
                                    <img src="imagenes/editar.png" title="<?php echo $botedi;?>"></a>&mdash;
                                <a href="#" id="del-<?php echo $row_grupo['GISSI'];?>">
                                    <img src="imagenes/cancelar.png" title="<?php echo $botdel;?>"></a>
                            </td>
                            <td class="centro"><?php echo $row_grupo['GISSI'];?></td>
                            <td><?php echo $row_grupo['MNEMONICO'];?></td>
                            <td><?php echo $row_grupo['TIPO'];?></td>
                            <td class="centro"><?php echo $nflotas;?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            <?php
            }
            else{
            ?>
                <p class='error'><?php echo $errnogrupos; ?></p>
            <?php
            }
            ?>

        <?php
        }
        else{
        ?>
            <h1><?php echo $h1perm; ?></h1>
            <p class='error'><?php echo $errnoperm; ?></p>
        <?php
        }
        ?>
    </body>
</html>
