<?php

class EnviadorXML{

    public function enviar_resumen_boletas($ruc, $usuario_sol, $pass_sol, $ruta_archivo, $ruta_archivo_cdr, $archivo, $ruta_ws) {
        //=================ZIPEAR ================
        $zip = new ZipArchive();
        $filenameXMLCPE = $ruta_archivo . '.ZIP';

        if ($zip->open($filenameXMLCPE, ZIPARCHIVE::CREATE) === true) {
            $zip->addFile($ruta_archivo . '.XML', $archivo . '.XML'); //ORIGEN, DESTINO
            $zip->close();
        }
    
        //===================ENVIO FACTURACION=====================
        $soapUrl = $ruta_ws; 
        $soapUser = "";  
        $soapPassword = ""; 
        // xml post structure
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
        xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" 
        xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken>
                    <wsse:Username>' . $ruc . $usuario_sol . '</wsse:Username>
                    <wsse:Password>' . $pass_sol . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
            <ser:sendSummary>
                <fileName>' . $archivo . '.ZIP</fileName>
                <contentFile>' . base64_encode(file_get_contents($ruta_archivo . '.ZIP')) . '</contentFile>
            </ser:sendSummary>
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
    
        $url = $soapUrl;
    
        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
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

        if ($httpcode == 200) {//======LA PAGINA SI RESPONDE
            //convertimos de base 64 a archivo fisico
            $doc = new DOMDocument();
            $doc->loadXML($response);
            
            //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
            if (isset($doc->getElementsByTagName('ticket')->item(0)->nodeValue)) {
                $ticket = $doc->getElementsByTagName('ticket')->item(0)->nodeValue;
                
                unlink($ruta_archivo . '.ZIP');
                $mensaje['respuesta'] = 'ok';
                $mensaje['cod_ticket'] = $ticket;
            } else {
                $mensaje['respuesta'] = 'error';
                $mensaje['cod_sunat'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje['mensaje'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                $mensaje['hash_cdr'] = "";
            }
            
        } else {
            //echo "no responde web";
            $mensaje['respuesta'] = 'error';
            $mensaje['cod_sunat']="0000";
            $mensaje['mensaje']= $response;
            $mensaje['hash_cdr'] = "";
        }
        return $mensaje;
    }

    public function enviar_comprobante($ruc, $usuario_sol, $pass_sol, $ruta_archivo, $ruta_archivo_cdr, $archivo, $ruta_ws) {
        //=================ZIPEAR ================
        $zip = new ZipArchive();
        $filenameXMLCPE = $ruta_archivo . '.ZIP';

        if ($zip->open($filenameXMLCPE, ZIPARCHIVE::CREATE) === true) {
            $zip->addFile($ruta_archivo . '.XML', $archivo . '.XML'); //ORIGEN, DESTINO
            $zip->close();
        }

        //===================ENVIO FACTURACION=====================
        $soapUrl = $ruta_ws;
        // xml post structure
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
        xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" 
        xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken>
                    <wsse:Username>' . $ruc . $usuario_sol . '</wsse:Username>
                    <wsse:Password>' . $pass_sol . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
            <ser:sendBill>
                <fileName>' . $archivo . '.ZIP</fileName>
                <contentFile>' . base64_encode(file_get_contents($ruta_archivo . '.ZIP')) . '</contentFile>
            </ser:sendBill>
        </soapenv:Body>
        </soapenv:Envelope>';

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-length: " . strlen($xml_post_string),
        );

        $url = $soapUrl;    

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        
        if ($httpcode == 200) {
            $doc = new DOMDocument();
            $doc->loadXML($response);

            file_put_contents($ruta_archivo_cdr . 'RAW-' . $archivo . '.txt', $response);

            //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
            if (isset($doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue)) {
                $xmlCDR = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;
                file_put_contents($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP', base64_decode($xmlCDR));
                //extraemos archivo zip a xml
                $zip = new ZipArchive;
                $extension = ".XML";
                $archivoZIP = $ruta_archivo_cdr. 'R-' . $archivo . '.ZIP';

                clearstatcache();
                if (!file_exists($archivoZIP) ||
                    (filesize($archivoZIP) == 0) ){
                    $resp['respuesta'] = 'ok';
                	$resp['cod_sunat'] = "-1";
	                $resp['mensaje'] = "SUNAT no devolvio el CDR correctamente. Verificar validez manualmente.";
                    //$resp['observaciones'] = "";
	                $resp['hash_cdr'] = "";
	                $resp['signature_value'] = "";
                    return $resp;
                }

                if ($zip->open($archivoZIP) == TRUE) {
                    if (!$zip->extractTo(substr($ruta_archivo_cdr, 0, -1), 'R-' . $archivo . $extension)){
                    	$extension = ".xml";
                    	$zip->extractTo(substr($ruta_archivo_cdr, 0, -1), 'R-' . $archivo . $extension);
                    }
                    $zip->close();
                }

                $archivoXML = $ruta_archivo_cdr. 'R-' . $archivo . $extension;

                //=============hash CDR=================
                if (file_exists($archivoXML)){
                	$doc_cdr = new DOMDocument();
               		$doc_cdr->load($archivoXML);

               		$resp['respuesta'] = 'ok';
	                $resp['cod_sunat'] = $doc_cdr->getElementsByTagName('ResponseCode')->item(0)->nodeValue;
                    //$resp['observaciones'] = "";
	                $resp['mensaje'] = str_replace("'","",$doc_cdr->getElementsByTagName('Description')->item(0)->nodeValue);
	                $resp['hash_cdr'] = $doc_cdr->getElementsByTagName('DigestValue')->item(0)->nodeValue;
	                $resp['signature_value'] = $doc_cdr->getElementsByTagName('SignatureValue')->item(0)->nodeValue;
                    $resp["xml_cdr"]  = $archivoXML;
                    //$resp['observaciones'] = $doc_cdr->getElementsByTagName('Notes')->item(0)->nodeValue;
                } else {
                	$resp['respuesta'] = 'ok';
                	$resp['cod_sunat'] = "-1";
	                $resp['mensaje'] = "SUNAT no devolvio el CDR correctamente. Verificar validez manualmente.";
                    //$resp['observaciones'] = "";
	                $resp['hash_cdr'] = "";
	                $resp['signature_value'] = "";
                    $resp["xml_cdr"]  = "";
                }

                //eliminamos los archivos Zipeados
                if (file_exists($ruta_archivo . '.ZIP')){
                    unlink($ruta_archivo . '.ZIP');
                }
                if (file_exists($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP')){
                    unlink($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP');
                }
            } else {
                $resp['respuesta'] = 'error';
                $responseCode = $doc->getElementsByTagName('faultcode')->item(0);
                if ($responseCode){
                    $resp['cod_sunat'] = $responseCode->nodeValue;
                } else{
                    $resp["cod_sunat"] = "-1";
                }
                $resp['mensaje'] = str_replace("'","",$doc->getElementsByTagName('faultstring')->item(0)->nodeValue);
              //  $resp['observaciones'] = "";
                $resp['hash_cdr'] = "";
                $resp['signature_value'] = "";
                $resp["xml_cdr"]  = "";
            }
        } else {
            //echo "no responde web";
            $doc = new DOMDocument();
            if ($response != ""){
                $doc->loadXML($response);

                //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
                if (isset($doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue)) {
                    $xmlCDR = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;

                    $xmlCDR = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;
                    file_put_contents($ruta_archivo_cdr . 'ERROR-' . $archivo . '.ZIP', base64_decode($xmlCDR));
                    //extraemos archivo zip a xml
                    $extension = ".XML";
                    $zip = new ZipArchive;
                    if ($zip->open($ruta_archivo_cdr . 'ERROR-' . $archivo . '.ZIP') === TRUE) {
                        if (!$zip->extractTo(substr($ruta_archivo_cdr, 0, -1), 'ERROR-' . $archivo . $extension)){
                            $extension =".xml";
                            $zip->extractTo(substr($ruta_archivo_cdr, 0, -1), 'ERROR-' . $archivo . $extension);
                        }
                        $zip->close();

                        //eliminamos los archivos Zipeados
                        unlink($ruta_archivo . '.ZIP');
                        unlink($ruta_archivo_cdr . 'ERROR-' . $archivo . '.ZIP');
                    }

                    $archivoXML = $ruta_archivo_cdr . 'ERROR-' . $archivo . $extension;
                    //=============hash CDR=================
                    $doc_cdr = new DOMDocument();
                    $doc_cdr->load($archivoXML);

                    $resp['respuesta'] = 'error';
                    $resp['cod_sunat'] = "-1000";
                    $resp['mensaje'] = "No hubo respuesta del servidor SUNAT. Intente nuevamente o verifique su conexión a INTERNET";
                    $resp['hash_cdr'] = "";
                    $resp["xml_cdr"]  = $archivoXML;
                } else {
                    //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
                    $extension = ".xml";
                    $archivoXML = $ruta_archivo_cdr . 'ERROR-' . $archivo . $extension;

                    $resp['respuesta'] = 'error';
                    $cod_sunat_full = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                    $resp['cod_sunat'] = preg_replace('/[^0-9]/', '', $cod_sunat_full);  
                    $resp['mensaje'] = str_replace("'","",$doc->getElementsByTagName('faultstring')->item(0)->nodeValue);
                    $resp['hash_cdr'] = "";
                    $resp["xml_cdr"]  = $archivoXML;

                    file_put_contents($archivoXML, $response);
                }
            }
        }
        return $resp;
    }
}