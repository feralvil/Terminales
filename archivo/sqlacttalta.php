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

// Importamos las variables del Formulario
import_request_variables("gp", "");

?>
<h3><?php echo $h3acttalta; ?></h3>
<form name="datosterm" id="datosterm" action="sqlacttupd.php" method="POST">
    <input type="hidden" name="origen" value="nueva" />
    <input type="hidden" name="idactuacion" value="<?php echo $idactuacion; ?>" />
    <input type="hidden" name="tipoact" value="ALTA" />
    <input type="hidden" name="origen" id="origen" value="nueva" />
    <table>
        <tr>
            <th><?php echo $provtxt; ?></th>
            <td><input type="text" name="proveedor" size="25" value=""></td>
            <th><?php echo $tipotxt; ?></th>
            <td>
                <select name="tipo">
                    <option value="F"><?php echo $fijo; ?></option>
                    <option value="M"><?php echo $movil; ?></option>
                    <option value="MB"><?php echo "- $movilb"; ?></option>
                    <option value="MA"><?php echo "- $movila"; ?></option>
                    <option value="MG"><?php echo "- $movilg"; ?></option>
                    <option value="P"><?php echo $portatilb; ?></option>
                    <option value="PB"><?php echo "- $portatilb"; ?></option>
                    <option value="PA"><?php echo "- $portatila"; ?></option>
                    <option value="PX"><?php echo "- $portatilx"; ?></option>
                    <option value="D"><?php echo $despacho; ?></option>
                </select>
            </td>
        </tr>
        <tr class="filapar">
            <th>Marca</th>
            <td><input type="text" name="marca" size="15" value=""></td>
            <th><?php echo $modtxt; ?></th>
            <td><input type="text" name="modelo" size="15" value=""></td>
        </tr>
        <tr>
            <th>ISSI</th>
            <td><input type="text" name="issi" size="10" value=""></td>
            <th>TEI</th>
            <td><input type="text" name="tei" size="30" value=""></td>
        </tr>
        <tr class="filapar">
            <th><?php echo $nserie; ?></th>
            <td><input type="text" name="nserie" size="20" value=""></td>
            <th>Número K</th>
            <td><input type="text" name="numerok" size="20" value=""></td>
        </tr>
        <tr>
            <th><?php echo $mnemo; ?></th>
            <td><input type="text" name="mnemonico" size="20" value=""></td>
            <th>Carpeta</th>
            <td><input type="text" name="carpeta" size="20" value=""></td>
        </tr>
        <tr class="filapar">
            <th><?php echo $llamsemi; ?></th>
            <td>
                <select name="semid">
                    <option value="NO">NO</option>
                    <option value="SI">SI</option>
                </select>
            </td>
            <th><?php echo $llamdup; ?></th>
            <td>
                <select name="duplex">
                    <option value="NO">NO</option>
                    <option value="SI">SI</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php echo $amtxt; ?></th>
            <td>
                <select name="amarco">
                    <option value="NO">NO</option>
                    <option value="SI">SI</option>
                </select>
            </td>
            <th><?php echo $version; ?></th>
            <td><input type="text" name="version" size="20" value=""></td>
        </tr>
        <tr class="filapar">
            <th><?php echo $observ; ?></th>
            <td colspan="3"><input type="text" name="observaciones" size="60" value=""></td>
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