<?php
	try {

        include_once "./config.php";

		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		error_reporting(E_ALL ^ E_NOTICE);
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

		//$bodyRequest = file_get_contents("php://input");
		// Decodificamos y lo guardamos en un array
		//$numero_documento = json_decode($bodyRequest, true);
        $numero_documento = $_GET["nd"];

		$token_cliente = F_TOKEN_PROVEEDOR; //KEY para que puedas consumir nuestra api
		//$data['ruc_proveedor'] = F_RUC_PROVEEDOR; //Tu número de RUC, el cuál será responsable por los datos enviados en todos los json
		$ruta = "";
        $HOY = date("d-m-Y");

        function dateDiffInDays($date1, $date2){
           // Calculating the difference in timestamps
            $diff = strtotime($date2) - strtotime($date1);
            // 1 day = 24 hours
            // 24 * 60 * 60 = 86400 seconds
            return round($diff / 86400);
        }

        $dateDiff = dateDiffInDays($HOY, F_FECHA_TOPE);

        if ($dateDiff < 0){
            $respuesta['respuesta'] = 'error';
			$respuesta['titulo'] = 'Error';
			$respuesta['data'] = '';
			$respuesta['encontrado'] = false;
			$respuesta['mensaje'] = 'El servicio de consulta RENIEC/SUNAT ha vencido. Fecha LIMITE: '.F_FECHA_TOPE;
			$respuesta['errores_curl'] = "";
            echo json_encode($respuesta);
            exit;
        }

		$cantidad_digitos = strlen($numero_documento);

		switch($cantidad_digitos){
			case 8:
				$ruta = 'https://facturalahoy.com/api/persona/';
				break;
			case 11:
				$ruta = 'https://facturalahoy.com/api/empresa/';
				break;
			default:
			break;
		}

		if ($ruta == ""){
			$respuesta['respuesta'] = 'error';
			$respuesta['titulo'] = 'Error';
			$respuesta['data'] = '';
			$respuesta['encontrado'] = false;
			$respuesta['mensaje'] = 'Tipo de comprobante de consulta no válido.';
			$respuesta['errores_curl'] = "";


            echo json_encode($respuesta);
		    exit;
		}

		$ruta .= $numero_documento.'/'.$token_cliente;

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $ruta,
			CURLOPT_USERAGENT => 'Consulta Datos',
			CURLOPT_CONNECTTIMEOUT => 0,
			CURLOPT_TIMEOUT => 400,
			CURLOPT_FAILONERROR => true
		));

		$respuesta  = curl_exec($curl);

		if (curl_error($curl)) {
			$error_msg = curl_error($curl);
		}
		curl_close($curl);

		if (isset($error_msg)) {
			$respuesta['respuesta'] = 'error';
			$respuesta['titulo'] = 'Error';
			$respuesta['data'] = '';
			$respuesta['encontrado'] = false;
			$respuesta['mensaje'] = 'Error en Api de Búsqueda';
			$respuesta['errores_curl'] = $error_msg;
		} else{
			$respuesta = json_decode($respuesta);
		}

        http_response_code(200);
		echo json_encode($respuesta);
		exit;
		
	 } catch (Exception $exc) {
		http_response_code(500);
		echo json_encode(["respuesta"=>"error", "mensaje"=>$exc->getMessage()]);
     }
	