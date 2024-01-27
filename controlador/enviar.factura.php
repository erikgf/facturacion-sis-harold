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

$directorio = "../cpe_xml/".F_RUC."/produccion/comprobante_firmado/".$fecha."/FA/";
$ruta_cdr = "../cpe_xml/".F_RUC."/produccion/cdr/".$fecha."/FA/";

if (!is_dir($directorio)){
	var_dump("No hay archivos");
	exit;
}

$ficheros  = array_diff(scandir($directorio), array('.', '..'));

if(!is_dir($ruta_cdr)){
    mkdir($ruta_cdr, 0755, true);
}

if (count($ficheros) <= 0){
    var_dump("No hay archivos.");
    exit;
}

$ruta_ws = 'https://e-beta.sunat.gob.pe:443/ol-ti-itcpfegem-beta/billService';
//$ruta_ws = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';

foreach ($ficheros as $key => $archivo) {
	$_ = explode(".", $archivo);
	$archivo = $_[0];
	$ruta_archivo = $directorio.$archivo;
	$r = $obj->enviar_documento(F_RUC, F_USUARIO_SOL, F_CLAVE_SOL, $ruta_archivo, $ruta_cdr, $archivo, $ruta_ws);

	var_dump($r);
	echo '<br>';
}



//$ruta = "../archivos_xml_sunat/cpe_xml/produccion/";
