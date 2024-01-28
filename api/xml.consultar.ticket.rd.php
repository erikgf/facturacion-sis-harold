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

        $tickets = $data["tickets"];
       
        require_once "../negocio/ConsultorTicket.clase.php";
		$objConsultorTicket = new ConsultorTicket();
        $arreglo_respuestas = [];

        foreach ($tickets as $key => $obj_ticket) {
            $fecha = str_replace("-","",$obj_ticket["fecha_emision"]);
			$EMISOR_RUC = $obj_ticket["EMISOR_RUC"];
			$EMISOR_USUARIO_SOL = $obj_ticket["EMISOR_USUARIO_SOL"];
			$EMISOR_PASS_SOL = $obj_ticket["EMISOR_PASS_SOL"];

            $ruta_cdr = "../cpe_xml/".F_RUC."/".$carpeta."/cdr/".$fecha."/".$data["id_tipo_comprobante"]."/";
            if(!is_dir($ruta_cdr)){
                mkdir($ruta_cdr, 0755, true);
            }
            $r = $objConsultorTicket->consultar_envio_ticket($EMISOR_RUC, $EMISOR_USUARIO_SOL, $EMISOR_PASS_SOL, $obj_ticket["ticket"], $obj_ticket["nombre_resumen"], $ruta_cdr, $ruta_ws);
            $r["id"] = $obj_ticket["id"];
			$r["nombre_archivo"] = $obj_ticket["nombre_resumen"];
            array_push($arreglo_respuestas, $r);
        }

        http_response_code(200);
		echo json_encode($arreglo_respuestas);
		exit;
		
	 } catch (Exception $exc) {
		http_response_code(500);
		echo json_encode(["respuesta"=>"error", "mensaje"=>$exc->getMessage()]);
     }
	