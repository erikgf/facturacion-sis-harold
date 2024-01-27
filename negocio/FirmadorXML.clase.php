<?php
require_once('../api_signature/XMLSecurityKey.php');
require_once('../api_signature/XMLSecurityDSig.php');
require_once('../api_signature/XMLSecEnc.php');

class FirmadorXML {

    public function firmar($ruta_archivo_a_firmar, $ruta_archivo_firmado,  $ruta_firma,
                                    $pass_firma, $flg_firma = "0") {
        //flg_firma:
        //          01, 03, 07, 08: Firmar en el nodo uno.
        //          00: Firmar en el Nodo Cero (para comprobantes de Percepción o Retención)
        try{

            if (!file_exists($ruta_archivo_a_firmar)){
                throw new Exception("No existe archivo que firmar", 1);
            }

            if ($pass_firma == ""){
                throw new Exception("Se esta enviando una clave vacía", 1);
            }

            if (!file_exists($ruta_firma)){
                throw new Exception("Archivo que contiene la firma digital no existe", 1);
            }

            $doc = new DOMDocument();

            $doc->load($ruta_archivo_a_firmar);

            $objDSig = new XMLSecurityDSig(FALSE);
            $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);
            $options['force_uri'] = TRUE;
            $options['id_name'] = 'ID';
            $options['overwrite'] = FALSE;

            $objDSig->addReference($doc, XMLSecurityDSig::SHA1, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'), $options);
            $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));

            $pfx = file_get_contents($ruta_firma);
            $key = array();

            openssl_pkcs12_read($pfx, $key, $pass_firma);
            $objKey->loadKey($key["pkey"]);
            $objDSig->add509Cert($key["cert"], TRUE, FALSE);
            $objDSig->sign($objKey, $doc->documentElement->getElementsByTagName("ExtensionContent")->item($flg_firma));

            $atributo = $doc->getElementsByTagName('Signature')->item(0);
            $atributo->setAttribute('Id', 'SignatureSP');
            
            //===================rescatamos Codigo(HASH_CPE) || VALOR RESUMEN==================
            $hash_cpe = $doc->getElementsByTagName('DigestValue')->item(0)->nodeValue;
            $signature_cpe = $doc->getElementsByTagName('SignatureValue')->item(0)->nodeValue;

            $doc->save($ruta_archivo_firmado);
            $resp['respuesta'] = 'ok';
            $resp['hash_cpe'] = $hash_cpe;
            $resp['signature_cpe'] = $signature_cpe;
            return $resp; 

        } catch (\Throwable $th) {
            throw $th;
         }
        
    }   
}