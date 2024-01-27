<?php 

require_once '../config/datos.empresa.php';
require_once '../controllers/apisunat_2_1.clinica.php';
//require_once '../sistema_facturacion/inhouse_facturacion/comunicacion.baja.php';

$obj = new Apisunat();

$fecha  = isset($_GET["p"]) ? $_GET["p"] : NULL;
if ($fecha == null){
	echo "Archivo no válido";
	return;
}

$directorio = "../cpe_xml/".F_RUC."/produccion/comprobante_firmado/".$fecha."/RC/";
$ruta_cdr = "../cpe_xml/".F_RUC."/produccion/cdr/".$fecha."/RC/";
$ficheros  = array_diff(scandir($directorio), array('.', '..'));

if (!is_dir($directorio)){
	var_dump("No hay archivos");
	exit;
}

if(!is_dir($ruta_cdr)){
    mkdir($ruta_cdr, 0755, true);
}

//$ruta_ws = 'https://e-beta.sunat.gob.pe:443/ol-ti-itcpfegem-beta/billService';
$ruta_ws =  'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';

foreach ($ficheros as $key => $archivo) {
	$_ = explode(".", $archivo);
	$archivo = $_[0];
	$ruta_archivo = $directorio.$archivo;
	$r = $obj->enviar_resumen_boletas(F_RUC, F_USUARIO_SOL, F_CLAVE_SOL, $ruta_archivo, $ruta_cdr, $archivo, $ruta_ws);
	echo '<br>';
}

/*
foreach ($resumenes_pendientes as $key => $archivo) {
	$idcorrelativo = $archivo["idcorrelativo"];
	$fecha = $archivo["fecha"];
	$archivo_xml = $archivo["xml"];
	$ruta_comprobantes_archivo = __obtenerDirectorio("comprobantes", $fecha).$archivo_xml;
	$ruta_cdr_archivo = __obtenerDirectorio("cdr", $fecha);

	$r = $obj->enviar_resumen_boletas(F_RUC, F_USUARIO_SOL, F_CLAVE_SOL, $ruta_comprobantes_archivo, $ruta_cdr_archivo, $archivo_xml, F_RUTA);

	if ($r["respuesta"] == "ok"){
		$objRes->asignarTicketResumenDiario($idcorrelativo, $r["cod_ticket"]);	
	}

	var_dump("Archivo XML enviado: ".$archivo_xml, $r);
}
*/