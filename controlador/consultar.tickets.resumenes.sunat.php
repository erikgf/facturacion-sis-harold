<?php 

/*
-- Este archivo debe ejecutarse a las 3.30 de todos los días, consulta todos los archivos
de resumenes diarios q esten pendientes (con ticket) pero SIN respuesta.
*/


require_once '../config/datos.empresa.php';
require_once '../controllers/apisunat_2_1.clinica.php';
//require_once '../modelos/ResumenDiario.php';
//require_once '../sistema_facturacion/inhouse_facturacion/comunicacion.baja.php';

$obj = new Apisunat();
//$objRes = new ResumenDiario();

$fecha = "20210601";
$ticket ="202107197360663";
$archivo_xml = "20480718560-RC-20210611-1";
/*
	
*/

$directorio = "../cpe_xml/".F_RUC."/produccion/comprobante_firmado/".$fecha."/RC/".$archivo_xml;
$ruta_cdr_archivo = "../cpe_xml/".F_RUC."/produccion/cdr/".$fecha."/RC/".$archivo_xml;
//$ficheros  = array_diff(scandir($directorio), array('.', '..'));

//$ruta_ws = 'https://e-beta.sunat.gob.pe:443/ol-ti-itcpfegem-beta/billService';
$ruta_ws =  'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';

$r = $obj->consultar_envio_ticket(F_RUC, F_USUARIO_SOL, F_CLAVE_SOL, $ticket, $archivo_xml, $ruta_cdr_archivo, $ruta_ws);

var_dump($r);

/*
function __obtenerDirectorio($comprobante_o_cdr, $fecha){
	return "../archivos_xml_sunat/cpe_xml/produccion/".$comprobante_o_cdr."/".$fecha."/RC/";	
}

$resumenes_pendientes = $objRes->obtenerResumenesDiariosConTicketsPendientes();

if (count($resumenes_pendientes) <= 0){
	echo "No hay dada que procesar";
	exit;
}

foreach ($resumenes_pendientes as $key => $archivo) {
	$idcorrelativo = $archivo["idcorrelativo"];
	$fecha = $archivo["fecha"];
	$ticket = $archivo["ticket"];
	$archivo_xml = $archivo["xml"];
	$ruta_cdr_archivo = __obtenerDirectorio("cdr", $fecha);

	$r = $obj->consultar_envio_ticket(F_RUC, F_USUARIO_SOL, F_CLAVE_SOL, $ticket, $archivo_xml, $ruta_cdr_archivo, F_RUTA);

	if ($r["respuesta"] == "ok" || $r["httpcode"] == "200"){
		$objRes->marcarResumenComoRespuestaOK($ticket);	
	}

	var_dump("Respuesta ticket: ".$archivo_xml, $r);
}

*/