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
            $carpeta = "produccion";
		} else {
			$ruta_ws = F_RUTA_BETA;
            $carpeta = "beta";
		}

		$comprobantes = $data["comprobantes"];

		require_once "../negocio/EnviadorXML.clase.php";
		$enviadorXML = new EnviadorXML();
        $arreglo_respuestas = [];
        foreach ($comprobantes as $key => $obj_comprobante) {
			$carpetaTipoComprobante = "";
			switch($data["id_tipo_comprobante"]){
				case "01": $carpetaTipoComprobante = "FA"; break;
				case "03": $carpetaTipoComprobante = "BV"; break;	
				case "07": $carpetaTipoComprobante = "NC"; break;
				case "08": $carpetaTipoComprobante = "ND"; break;
			}

            $fecha = str_replace("-","",$obj_comprobante["fecha_emision"]);
			$archivo = basename($obj_comprobante["nombre_archivo"], '.XML');
			$EMISOR_RUC = $obj_comprobante["EMISOR_RUC"];
			$EMISOR_USUARIO_SOL = $obj_comprobante["EMISOR_USUARIO_SOL"];
			$EMISOR_PASS_SOL = $obj_comprobante["EMISOR_PASS_SOL"];

			$directorio = "../cpe_xml/".$EMISOR_RUC."/".$carpeta."/comprobante_firmado/".$fecha."/".$carpetaTipoComprobante."/";
            $ruta_cdr = "../cpe_xml/".$EMISOR_RUC."/".$carpeta."/cdr/".$fecha."/".$carpetaTipoComprobante."/";
            if(!is_dir($ruta_cdr)){
                mkdir($ruta_cdr, 0755, true);
            }
			$ruta_archivo = $directorio.$archivo;
			$r = $enviadorXML->enviar_comprobante($EMISOR_RUC, $EMISOR_USUARIO_SOL, $EMISOR_PASS_SOL, $ruta_archivo, $ruta_cdr, $archivo, $ruta_ws);
            $r["id"] = $obj_comprobante["id"];
			$r["id_tipo_comprobante"] = $obj_comprobante["id_tipo_comprobante"];
			$r["nombre_archivo"] = $archivo;
            array_push($arreglo_respuestas, $r);
        }

		http_response_code(200);
		echo json_encode($arreglo_respuestas);
		exit;
		
	 } catch (Exception $exc) {
		http_response_code(500);
		echo json_encode(["respuesta"=>"error", "mensaje"=>$exc->getMessage()]);
     }
	