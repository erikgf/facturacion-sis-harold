<?php

class ConsultorTicket{
    function consultar_envio_ticket($ruc, $usuario_sol, $pass_sol, $ticket, $archivo, $ruta_archivo_cdr, $ruta_ws) 
    {  
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
        <wsse:Security>
        <wsse:UsernameToken>
        <wsse:Username>' . $ruc . $usuario_sol . '</wsse:Username>
        <wsse:Password>' . $pass_sol . '</wsse:Password>
        </wsse:UsernameToken>
        </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
        <ser:getStatus>
        <ticket>' . $ticket . '</ticket>
        </ser:getStatus>
        </soapenv:Body>
        </soapenv:Envelope>';
    
        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-length: " . strlen($xml_post_string),
        ); //SOAPAction: your op URL
        
        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $ruta_ws);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        // converting
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //var_dump($response, "<br>");

        IF ($response == ""){
        	return [
        		"respuesta"=>"error",
        		"cod_sunat"=>"-1",
        		"mensaje"=>"SUNAT ESTA FUERA DE SERVICIO / SIN RESPUESTA",
        		"httpcode"=>$httpcode
        	];
        }

        $doc = new DOMDocument();
        $doc->loadXML($response);

        if ($httpcode == 200) {//======LA PAGINA SI RESPONDE
            //echo $httpcode.'----'.$response;
            //convertimos de base 64 a archivo fisico
            //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
            if (isset($doc->getElementsByTagName('content')->item(0)->nodeValue)) {
                $xmlCDR = $doc->getElementsByTagName('content')->item(0)->nodeValue;
                file_put_contents($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP', base64_decode($xmlCDR));
                //extraemos archivo zip a xml
                $zip = new ZipArchive;
                $openedZip = $zip->open($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP');
                $extension = ".XML";
                if ($openedZip === TRUE) {
                   	if (!$zip->extractTo($ruta_archivo_cdr, 'R-' . $archivo . $extension)){
                   		$extension = ".xml";
                   		$zip->extractTo($ruta_archivo_cdr, 'R-' . $archivo . $extension);
                   	}
               		$zip->close();
                }

                $archivoXML = $ruta_archivo_cdr. 'R-' . $archivo . $extension;
                //eliminamos los archivos Zipeados
                //unlink($ruta_archivo . '.ZIP');
                unlink($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP');

                $mensaje['respuesta'] = 'ok';
                //$doc_cdr->load(dirname(__FILE__) . '/' . $ruta_archivo_cdr . 'R-' . $archivo . '.XML');
                if (file_exists($archivoXML)){
               		$doc_cdr = new DOMDocument();
                	$doc_cdr->load($archivoXML);
	                $mensaje['cod_sunat'] = $doc_cdr->getElementsByTagName('ResponseCode')->item(0)->nodeValue;
	                $mensaje['msj_sunat'] = $doc_cdr->getElementsByTagName('Description')->item(0)->nodeValue;
	                $mensaje['mensaje'] = $doc_cdr->getElementsByTagName('Description')->item(0)->nodeValue;
	                $mensaje['hash_cdr'] =  $doc_cdr->getElementsByTagName('DigestValue')->item(0)->nodeValue;
	                $mensaje['signature_value'] =  $doc_cdr->getElementsByTagName('SignatureValue')->item(0)->nodeValue;	
                } else {
		            $mensaje['cod_sunat']= $doc->getElementsByTagName('statusCode')->item(0)->nodeValue;
		            $mensaje['mensaje']= $response;	
		            $mensaje['hash_cdr'] = "";
		            $mensaje['signature_value'] =  "";
                }
            } else {

            	$mensaje['cod_sunat'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
	            $mensaje['msj_sunat'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
	            $mensaje['hash_cdr'] = "";
	            $mensaje['respuesta'] = 'error';
	            $mensaje['cod_sunat'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
	            $mensaje['mensaje'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
	            $mensaje['msj_sunat'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
	            $mensaje['hash_cdr'] = "";
	            $mensaje['signature_value'] =  "";
            }

        } else {

            //echo "no responde web";
            $mensaje['respuesta'] = 'error';
            $mensaje['cod_sunat']="0000";
            $mensaje['mensaje']= $response;
            $mensaje['hash_cdr'] = "";
            $mensaje['signature_value'] =  "";
        }

       	$mensaje['httpcode'] = $httpcode;
        return $mensaje;
    }
  
}