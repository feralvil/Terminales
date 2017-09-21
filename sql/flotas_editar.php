<?php
// Fijamos la Flota si es un usuario restringido o si se ha elegido una
$idflota = $_POST['idflota'];
// Consulta de la Flota
$sql_flota = "SELECT * FROM flotas WHERE (flotas.ID = $idflota)";
$res_flota = mysqli_query($link, $sql_flota) or die($errsqlflota . ': ' . mysqli_error($link));
$nflota = mysqli_num_rows($res_flota);
if ($nflota > 0){
    $flota = mysqli_fetch_assoc($res_flota);
    mysqli_free_result($res_flota);    
	// Select de Organizaciones
	$sql_selorg = "SELECT ID, ORGANIZACION FROM organizaciones ORDER BY organizaciones.ORGANIZACION ASC";
	$res_selorg = mysqli_query($link, $sql_selorg) or die($errsqlselorg . ": " . mysqli_error($link));
	$nselorg = mysqli_num_rows($res_selorg);
	// Construimos el Select de Organizaciones:
	$selorg = array();
	while ($orgsel = mysqli_fetch_assoc($res_selorg)){
	    $selorg[$orgsel['ID']] = $orgsel['ORGANIZACION'];
	}
	mysqli_free_result($res_selorg);
    // Select de Municipios
    $sql_muni = "SELECT INE, MUNICIPIO FROM municipios ORDER BY municipios.MUNICIPIO ASC";
    $res_selmuni = mysqli_query($link, $sql_muni) or die($errsqlmuni . ': ' . mysqli_error($link));
    $nmuni = mysqli_num_rows($res_selmuni);
    // Construimos el Select de Municipio:
    $selmuni = array();
	while ($munisel = mysqli_fetch_assoc($res_selmuni)){
	    $selmuni[$munisel['INE']] = $munisel['MUNICIPIO'];
	}
	mysqli_free_result($res_selmuni);
}
mysqli_close($link);
?>