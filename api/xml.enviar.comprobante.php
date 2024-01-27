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

		$resumenes = $data["resumenes"];
		
		require_once "../negocio/EnviadorXML.clase.php";
		$enviadorXML = new EnviadorXML();
        $arreglo_respuestas = [];
        foreach ($resumenes as $key => $obj_resumen) {
            $fecha = str_replace("-","",$obj_resumen["fecha_emision"]);
			$archivo = $obj_resumen["nombre_archivo"];
			$directorio = "../cpe_xml/".F_RUC."/".$carpeta."/comprobante_firmado/".$fecha."/".$data["id_tipo_comprobante"]."/";
            $ruta_cdr = "../cpe_xml/".F_RUC."/".$carpeta."/cdr/".$fecha."/".$data["id_tipo_comprobante"]."/";
            if(!is_dir($ruta_cdr)){
                mkdir($ruta_cdr, 0755, true);
            }
			$ruta_archivo = $directorio.$archivo;
			$r = $enviadorXML->enviar_resumen_boletas(F_RUC, F_USUARIO_SOL, F_CLAVE_SOL, $ruta_archivo, $ruta_cdr, $archivo, $ruta_ws);
            $r["id"] = $obj_resumen["id"];
            array_push($arreglo_respuestas, $r);
        }

		http_response_code(200);
		echo json_encode($arreglo_respuestas);
		exit;
		
	 } catch (Exception $exc) {
		http_response_code(500);
		echo json_encode(["respuesta"=>"error", "mensaje"=>$exc->getMessage()]);
     }
	