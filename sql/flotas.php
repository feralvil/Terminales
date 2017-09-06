<?php
// Select de Organizaciones
$sql_organiza = "SELECT ID, ORGANIZACION FROM organizaciones ORDER BY organizaciones.ORGANIZACION ASC";
$res_organiza = mysqli_query($link, $sql_organiza) or die($errsqlorg . ": " . mysqli_error($link));
$norganiza = mysqli_num_rows($res_organiza);
// Construimos el Select de Organizaciones:
$selorganiza = array();
while ($orgsql = mysqli_fetch_assoc($res_organiza)){
    $selorganiza[] = array('ID' => $orgsql['ID'], 'ORGANIZACION' => $orgsql['ORGANIZACION']);
}

// Select de Flotas
$sql_selflotas = "SELECT ID, FLOTA FROM flotas WHERE 1";
if ((isset($_POST['idorg']))&&($_POST['idorg'] > 0)){
    $sql_selflotas .=  " AND (flotas.ORGANIZACION = " . $_POST['idorg'] .")";
}
$sql_selflotas .= " ORDER BY flotas.FLOTA ASC";
$res_selflotas = mysqli_query($link, $sql_selflotas) or die($errsqlselflo . ": " . mysqli_error($link));
$nselflotas = mysqli_num_rows($res_selflotas);
// Construimos el Select de Flotas:
$selflotas = array();
while ($flotasel = mysqli_fetch_assoc($res_selflotas)){
    $selflotas[] = array('ID' => $flotasel['ID'], 'FLOTA' => $flotasel['FLOTA']);
}

// Fijamos la Flota si es un usuario restringido o si se ha elegido una
$idflota = 0;
if ($permiso < 2){
    $idflota = $flota_usu;
}
else{
    if (isset($_POST['idflota'])){
        $idflota = $_POST['idflota'];
    }
}
// Consulta de tabla de flotas (limitada)
$sql_flotas = "SELECT flotas.ID, flotas.FLOTA, flotas.ACRONIMO, organizaciones.ORGANIZACION, flotas.ENCRIPTACION";
$sql_flotas .= " FROM flotas, organizaciones WHERE (flotas.ORGANIZACION = organizaciones.ID)";
if ((isset($_POST['idorg']))&&($_POST['idorg'] > 0)){
    $sql_flotas .=  " AND (flotas.ORGANIZACION = " . $_POST['idorg'] .")";
}
if ($idflota > 0){
    $sql_flotas .=  " AND (flotas.ID = " . $_POST['idflota'] .")";
}
if ((isset($_POST['formcont']))&&($_POST['formcont'] != "00")){
    $sql_flotas .=  " AND (flotas.FORMCONT = '" . $_POST['formcont'] ."')";
}
if ((isset($_POST['ambito']))&&($_POST['ambito'] != "00")){
    $sql_flotas .=  " AND (flotas.AMBITO = '" . $_POST['ambito'] ."')";
}
$sql_flotas .= " ORDER BY organizaciones.ORGANIZACION ASC, flotas.FLOTA ASC";
$res_flotas = mysqli_query($link, $sql_flotas) or die($errsqlflotot . mysqli_error($link));
$nftotal = mysqli_num_rows($res_flotas);
// Páginación
$pagina = 1;
if (isset($_POST['pagina'])){
    $pagina = $_POST['pagina'];
}
$tampagina = 30;
if (isset($_POST['tampagina'])){
    $tampagina = $_POST['tampagina'];
}
// Nº total de páginas:
$npaginas = ceil($nftotal / $tampagina);
$inicio = ($pagina - 1) * $tampagina;
$sql_flotas .= " LIMIT ".$inicio.",". $tampagina;
$res_flotas = mysqli_query($link, $sql_flotas) or die($errsqlflotas . mysqli_error($link));
$nflotas = mysqli_num_rows($res_flotas);
$flotas = array();
$totterm = array (0, 0, 0, 0, 0);
$tipoterm = array('%', 'F', 'M%', 'P%', 'D');
while ($sqlflota = mysqli_fetch_assoc($res_flotas)){
    $nterm = array(0, 0, 0, 0, 0);
    // Ejecutar consulta de terminales
    for ($i = 0; $i < count($tipoterm); $i++){
        $sql_termflota = "SELECT * FROM terminales WHERE FLOTA = '".$sqlflota["ID"]."' AND TIPO LIKE '".$tipoterm[$i]."'";
        $res_termflota = mysqli_query($link, $sql_termflota) or die($errsqlterm . ": " . mysqli_error($link));
        $nterm [$i] = mysqli_num_rows($res_termflota);
        $totterm [$i] += $nterm[$i];
    }
    $flotas[] = array(
        'ID' => $sqlflota['ID'],
        'ORGANIZACION' => $sqlflota['ORGANIZACION'],
        'FLOTA' => $sqlflota['FLOTA'],
        'ACRONIMO' => $sqlflota['ACRONIMO'],
        'ENCRIPTACION' => $sqlflota['ENCRIPTACION'],
        'NTERM' => $nterm[0],
        'NBASE' => $nterm[1],
        'NMOV' => $nterm[2],
        'NPORT' => $nterm[3],
        'NDESP' => $nterm[4]
    );
}
mysqli_free_result($res_organiza);
mysqli_free_result($res_selflotas);
mysqli_free_result($res_flotas);
mysqli_close($link);
?>
