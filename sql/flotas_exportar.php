<?php
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
    $sql_org = "SELECT * FROM organizaciones WHERE ID = " . $_POST['idorg'];
    $res_org = mysqli_query($link, $sql_org) or die($errsqlorg . mysqli_error($link));
    $norg = mysqli_num_rows($res_org);
    if ($norg > 0){
        $selorg = mysqli_fetch_assoc($res_org);
        mysqli_free_result($res_org);
    }
}
if ($idflota > 0){
    $sql_flotas .=  " AND (flotas.ID = " . $idflota .")";
    $sql_flota = "SELECT * FROM flotas WHERE ID = $idflota";
    $res_flota = mysqli_query($link, $sql_flota) or die($errsqlflotas . mysqli_error($link));
    $nflotas = mysqli_num_rows($res_flota);
    if ($nflotas > 0){
        $selflota = mysqli_fetch_assoc($res_flota);
        mysqli_free_result($res_flota);
    }
}
if ((isset($_POST['formcont']))&&($_POST['formcont'] != "00")){
    $sql_flotas .=  " AND (flotas.FORMCONT = '" . $_POST['formcont'] ."')";
}
if ((isset($_POST['ambito']))&&($_POST['ambito'] != "00")){
    $sql_flotas .=  " AND (flotas.AMBITO = '" . $_POST['ambito'] ."')";
}
$sql_flotas .= " ORDER BY organizaciones.ORGANIZACION ASC, flotas.FLOTA ASC";
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
        $res_termflota = mysqli_query($link, $sql_termflota) or die("Error en la Consulta de Terminales: " . mysqli_error($link));
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
mysqli_free_result($res_flotas);
mysqli_close($link);
?>
