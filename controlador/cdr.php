<?php 

require_once '../config/datos.empresa.php';
require_once '../controllers/apisunat_2_1.clinica.php';
//require_once '../sistema_facturacion/inhouse_facturacion/comunicacion.baja.php';

$obj = new Apisunat();

//$ruta_ws = 'https://e-beta.sunat.gob.pe:443/ol-ti-itcpfegem-beta/billService';
$ruta_ws = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';
$obj->consultar_cdr(F_RUC, F_USUARIO_SOL, F_CLAVE_SOL, "", "", "", $ruta_ws);

//$ruta = "../archivos_xml_sunat/cpe_xml/produccion/";
