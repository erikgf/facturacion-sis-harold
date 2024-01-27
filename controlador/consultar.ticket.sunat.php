<?php 

/*
-- Este archivo debe ejecutarse a las 3.30 de todos los días, consulta todos los archivos
de resumenes diarios q esten pendientes (con ticket) pero SIN respuesta.
*/


require_once '../config/datos.empresa.php';
require_once '../controllers/apisunat_2_1.clinica.php';
require_once '../modelos/ResumenDiario.php';
//require_once '../sistema_facturacion/inhouse_facturacion/comunicacion.baja.php';

$obj = new Apisunat();
$objRes = new ResumenDiario();

function __obtenerDirectorio($comprobante_o_cdr, $fecha){
	return "../archivos_xml_sunat/cpe_xml/produccion/".$comprobante_o_cdr."/".$fecha."/RC/";	
}

$ticket = $_GET["t"];
$archivo_xml = $_GET["x"];
$fecha =  $_GET["f"];

if (count($ticket) == null){
	echo "No hay dada que procesar";
	exit;
}

$ruta_cdr_archivo = __obtenerDirectorio("cdr", $fecha);

$r = $obj->consultar_envio_ticket(F_RUC, F_USUARIO_SOL, F_CLAVE_SOL, $ticket, $archivo_xml, $ruta_cdr_archivo, F_RUTA);

var_dump("Respuesta ticket: ".$archivo_xml, $r);



