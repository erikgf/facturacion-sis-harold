<?php
	try {
		include_once "../config/datos.empresa.php";

		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		error_reporting(E_ALL ^ E_NOTICE);
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

		$bodyRequest = file_get_contents("php://input");
		// Decodificamos y lo guardamos en un array
		$data = json_decode($bodyRequest, true);

		$tipo_proceso = (isset($data['tipo_proceso'])) ? $data['tipo_proceso'] : "3";

		if ($tipo_proceso == "1"){
			$ruta_ws = F_RUTA;
		} else {
			$ruta_ws = F_RUTA_BETA;
		}

		$nombre_archivo = $data['EMISOR_RUC'] . '-' . $data['COD_TIPO_DOCUMENTO'] . '-' . $data['NRO_COMPROBANTE'].".XML";

		require_once "../negocio/GeneradorRutaCPE.clase.php";
		require_once "../negocio/GeneradorXML.clase.php";

		$objGeneradorRutaCPE = new GeneradorRutaCPE();
		$objGeneradorRutaCPE->emisor_ruc = $data["EMISOR_RUC"];
		$objGeneradorRutaCPE->id_tipo_comprobante = $data["COD_TIPO_DOCUMENTO"];
		$objGeneradorRutaCPE->tipo_proceso = $data["tipo_proceso"];
		
		if ($objGeneradorRutaCPE->id_tipo_comprobante == "RC"){
			$objGeneradorRutaCPE->fecha_comprobante = $data["FECHA_EMISION"];	
		} else {
			$objGeneradorRutaCPE->fecha_comprobante = $data["FECHA_DOCUMENTO"];
		}

		$ruta = $objGeneradorRutaCPE->getRutaComprobante(); 
		$objGeneradorXML = new GeneradorXML();

		switch($objGeneradorRutaCPE->id_tipo_comprobante){
			case "01":
			case "03":
				$resp = $objGeneradorXML->crear_xml_factura($data,  $nombre_archivo, $ruta);
				break;
			case "07":
				$resp = $objGeneradorXML->crear_xml_nota_credito($data,  $nombre_archivo, $ruta);
				break;
			case "08":
				$resp = $objGeneradorXML->crear_xml_nota_debito($data,  $nombre_archivo, $ruta);
				break;
			case "RC":
				$resp = $objGeneradorXML->crear_xml_resumen_documentos($data, $nombre_archivo, $ruta);
				break;
			default:
				throw new Exception("Se ha ingresado un tipo de comprobate no válido.", 1);
				break;
		}

		//Reformular la ruta: de local a externa.
		$resp["ruta"] = $objGeneradorRutaCPE->getRutaExterna($resp["ruta"]); 
		
		http_response_code(200);
		echo json_encode($resp);
		exit;
		
	} catch (\Throwable $th) {
		http_response_code(500);
		echo json_encode(["respuesta"=>"error", "mensaje"=>mb_convert_encoding($th->getMessage(),'HTML-ENTITIES','UTF-8')]);
	}
	