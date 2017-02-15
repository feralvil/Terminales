<?php

/*
 * Consulta de la tabla de contactos con AJAX
 * 
 */

// ------------ Obtención del usuario Joomla! --------------------------------------- //
// Le decimos que estamos en Joomla
define('_JEXEC', 1);

// Definimos la constante de directorio actual y el separador de directorios (windows server: \ y linux server: /)
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__) . DS . '..');

// Cargamos los ficheros de framework de Joomla 1.5, y las definiciones de constantes (IMPORTANTE AMBAS LÍNEAS)
require_once ( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );
require_once ( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );

// Iniciamos nuestra aplicación (site: frontend)
$mainframe = & JFactory::getApplication('site');

// Obtenemos el idioma de la cookie de JoomFish
$idioma = $_COOKIE['jfcookie']['lang'];
$lang = "idioma/actterm_$idioma.php";
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


// Importamos las variables del Formulario
import_request_variables("gp", "");

$sql_term = "SELECT * FROM terminales WHERE ID = '$idterm' ORDER BY ISSI ASC";
$res_term = mysql_query($sql_term) or die("Error en la consulta de Terminal" . mysql_error());
$nterm = mysql_num_rows($res_term);
?>
<h3><?php echo $h3acttmod; ?></h3>
<?php
if ($nterm == 0){
?>
    <p class="error"><?php echo $errnoterm; ?></p>
    <table>
        <tr>
            <td class="borde">
                <a href='#' id="cancelar"><img src='imagenes/no.png' alt='<?php echo $cancelar; ?>' title='<?php echo $cancelar; ?>'></a><br><?php echo $cancelar; ?>
            </td>
        </tr>
    </table>
<?php
}
else{
    $row_term = mysql_fetch_array($res_term);
?>
    <form name="datosterm" id="datosterm" action="sqlacttupd.php" method="POST">
        <input type="hidden" name="origen" value="nueva" />
        <input type="hidden" name="idactuacion" value="<?php echo $idactuacion; ?>" />
        <input type="hidden" name="tipoact" value="MOD" />
        <input type="hidden" name="origen" id="origen" value="nueva" />
        <input type="hidden" name="idterm" value="<?php echo $idterm; ?>" />
        <table>
            <tr>
                <th><?php echo $provtxt; ?></th>
                <td>
                    <?php echo $row_term["PROVEEDOR"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="proveedor" size="25" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
                <th><?php echo $tipotxt; ?></th>
                <td>
                    <?php echo $row_term["TIPO"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="hidden" name="original" id="original" value="0" />
                        <select name="tipo">
                            <option value="F"<?php if ($row_term["TIPO"]=="F") echo " selected"; ?>><?php echo $fijo; ?> (F)</option>
                            <option value="M"<?php if ($row_term["TIPO"]=="M") echo " selected"; ?>><?php echo $movil; ?> (M)</option>
                            <option value="MB"<?php if ($row_term["TIPO"]=="MB") echo " selected"; ?>><?php echo "- $movilb"; ?> (MB)</option>
                            <option value="MA"<?php if ($row_term["TIPO"]=="MA") echo " selected"; ?>><?php echo "- $movila"; ?> (MA)</option>
                            <option value="MG"<?php if ($row_term["TIPO"]=="MG") echo " selected"; ?>><?php echo "- $movilg"; ?> (MG)</option>
                            <option value="P"<?php if ($row_term["TIPO"]=="P") echo " selected"; ?>><?php echo $portatil; ?> (P)</option>
                            <option value="PB"<?php if ($row_term["TIPO"]=="PB") echo " selected"; ?>><?php echo "- $portatilb"; ?> (PB)</option>
                            <option value="PA"<?php if ($row_term["TIPO"]=="PA") echo " selected"; ?>><?php echo "- $portatila"; ?> (PA)</option>
                            <option value="PX"<?php if ($row_term["TIPO"]=="PX") echo " selected"; ?>><?php echo "- $portatilx"; ?> (PX)</option>
                            <option value="D"<?php if ($row_term["TIPO"]=="D") echo " selected"; ?>><?php echo $despacho; ?> (D)</option>
                        </select> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
            </tr>
            <tr class="filapar">
                <th>Marca</th>
                <td>
                    <?php echo $row_term["MARCA"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="marca" size="15" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
                <th><?php echo $modtxt; ?></th>
                <td>
                    <?php echo $row_term["MODELO"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="modelo" size="15" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
            </tr>
            <tr>
                <th>ISSI</th>
                <td>
                    <?php echo $row_term["ISSI"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="issi" size="10" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
                <th>TEI</th>
                <td>
                    <?php echo $row_term["TEI"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="tei" size="30" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
            </tr>
            <tr class="filapar">
                <th><?php echo $nserie; ?></th>
                <td>
                        <?php echo $row_term["NSERIE"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="nserie" size="10" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
                <th>Número K</th>
                <td>
                        <?php echo $row_term["NUMEROK"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="numerok" size="30" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php echo $mnemo; ?></th>
                <td>
                    <?php echo $row_term["MNEMONICO"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="mnemonico" size="15" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
                <th>Carpeta</th>
                <td>
                    <?php echo $row_term["CARPETA"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="carpeta" size="15" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
            </tr>
            <tr class="filapar">
                <th><?php echo $llamsemi; ?></th>
                <td>
                    <?php echo $row_term["SEMID"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="hidden" name="original" id="original" value="0" />
                        <select name="semid">
                            <option value="NO"<?php if ($row_term["SEMID"]=="NO") echo " selected = 'selected'"; ?>>No</option>
                            <option value="SI"<?php if ($row_term["SEMID"]=="SI") echo " selected = 'selected'"; ?>>Sí</option>
                        </select> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
                <th><?php echo $llamdup; ?></th>
                <td>
                    <?php echo $row_term["DUPLEX"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="hidden" name="original" id="original" value="0" />
                        <select name="duplex">
                            <option value="NO"<?php if ($row_term["DUPLEX"]=="NO") echo " selected = 'selected'"; ?>>No</option>
                            <option value="SI"<?php if ($row_term["DUPLEX"]=="SI") echo " selected = 'selected'"; ?>>Sí</option>
                        </select> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php echo $amtxt; ?></th>
                <td>
                    <?php echo $row_term["AM"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="hidden" name="original" id="original" value="0" />
                        <select name="amarco">
                            <option value="NO"<?php if ($row_term["AM"]=="NO") echo " selected = 'selected'"; ?>>No</option>
                            <option value="SI"<?php if ($row_term["AM"]=="SI") echo " selected = 'selected'"; ?>>Sí</option>
                        </select> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
                <th><?php echo $version; ?></th>
                <td>
                    <?php echo $row_term["VERSION"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="version" size="15" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
            </tr>
            <tr class="filapar">
                <th><?php echo $observ; ?></th>
                <td colspan="3">
                    <?php echo $row_term["OBSERVACIONES"]; ?> &nbsp;
                    <a href="#" id="editar"><img src="imagenes/editar.png" alt="Modificar" title="Modificar"></a>
                    <div id="edicion" class="oculto">
                        <input type="text" name="observaciones" size="60" value=""> &nbsp; 
                        <a href="#" id="borrar"><img src="imagenes/cancelar.png" alt="Cancelar" title="Cancelar"></a>
                    </div>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td class="borde">
                    <a href='#' id="guardar"><img src='imagenes/guardar.png' alt='<?php echo $guardar; ?>' title='<?php echo $guardar; ?>'></a><br><?php echo $guardar; ?>
                </td>
                <td class="borde">
                    <a href='#' id="cancelar"><img src='imagenes/no.png' alt='<?php echo $cancelar; ?>' title='<?php echo $cancelar; ?>'></a><br><?php echo $cancelar; ?>
                </td>
            </tr>
        </table>
    </form>
<?php
}
?>