<?php 

require_once 'ValidacionDatos.clase.php';

class GeneradorXML {
    /*
        Recibe Datos de comprobante:
            Cabecera
                Detalle
        Nombre Archivo
        Ruta de guardado
    */
    public function crear_xml_factura($data, $nombre_archivo, $ruta) { /*SOPORTE FACT Y BOLETA*/
        $validacion = new ValidacionDatos();
        $doc = new DOMDocument();
        $doc->encoding = 'utf-8';

        $cabecera = $data;
        $detalle = $data["detalle"];
        $cuotas = $data["cuotas"];

        try {
            $xmlCPE = '<?xml version="1.0" encoding="'.$doc->encoding.'"?>
                <Invoice xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
                    <ext:UBLExtensions>
                        <ext:UBLExtension>
                            <ext:ExtensionContent>
                            </ext:ExtensionContent>
                        </ext:UBLExtension>
                    </ext:UBLExtensions>
                    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
                    <cbc:CustomizationID schemeAgencyName="PE:SUNAT">2.0</cbc:CustomizationID>
                    <cbc:ProfileID schemeName="Tipo de Operacion" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51">' . $cabecera["TIPO_OPERACION"] . '</cbc:ProfileID>
                    <cbc:ID>' . $cabecera["NRO_COMPROBANTE"] . '</cbc:ID>
                    <cbc:IssueDate>' . $cabecera["FECHA_DOCUMENTO"] . '</cbc:IssueDate>
                    <cbc:IssueTime>' . $cabecera["HORA_DOCUMENTO"] . '</cbc:IssueTime>
                    <cbc:DueDate>' . $cabecera["FECHA_VTO"] . '</cbc:DueDate>';
                    
                    $xmlCPE .=
                    '<cbc:InvoiceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" listID="0101" name="Tipo de Operacion" listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51">' . $cabecera["COD_TIPO_DOCUMENTO"] . '</cbc:InvoiceTypeCode>';

                    if ($cabecera["TOTAL_LETRAS"] <> "") {
                            $xmlCPE = $xmlCPE .
                                '<cbc:Note languageLocaleID="1000">' . $cabecera["TOTAL_LETRAS"] . '</cbc:Note>';
                        }

                    if (isset($cabecera["OBSERVACIONES"]) && $cabecera["OBSERVACIONES"] <> "") {
                            $xmlCPE = $xmlCPE .
                                '<cbc:Note><![CDATA[' . $validacion->replace_invalid_caracters($cabecera["OBSERVACIONES"]) . ']]></cbc:Note>';
                    }    

                        $xmlCPE = $xmlCPE .
                                '<cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency" listAgencyName="United Nations Economic Commission for Europe">' . $cabecera["COD_MONEDA"] . '</cbc:DocumentCurrencyCode>
                            <cbc:LineCountNumeric>' . count($detalle) . '</cbc:LineCountNumeric>';
                        if ($cabecera["NRO_OTR_COMPROBANTE"] <> "") {
                            $xmlCPE = $xmlCPE .
                                    '<cac:OrderReference>
                                    <cbc:ID>' . $cabecera["NRO_OTR_COMPROBANTE"] . '</cbc:ID>
                            </cac:OrderReference>';
                        }
                        if ($cabecera["NRO_GUIA_REMISION"] <> "") {
                        $xmlCPE = $xmlCPE .
                                '<cac:DespatchDocumentReference>
                        <cbc:ID>' . $cabecera["NRO_GUIA_REMISION"] . '</cbc:ID>
                        <cbc:IssueDate>' . $cabecera["FECHA_GUIA_REMISION"] . '</cbc:IssueDate>
                        <cbc:DocumentTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01">' . $cabecera["COD_GUIA_REMISION"] . '</cbc:DocumentTypeCode>
                            </cac:DespatchDocumentReference>';
                        }

                        if (isset($cabecera["TOTAL_ANTICIPOS"]) && $cabecera["TOTAL_ANTICIPOS"] > 0.00){
                            $xmlCPE  .= '
                        <cac:AdditionalDocumentReference>
                            <cbc:ID schemeID="01">'.$cabecera["ANTICIPO_COMPROBANTE"].'</cbc:ID>
                            <cbc:DocumentTypeCode listAgencyName="PE:SUNAT" listName="Documento Relacionado" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo12">'.$cabecera["ANTICIPO_TIPO_COMPROBANTE"].'</cbc:DocumentTypeCode>
                            <cbc:DocumentStatusCode listName="Anticipo" listAgencyName="PE:SUNAT">1</cbc:DocumentStatusCode>
                            <cac:IssuerParty>
                            <cac:PartyIdentification>
                                <cbc:ID schemeAgencyName="PE:SUNAT" schemeID="6" schemeName="Documento de Identidad" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">'.$cabecera["NRO_DOCUMENTO_EMPRESA"].'</cbc:ID>
                            </cac:PartyIdentification>
                            </cac:IssuerParty>
                        </cac:AdditionalDocumentReference>';
                        }

                        $xmlCPE .= '
                    <cac:Signature>
                        <cbc:ID>' . $cabecera["NRO_COMPROBANTE"] . '</cbc:ID>
                        <cac:SignatoryParty>
                            <cac:PartyIdentification>
                                <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
                            </cac:PartyIdentification>
                            <cac:PartyName>
                                <cbc:Name>' . $cabecera["RAZON_SOCIAL_EMPRESA"] . '</cbc:Name>
                            </cac:PartyName>
                        </cac:SignatoryParty>
                        <cac:DigitalSignatureAttachment>
                            <cac:ExternalReference>
                                <cbc:URI>#' . $cabecera["NRO_COMPROBANTE"] . '</cbc:URI>
                            </cac:ExternalReference>
                        </cac:DigitalSignatureAttachment>
                    </cac:Signature>
                    <cac:AccountingSupplierParty>
                        <cac:Party>
                            <cac:PartyIdentification>
                                <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
                            </cac:PartyIdentification>
                            <cac:PartyName>
                                <cbc:Name><![CDATA[' . $cabecera["NOMBRE_COMERCIAL_EMPRESA"] . ']]></cbc:Name>
                            </cac:PartyName>
                            <cac:PartyTaxScheme>
                                <cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:RegistrationName>
                                <cbc:CompanyID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:CompanyID>
                                <cac:TaxScheme>
                                    <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
                                </cac:TaxScheme>
                            </cac:PartyTaxScheme>
                            <cac:PartyLegalEntity>
                                <cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:RegistrationName>
                                <cac:RegistrationAddress>
                                    <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">'.$cabecera["CODIGO_UBIGEO_EMPRESA"].'</cbc:ID>
                                    <cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">0000</cbc:AddressTypeCode>';


                        if ( $cabecera["URBANIZACION_EMPRESA"] != NULL ){
                            $xmlCPE .= '<cbc:CitySubdivisionName><![CDATA['.  $cabecera["URBANIZACION_EMPRESA"] . ']]></cbc:CitySubdivisionName>';
                        }
                                    
                            $xmlCPE .= '<cbc:CityName><![CDATA[' . $cabecera["DEPARTAMENTO_EMPRESA"] . ']]></cbc:CityName>
                                    <cbc:CountrySubentity><![CDATA[' . $cabecera["PROVINCIA_EMPRESA"] . ']]></cbc:CountrySubentity>
                                    <cbc:District><![CDATA[' . $cabecera["DISTRITO_EMPRESA"] . ']]></cbc:District>
                                    <cac:AddressLine>
                                        <cbc:Line><![CDATA[' . $cabecera["DIRECCION_EMPRESA"] . ']]></cbc:Line>
                                    </cac:AddressLine>
                                    <cac:Country>
                                        <cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">' . $cabecera["CODIGO_PAIS_EMPRESA"] . '</cbc:IdentificationCode>
                                    </cac:Country>
                                </cac:RegistrationAddress>
                            </cac:PartyLegalEntity>
                            <cac:Contact>
                                <cbc:Name><![CDATA[' . ($cabecera["CONTACTO_EMPRESA"] == NULL ? '' : $cabecera["CONTACTO_EMPRESA"]). ']]></cbc:Name>
                            </cac:Contact>
                        </cac:Party>
                    </cac:AccountingSupplierParty>
                    <cac:AccountingCustomerParty>
                        <cac:Party>
                            <cac:PartyIdentification>';

                        if (!isset($cabecera["NRO_DOCUMENTO_CLIENTE"]) || $cabecera["TIPO_DOCUMENTO_CLIENTE"] == "0" || $cabecera["NRO_DOCUMENTO_CLIENTE"] == "" || $cabecera["NRO_DOCUMENTO_CLIENTE"] == "0" || $cabecera["NRO_DOCUMENTO_CLIENTE"] == "00000000"){
                            $NRO_DOCUMENTO_CLIENTE = "-";
                            $RAZON_SOCIAL_CLIENTE = "CLIENTES VARIOS";
                        } else {
                            $NRO_DOCUMENTO_CLIENTE = $cabecera["NRO_DOCUMENTO_CLIENTE"];
                            $RAZON_SOCIAL_CLIENTE = $cabecera["RAZON_SOCIAL_CLIENTE"];
                        }

                        $xmlCPE .= '
                                <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $NRO_DOCUMENTO_CLIENTE . '</cbc:ID>
                            </cac:PartyIdentification>
                            <cac:PartyName>
                                <cbc:Name><![CDATA[' . $RAZON_SOCIAL_CLIENTE . ']]></cbc:Name>
                            </cac:PartyName>
                            <cac:PartyTaxScheme>
                                <cbc:RegistrationName><![CDATA[' . $RAZON_SOCIAL_CLIENTE . ']]></cbc:RegistrationName>
                                <cbc:CompanyID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $NRO_DOCUMENTO_CLIENTE . '</cbc:CompanyID>
                                <cac:TaxScheme>
                                    <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $NRO_DOCUMENTO_CLIENTE. '</cbc:ID>
                                </cac:TaxScheme>
                            </cac:PartyTaxScheme>
                            <cac:PartyLegalEntity>
                                <cbc:RegistrationName><![CDATA[' . $RAZON_SOCIAL_CLIENTE . ']]></cbc:RegistrationName>
                                <cac:RegistrationAddress>
                                    <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">' . $cabecera["COD_UBIGEO_CLIENTE"] . '</cbc:ID>';

                            if (isset($cabecera["DEPARTAMENTO_CLIENTE"]) && $cabecera["DEPARTAMENTO_CLIENTE"] != NULL){
                                $xmlCPE .= '<cbc:CityName><![CDATA[' . $cabecera["DEPARTAMENTO_CLIENTE"] . ']]></cbc:CityName>';
                            }

                            if (isset($cabecera["PROVINCIA_CLIENTE"]) && $cabecera["PROVINCIA_CLIENTE"] != NULL){
                                $xmlCPE .= '<cbc:CountrySubentity><![CDATA[' . $cabecera["PROVINCIA_CLIENTE"] . ']]></cbc:CountrySubentity>';
                            }

                            if (isset($cabecera["DISTRITO_CLIENTE"]) && $cabecera["DISTRITO_CLIENTE"] != NULL){
                                $xmlCPE .= '<cbc:District><![CDATA[' . $cabecera["DISTRITO_CLIENTE"] . ']]></cbc:District>';
                            }

                            $xmlCPE .= ' <cac:AddressLine>
                                            <cbc:Line><![CDATA[' . $cabecera["DIRECCION_CLIENTE"] . ']]></cbc:Line>
                                        </cac:AddressLine> 
                                    <cac:Country>
                                        <cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">' . $cabecera["COD_PAIS_CLIENTE"] . '</cbc:IdentificationCode>
                                    </cac:Country>
                                </cac:RegistrationAddress>
                            </cac:PartyLegalEntity>
                        </cac:Party>
                    </cac:AccountingCustomerParty>';

                    if ($cabecera["CONDICION_PAGO"] == "1"){
                        $xmlCPE .= '
                        <cac:PaymentTerms>
                            <cbc:ID>FormaPago</cbc:ID>
                            <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
                        </cac:PaymentTerms>';
                    }  else {
                        $xmlCPE .= '
                        <cac:PaymentTerms>
                            <cbc:ID>FormaPago</cbc:ID>
                            <cbc:PaymentMeansID>Credito</cbc:PaymentMeansID>
                            <cbc:Amount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cabecera["TOTAL_CREDITO"].'</cbc:Amount>
                        </cac:PaymentTerms>';

                        foreach ($cuotas as $id_cuota => $cuota) {
                            $xmlCPE .= '
                            <cac:PaymentTerms>
                                <cbc:ID>FormaPago</cbc:ID>
                                <cbc:PaymentMeansID>'.$cuota["NUMERO_CUOTA"].'</cbc:PaymentMeansID>
                                <cbc:Amount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cuota["MONTO_CUOTA"].'</cbc:Amount>
                                <cbc:PaymentDueDate>'.$cuota["FECHA_VENCIMIENTO"].'</cbc:PaymentDueDate>
                            </cac:PaymentTerms>';
                        }
                    }

                    if (isset($cabecera["TOTAL_ANTICIPOS"]) && $cabecera["TOTAL_ANTICIPOS"] > 0.00){
                        /*Anticipos !*/
                    $xmlCPE .='
                    <cac:PrepaidPayment>
                        <cbc:ID schemeName="Anticipo" schemeAgencyName="PE:SUNAT">1</cbc:ID>
                        <cbc:PaidAmount currencyID="' . $cabecera["COD_MONEDA"] . '">'.$cabecera["TOTAL_ANTICIPOS"].'</cbc:PaidAmount>
                    </cac:PrepaidPayment>';
                        /*Anticipos*/
                    }

                    if (isset($cabecera["POR_DESCUENTO"]) && $cabecera["POR_DESCUENTO"] > 0.00){
                    /*Descuento Global*/
                        $xmlCPE .='
                    <cac:AllowanceCharge>
                        <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
                        <cbc:AllowanceChargeReasonCode>02</cbc:AllowanceChargeReasonCode>
                        <cbc:MultiplierFactorNumeric>1</cbc:MultiplierFactorNumeric>
                        <cbc:Amount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["DESCUENTO_GLOBAL"] . '</cbc:Amount>
                        <cbc:BaseAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["DESCUENTO_GLOBAL"] . '</cbc:BaseAmount>
                    </cac:AllowanceCharge>';

                    /*Descuento Global*/
                    }

                    /*Impuesto a GRAVADAS, (EXOERADAS, INAFECTAS)=> opcional)*/
                    $xmlCPE = $xmlCPE .'
                    <cac:TaxTotal>';

                    if (isset($cabecera["TOTAL_IGV"]) && $cabecera["TOTAL_IGV"] > 0.00){
                        $xmlCPE = $xmlCPE .'    
                        <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
                        <cac:TaxSubtotal>
                            <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_GRAVADAS"] . '</cbc:TaxableAmount>
                            <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
                            <cac:TaxCategory>
                                <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">S</cbc:ID>
                                <cac:TaxScheme>
                                    <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">1000</cbc:ID>
                                    <cbc:Name>IGV</cbc:Name>
                                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                                </cac:TaxScheme>
                            </cac:TaxCategory>
                        </cac:TaxSubtotal>';
                    }

                    if (isset($cabecera["TOTAL_EXONERADAS"]) && $cabecera["TOTAL_EXONERADAS"] > 0.00){
                        $xmlCPE = $xmlCPE .'    
                        <cac:TaxSubtotal>
                            <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_EXONERADAS"] . '</cbc:TaxableAmount>
                            <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:TaxAmount>
                            <cac:TaxCategory>
                                <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">E</cbc:ID>
                                <cac:TaxScheme>
                                    <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9997</cbc:ID>
                                    <cbc:Name>EXO</cbc:Name>
                                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                                </cac:TaxScheme>
                            </cac:TaxCategory>
                        </cac:TaxSubtotal>';
                    }

                    if (isset($cabecera["TOTAL_INAFECTA"]) && $cabecera["TOTAL_INAFECTA"] > 0.00){
                        $xmlCPE = $xmlCPE .'    
                        <cac:TaxSubtotal>
                            <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_INAFECTA"] . '</cbc:TaxableAmount>
                            <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:TaxAmount>
                            <cac:TaxCategory>
                                <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">O</cbc:ID>
                                <cac:TaxScheme>
                                    <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9998</cbc:ID>
                                    <cbc:Name>INA</cbc:Name>
                                    <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                                </cac:TaxScheme>
                            </cac:TaxCategory>
                        </cac:TaxSubtotal>';
                    }

                    $xmlCPE = $xmlCPE .'
                    </cac:TaxTotal>';   

                    //TOTAL=GRAVADA+IGV+EXONERADA
                    //NO ENTRA GRATUITA(INAFECTA) NI DESCUENTO
                    //SUB_TOTAL=PRECIO(SIN IGV) * CANTIDAD

                    $xmlCPE .= '
                    <cac:LegalMonetaryTotal>
                        <cbc:LineExtensionAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format($cabecera["SUB_TOTAL"],2, '.', '') . '</cbc:LineExtensionAmount>
                        <cbc:TaxInclusiveAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format($cabecera["TOTAL"],2, '.', '') . '</cbc:TaxInclusiveAmount>';
                        /*
                        <cbc:AllowanceTotalAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format($cabecera["TOTAL"],2, '.', '') . '</cbc:AllowanceTotalAmount>';
                        <cbc:ChargeTotalAmount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:ChargeTotalAmount>';
                        */

                    if (isset($cabecera["TOTAL_ANTICIPOS"]) && $cabecera["TOTAL_ANTICIPOS"] > 0.00){
                        /*nota, la proxima intentar con el monto tras pasar el IGV.*/
                    $xmlCPE.= '
                        <cbc:PrepaidAmount currencyID="' . $cabecera["COD_MONEDA"] . '">'.$cabecera["TOTAL_ANTICIPOS"].'</cbc:PrepaidAmount>';
                    }

                    $xmlCPE .='
                        <cbc:PayableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL"] . '</cbc:PayableAmount>';
                    $xmlCPE .='
                    </cac:LegalMonetaryTotal>';

                    for ($i = 0; $i < count($detalle); $i++) {
                        /*2.1.1: Se agregó el bloque Allowance Charge para la emisión de decuenos por ITEM. Recibe ChangeReasone 00, por otros DESCUENTOS*/     
                        $xmlCPE = $xmlCPE . '
                    <cac:InvoiceLine>
                        <cbc:ID>' . $detalle[$i]["txtITEM"] . '</cbc:ID>
                        <cbc:InvoicedQuantity unitCode="' . $detalle[$i]["txtUNIDAD_MEDIDA_DET"] . '" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">' . $detalle[$i]["txtCANTIDAD_DET"] . '</cbc:InvoicedQuantity>
                        <cbc:LineExtensionAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format(abs($detalle[$i]["txtIMPORTE_DET"]),2, '.', '') . '</cbc:LineExtensionAmount>
                        <cac:PricingReference>
                            <cac:AlternativeConditionPrice>
                                <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format(abs($detalle[$i]["txtPRECIO_DET"]),4, '.', '') . '</cbc:PriceAmount>
                                <cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">' . $detalle[$i]["txtPRECIO_TIPO_CODIGO"] . '</cbc:PriceTypeCode>
                            </cac:AlternativeConditionPrice>
                        </cac:PricingReference>';


                        /*No funcionará DESCUENTO POR ITEM*/
                        /*
                        $xmlCPE .= '
                        <cac:AllowanceCharge>
                            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
                            <cbc:AllowanceChargeReasonCode>00</cbc:AllowanceChargeReasonCode>
                            <cbc:MultiplierFactorNumeric>'.$detalle[$i]["txtPOR_DESCUENTO_DET"].'</cbc:MultiplierFactorNumeric>
                            <cbc:Amount currencyID="' . $cabecera["COD_MONEDA"] . '">'.$detalle[$i]["txtDESCUENTO_DET"].'</cbc:Amount>
                            <cbc:BaseAmount currencyID="' . $cabecera["COD_MONEDA"] . '">'.abs($detalle[$i]["txtVALOR_VENTA_BRUTO"]).'</cbc:BaseAmount>
                        </cac:AllowanceCharge>';
                        */
                        /*No funcionará DESCUENTO POR ITEM*/

                        /*Esto solo funciona para IGV, se necesita implementar para ISC.*/
                        $xmlCPE .= '
                        <cac:TaxTotal>
                            <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format(abs($detalle[$i]["txtIGV"]),2, '.', '') . '</cbc:TaxAmount>
                            <cac:TaxSubtotal>
                                <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format(abs($detalle[$i]["txtIMPORTE_DET"]),2, '.', '') . '</cbc:TaxableAmount>
                                <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format(abs($detalle[$i]["txtIGV"]),2, '.', '') . '</cbc:TaxAmount>
                                <cac:TaxCategory>
                                    <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">S</cbc:ID>
                                    <cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
                                    <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . $detalle[$i]["txtCOD_TIPO_OPERACION"] . '</cbc:TaxExemptionReasonCode>
                                    <cac:TaxScheme>
                                        <cbc:ID schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05" schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT">1000</cbc:ID>
                                        <cbc:Name>IGV</cbc:Name>
                                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                                    </cac:TaxScheme>
                                </cac:TaxCategory>
                            </cac:TaxSubtotal>
                        </cac:TaxTotal>';

                        $xmlCPE .= '
                        <cac:Item>
                            <cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]["txtDESCRIPCION_DET"])) ? $detalle[$i]["txtDESCRIPCION_DET"] : "") . ']]></cbc:Description>';

                        $codigo_detalle = $validacion->replace_invalid_caracters((isset($detalle[$i]["txtCODIGO_DET"]))?$detalle[$i]["txtCODIGO_DET"]:"");
                        if (strlen($codigo_detalle) > 0){
                            $xmlCPE = $xmlCPE . '<cac:SellersItemIdentification>
                                        <cbc:ID><![CDATA[' . $codigo_detalle . ']]></cbc:ID>
                                    </cac:SellersItemIdentification>';
                        }
                            
                        if (isset($detalle[$i]['txtCODIGO_PROD_SUNAT'])){
                            $xmlCPE = $xmlCPE .'
                                <cac:CommodityClassification>
                                        <cbc:ItemClassificationCode listID="UNSPSC" listAgencyName="GS1 US" listName="Item Classification">'.$detalle[$i]['txtCODIGO_PROD_SUNAT'].'</cbc:ItemClassificationCode>
                                </cac:CommodityClassification>';
                        }

                        $xmlCPE .= '
                        </cac:Item>
                        <cac:Price>
                            <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format(round(abs($detalle[$i]["txtPRECIO_SIN_IGV_DET"]),4),4, '.', '') . '</cbc:PriceAmount>
                        </cac:Price>
                    </cac:InvoiceLine>';
                    }

                    $xmlCPE .= '
                </Invoice>';
            $doc->loadXML($xmlCPE);
            $doc->save($ruta ."/". $nombre_archivo);

            
            $resp["xml_filename"] =  basename($nombre_archivo, '.XML').'.XML';
            $resp['respuesta'] = 'ok';
            $resp["creado"]  = "1";
            $resp["ruta"] = $ruta;
            return $resp;
        } catch (\Throwable $th) {
           throw $th;
        }
    }

    public function crear_xml_nota_credito($data, $nombre_archivo, $ruta) {
        $validacion = new ValidacionDatos();
        $doc = new DOMDocument();
        $doc->encoding = 'utf-8';
        
        $cabecera = $data;
        $detalle = $data["detalle"];
        
        try{
            $xmlCPE = '<?xml version="1.0" encoding="UTF-8"?>
            <CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <ext:UBLExtensions>
                    <ext:UBLExtension>
                        <ext:ExtensionContent>
                        </ext:ExtensionContent>
                    </ext:UBLExtension>
                </ext:UBLExtensions>
                <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
                <cbc:CustomizationID>2.0</cbc:CustomizationID>
                <cbc:ID>'.$cabecera["NRO_COMPROBANTE"].'</cbc:ID>
                <cbc:IssueDate>'.$cabecera["FECHA_DOCUMENTO"].'</cbc:IssueDate>
                <cbc:IssueTime>00:00:00</cbc:IssueTime>
                <cbc:DocumentCurrencyCode>'.$cabecera["COD_MONEDA"].'</cbc:DocumentCurrencyCode>
                <cac:DiscrepancyResponse>
                    <cbc:ReferenceID>'.$cabecera["NRO_DOCUMENTO_MODIFICA"].'</cbc:ReferenceID>
                    <cbc:ResponseCode>'.$cabecera["COD_TIPO_MOTIVO"].'</cbc:ResponseCode>
                    <cbc:Description><![CDATA['.$cabecera["DESCRIPCION_MOTIVO"].']]></cbc:Description>
                </cac:DiscrepancyResponse>
                <cac:BillingReference>
                    <cac:InvoiceDocumentReference>
                        <cbc:ID>'.$cabecera["NRO_DOCUMENTO_MODIFICA"].'</cbc:ID>
                        <cbc:DocumentTypeCode>'.$cabecera["TIPO_COMPROBANTE_MODIFICA"].'</cbc:DocumentTypeCode>
                    </cac:InvoiceDocumentReference>
                </cac:BillingReference>
                <cac:Signature>
                    <cbc:ID>IDSignST</cbc:ID>
                    <cac:SignatoryParty>
                        <cac:PartyIdentification>
                            <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:Name>
                        </cac:PartyName>
                    </cac:SignatoryParty>
                    <cac:DigitalSignatureAttachment>
                        <cac:ExternalReference>
                            <cbc:URI>#SignatureSP</cbc:URI>
                        </cac:ExternalReference>
                    </cac:DigitalSignatureAttachment>
                </cac:Signature>
                <cac:AccountingSupplierParty>
                    <cac:Party>
                        <cac:PartyIdentification>
                            <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">'.$cabecera["NRO_DOCUMENTO_EMPRESA"].'</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA[' . $cabecera["NOMBRE_COMERCIAL_EMPRESA"] . ']]></cbc:Name>
                        </cac:PartyName>
                        <cac:PartyLegalEntity>
            <cbc:RegistrationName><![CDATA['.$cabecera["RAZON_SOCIAL_EMPRESA"].']]></cbc:RegistrationName>
                            <cac:RegistrationAddress>
                                <cbc:AddressTypeCode>0001</cbc:AddressTypeCode>
                            </cac:RegistrationAddress>
                        </cac:PartyLegalEntity>
                    </cac:Party>
                </cac:AccountingSupplierParty>
                <cac:AccountingCustomerParty>
                    <cac:Party>
                        <cac:PartyIdentification>
                            <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_CLIENTE"] . '</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyLegalEntity>
            <cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_CLIENTE"] . ']]></cbc:RegistrationName>
                        </cac:PartyLegalEntity>
                    </cac:Party>
                </cac:AccountingCustomerParty>
                <cac:TaxTotal>
                    <cbc:TaxAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cabecera["TOTAL_IGV"].'</cbc:TaxAmount>
                    <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cabecera["TOTAL_GRAVADAS"].'</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cabecera["TOTAL_IGV"].'</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">1000</cbc:ID>
                                <cbc:Name>IGV</cbc:Name>
                                <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>
                </cac:TaxTotal>
                <cac:LegalMonetaryTotal>
                    <cbc:PayableAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cabecera["TOTAL"].'</cbc:PayableAmount>
                </cac:LegalMonetaryTotal>';
                
                

            for ($i = 0; $i < count($detalle); $i++) {
                $xmlCPE = $xmlCPE .'<cac:CreditNoteLine>
                        <cbc:ID>'.$detalle[$i]["txtITEM"].'</cbc:ID>
                <cbc:CreditedQuantity unitCode="' . $detalle[$i]["txtUNIDAD_MEDIDA_DET"] . '">' . $detalle[$i]["txtCANTIDAD_DET"] . '</cbc:CreditedQuantity>
                <cbc:LineExtensionAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtIMPORTE_DET"].'</cbc:LineExtensionAmount>
                        <cac:PricingReference>
                            <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtPRECIO_DET"].'</cbc:PriceAmount>
                                <cbc:PriceTypeCode>'.$detalle[$i]["txtPRECIO_TIPO_CODIGO"].'</cbc:PriceTypeCode>
                            </cac:AlternativeConditionPrice>
                        </cac:PricingReference>
                        <cac:TaxTotal>
                <cbc:TaxAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtIGV"].'</cbc:TaxAmount>
                            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtIMPORTE_DET"].'</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtIGV"].'</cbc:TaxAmount>
                                <cac:TaxCategory>
                                    <cbc:Percent>'.$cabecera["POR_IGV"].'</cbc:Percent>
                <cbc:TaxExemptionReasonCode>'.$detalle[$i]["txtCOD_TIPO_OPERACION"].'</cbc:TaxExemptionReasonCode>
                                    <cac:TaxScheme>
                                        <cbc:ID>1000</cbc:ID>
                                        <cbc:Name>IGV</cbc:Name>
                                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                                    </cac:TaxScheme>
                                </cac:TaxCategory>
                            </cac:TaxSubtotal>
                        </cac:TaxTotal>
                        <cac:Item>
                <cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]["txtDESCRIPCION_DET"]))?$detalle[$i]["txtDESCRIPCION_DET"]:"") . ']]></cbc:Description>';

                $codigo_detalle = $validacion->replace_invalid_caracters((isset($detalle[$i]["txtCODIGO_DET"]))?$detalle[$i]["txtCODIGO_DET"]:"");
                if (strlen($codigo_detalle) > 0){
                    $xmlCPE = $xmlCPE . '<cac:SellersItemIdentification>
                                <cbc:ID><![CDATA[' . $codigo_detalle . ']]></cbc:ID>
                            </cac:SellersItemIdentification>';
                }
                
                $codigo_sunat = $validacion->replace_invalid_caracters((isset($detalle[$i]["txtCODIGO_PROD_SUNAT"]))?$detalle[$i]["txtCODIGO_PROD_SUNAT"]:"");
                if ($codigo_sunat != NULL && $codigo_sunat != ""){
                    $xmlCPE = $xmlCPE . '   <cac:CommodityClassification>
                                <cbc:ItemClassificationCode listID="UNSPSC" listAgencyName="GS1 US" listName="Item Classification">'.$codigo_sunat.'</cbc:ItemClassificationCode>
                            </cac:CommodityClassification>';
                }

                $xmlCPE = $xmlCPE . '</cac:Item>
                        <cac:Price>
                        <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format(round(abs($detalle[$i]["txtPRECIO_SIN_IGV_DET"]),4),4, '.', '') . '</cbc:PriceAmount>
                        </cac:Price>
                    </cac:CreditNoteLine>';
                        
            }

            $xmlCPE = $xmlCPE . '</CreditNote>';
            $doc->loadXML($xmlCPE);
            $doc->save($ruta ."/". $nombre_archivo);
            $resp["xml_filename"] =  basename($nombre_archivo, '.XML').'.XML';
            $resp['respuesta'] = 'ok';
            $resp["creado"]  = "1";
            $resp["ruta"] = $ruta;
            return $resp;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function crear_xml_nota_debito($data, $nombre_archivo, $ruta) {
        $validacion = new ValidacionDatos();
        $doc = new DOMDocument();
        $doc->encoding = 'utf-8';
        
        $cabecera = $data;
        $detalle = $data["detalle"];

        try{
            $xmlCPE = '<?xml version="1.0" encoding="UTF-8"?>
            <DebitNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <ext:UBLExtensions>
                    <ext:UBLExtension>
                        <ext:ExtensionContent>
                        </ext:ExtensionContent>
                    </ext:UBLExtension>
                </ext:UBLExtensions>
                <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
                <cbc:CustomizationID>2.0</cbc:CustomizationID>
                <cbc:ID>'.$cabecera["NRO_COMPROBANTE"].'</cbc:ID>
                <cbc:IssueDate>'.$cabecera["FECHA_DOCUMENTO"].'</cbc:IssueDate>
                <cbc:IssueTime>00:00:00</cbc:IssueTime>
                <cbc:DocumentCurrencyCode>'.$cabecera["COD_MONEDA"].'</cbc:DocumentCurrencyCode>
                <cac:DiscrepancyResponse>
                    <cbc:ReferenceID>'.$cabecera["NRO_DOCUMENTO_MODIFICA"].'</cbc:ReferenceID>
                    <cbc:ResponseCode>'.$cabecera["COD_TIPO_MOTIVO"].'</cbc:ResponseCode>
                    <cbc:Description><![CDATA['.$cabecera["DESCRIPCION_MOTIVO"].']]></cbc:Description>
                </cac:DiscrepancyResponse>
                <cac:BillingReference>
                    <cac:InvoiceDocumentReference>
                        <cbc:ID>'.$cabecera["NRO_DOCUMENTO_MODIFICA"].'</cbc:ID>
                        <cbc:DocumentTypeCode>'.$cabecera["TIPO_COMPROBANTE_MODIFICA"].'</cbc:DocumentTypeCode>
                    </cac:InvoiceDocumentReference>
                </cac:BillingReference>
                <cac:Signature>
                    <cbc:ID>IDSignST</cbc:ID>
                    <cac:SignatoryParty>
                        <cac:PartyIdentification>
                            <cbc:ID>'.$cabecera["NRO_DOCUMENTO_EMPRESA"].'</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA['.$cabecera["RAZON_SOCIAL_EMPRESA"].']]></cbc:Name>
                        </cac:PartyName>
                    </cac:SignatoryParty>
                    <cac:DigitalSignatureAttachment>
                        <cac:ExternalReference>
                            <cbc:URI>#SignatureSP</cbc:URI>
                        </cac:ExternalReference>
                    </cac:DigitalSignatureAttachment>
                </cac:Signature>
                <cac:AccountingSupplierParty>
                    <cac:Party>
                        <cac:PartyIdentification>
                            <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">'.$cabecera["NRO_DOCUMENTO_EMPRESA"].'</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA[' . $cabecera["NOMBRE_COMERCIAL_EMPRESA"] . ']]></cbc:Name>
                        </cac:PartyName>
                        <cac:PartyLegalEntity>
                            <cbc:RegistrationName><![CDATA['.$cabecera["RAZON_SOCIAL_EMPRESA"].']]></cbc:RegistrationName>
                            <cac:RegistrationAddress>
                                <cbc:AddressTypeCode>0001</cbc:AddressTypeCode>
                            </cac:RegistrationAddress>
                        </cac:PartyLegalEntity>
                    </cac:Party>
                </cac:AccountingSupplierParty>
                <cac:AccountingCustomerParty>
                    <cac:Party>
                        <cac:PartyIdentification>
                            <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">'.$cabecera["NRO_DOCUMENTO_CLIENTE"].'</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyLegalEntity>
            <cbc:RegistrationName><![CDATA['.$cabecera["RAZON_SOCIAL_CLIENTE"].']]></cbc:RegistrationName>
                        </cac:PartyLegalEntity>
                    </cac:Party>
                </cac:AccountingCustomerParty>
                <cac:TaxTotal>
                    <cbc:TaxAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cabecera["TOTAL_IGV"].'</cbc:TaxAmount>
                    <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cabecera["TOTAL_GRAVADAS"].'</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cabecera["TOTAL_IGV"].'</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">1000</cbc:ID>
                                <cbc:Name>IGV</cbc:Name>
                                <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>
                </cac:TaxTotal>
                <cac:RequestedMonetaryTotal>
            <cbc:PayableAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$cabecera["TOTAL"].'</cbc:PayableAmount>
            </cac:RequestedMonetaryTotal>';
        
            for ($i = 0; $i < count($detalle); $i++) {
                    $xmlCPE = $xmlCPE . '
                <cac:DebitNoteLine>
                    <cbc:ID>'.$detalle[$i]["txtITEM"].'</cbc:ID>
                    <cbc:DebitedQuantity unitCode="' . $detalle[$i]["txtUNIDAD_MEDIDA_DET"] . '">'.$detalle[$i]["txtCANTIDAD_DET"].'</cbc:DebitedQuantity>
                    <cbc:LineExtensionAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtIMPORTE_DET"].'</cbc:LineExtensionAmount>
                            <cac:PricingReference>
                                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtPRECIO_DET"].'</cbc:PriceAmount>
                    <cbc:PriceTypeCode>'.$detalle[$i]["txtPRECIO_TIPO_CODIGO"].'</cbc:PriceTypeCode>
                                </cac:AlternativeConditionPrice>
                            </cac:PricingReference>
                            <cac:TaxTotal>      
                    <cbc:TaxAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtIGV"].'</cbc:TaxAmount>
                                <cac:TaxSubtotal>
                                    <cbc:TaxableAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtIMPORTE_DET"].'</cbc:TaxableAmount>
                                    <cbc:TaxAmount currencyID="'.$cabecera["COD_MONEDA"].'">'.$detalle[$i]["txtIGV"].'</cbc:TaxAmount>
                                    <cac:TaxCategory>
                                        <cbc:Percent>'.$cabecera["POR_IGV"].'</cbc:Percent>
                    <cbc:TaxExemptionReasonCode>'.$detalle[$i]["txtCOD_TIPO_OPERACION"].'</cbc:TaxExemptionReasonCode>
                                        <cac:TaxScheme>
                                            <cbc:ID>1000</cbc:ID>
                                            <cbc:Name>IGV</cbc:Name>
                                            <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                                        </cac:TaxScheme>
                                    </cac:TaxCategory>
                                </cac:TaxSubtotal>
                            </cac:TaxTotal>
                            
                    <cac:Item>
                    <cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]["txtDESCRIPCION_DET"]))?$detalle[$i]["txtDESCRIPCION_DET"]:"") . ']]></cbc:Description>
                                <cac:SellersItemIdentification>
                                    <cbc:ID><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]["txtCODIGO_DET"]))?$detalle[$i]["txtCODIGO_DET"]:"") . ']]></cbc:ID>
                                </cac:SellersItemIdentification>
                                <cac:CommodityClassification>
                                    <cbc:ItemClassificationCode listID="UNSPSC" listAgencyName="GS1 US" listName="Item Classification">'.$detalle[$i]['txtCODIGO_PROD_SUNAT'].'</cbc:ItemClassificationCode>
                                </cac:CommodityClassification>
                            </cac:Item>
                    <cac:Price>
                    <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . number_format(round(abs($detalle[$i]["txtPRECIO_SIN_IGV_DET"]),4),4, '.', '') . '</cbc:PriceAmount>
                    </cac:Price>
                </cac:DebitNoteLine>';
            }
            
            $xmlCPE = $xmlCPE . '</DebitNote>';

            $doc->loadXML($xmlCPE);
            $doc->save($ruta ."/". $nombre_archivo);
            $resp["xml_filename"] = basename($nombre_archivo, '.XML').'.XML';
            $resp['respuesta'] = 'ok';
            $resp["creado"]  = "1";
            $resp["ruta"] = $ruta;
            return $resp;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function crear_xml_resumen_documentos($data, $nombre_archivo, $ruta){
        $validacion = new ValidacionDatos();
        $doc = new DOMDocument();
        $doc->encoding = 'utf-8';

        /*REFERENCE DATE = FECHA EMISION: EN ESA SE BASA EL NOMBRE DEL ENVIO */ 
        $cabecera = $data;
        $detalle = $data["detalle"];

        try{
                $xmlCPE = '<?xml version="1.0" encoding="iso-8859-1" standalone="no"?>
            <SummaryDocuments 
            xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1" 
            xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" 
            xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" 
            xmlns:ds="http://www.w3.org/2000/09/xmldsig#" 
            xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" 
            xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1"
            xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" 
            xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2">
            <ext:UBLExtensions>
                <ext:UBLExtension>
                                <ext:ExtensionContent>
                </ext:ExtensionContent>
                </ext:UBLExtension>
            </ext:UBLExtensions>
            <cbc:UBLVersionID>2.0</cbc:UBLVersionID>
            <cbc:CustomizationID>1.1</cbc:CustomizationID>
            <cbc:ID>'.$cabecera["CODIGO"].'-'.$cabecera["SERIE"].'-'.$cabecera["SECUENCIA"].'</cbc:ID>
            <cbc:ReferenceDate>'.$cabecera["FECHA_REFERENCIA"].'</cbc:ReferenceDate>
            <cbc:IssueDate>'.$cabecera["FECHA_DOCUMENTO"].'</cbc:IssueDate>
            <cac:Signature>
                <cbc:ID>' . $cabecera["CODIGO"] . '-' . $cabecera["SERIE"] . '-' . $cabecera["SECUENCIA"] . '</cbc:ID>
                <cac:SignatoryParty>
                    <cac:PartyIdentification>
                        <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
                    </cac:PartyIdentification>
                    <cac:PartyName>
                        <cbc:Name>' . $cabecera["RAZON_SOCIAL_EMPRESA"] . '</cbc:Name>
                    </cac:PartyName>
                </cac:SignatoryParty>
                <cac:DigitalSignatureAttachment>
                    <cac:ExternalReference>
                        <cbc:URI>' . $cabecera["CODIGO"] . '-' . $cabecera["SERIE"] . '-' . $cabecera["SECUENCIA"] . '</cbc:URI>
                    </cac:ExternalReference>
                </cac:DigitalSignatureAttachment>
            </cac:Signature>
            <cac:AccountingSupplierParty>
                <cbc:CustomerAssignedAccountID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:CustomerAssignedAccountID>
                <cbc:AdditionalAccountID>' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '</cbc:AdditionalAccountID>
                <cac:Party>
                    <cac:PartyLegalEntity>
                        <cbc:RegistrationName>' . $cabecera["RAZON_SOCIAL_EMPRESA"] . '</cbc:RegistrationName>
                    </cac:PartyLegalEntity>
                </cac:Party>
            </cac:AccountingSupplierParty>';
            for ($i = 0; $i < count($detalle); $i++) {
                $xmlCPE = $xmlCPE . '<sac:SummaryDocumentsLine>
                <cbc:LineID>' . $detalle[$i]["ITEM"] . '</cbc:LineID>
                <cbc:DocumentTypeCode>' . $detalle[$i]["TIPO_COMPROBANTE"] . '</cbc:DocumentTypeCode>
                <cbc:ID>' . $detalle[$i]["NRO_COMPROBANTE"] . '</cbc:ID>';

                if (!isset($detalle[$i]["NRO_DOCUMENTO"]) || $detalle[$i]["NRO_DOCUMENTO"] == "" || $detalle[$i]["NRO_DOCUMENTO"] == "0" || $detalle[$i]["NRO_DOCUMENTO"] == "00000000"){
                    $NRO_DOCUMENTO = "-";
                    $TIPO_DOCUMENTO = "-";
                } else {
                    $NRO_DOCUMENTO = $detalle[$i]["NRO_DOCUMENTO"];
                    $TIPO_DOCUMENTO = $detalle[$i]["TIPO_DOCUMENTO"];
                }

                $xmlCPE = $xmlCPE .'<cac:AccountingCustomerParty>
                                    <cbc:CustomerAssignedAccountID>' . $NRO_DOCUMENTO . '</cbc:CustomerAssignedAccountID>
                                    <cbc:AdditionalAccountID>' . $TIPO_DOCUMENTO . '</cbc:AdditionalAccountID>
                                </cac:AccountingCustomerParty>';


                        if ($detalle[$i]["TIPO_COMPROBANTE"]=="07"||$detalle[$i]["TIPO_COMPROBANTE"]=="08"){
                $xmlCPE = $xmlCPE . '<cac:BillingReference>
                    <cac:InvoiceDocumentReference>
                        <cbc:ID>' . $detalle[$i]["NRO_COMPROBANTE_REF"] . '</cbc:ID>
                        <cbc:DocumentTypeCode>' . $detalle[$i]["TIPO_COMPROBANTE_REF"] . '</cbc:DocumentTypeCode>
                    </cac:InvoiceDocumentReference>
                </cac:BillingReference>';
                        }
                $xmlCPE = $xmlCPE . '<cac:Status>
                    <cbc:ConditionCode>' . $detalle[$i]["STATUS"] . '</cbc:ConditionCode>
                </cac:Status>                
                <sac:TotalAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["TOTAL"] . '</sac:TotalAmount>
                
                        <sac:BillingPayment>
                    <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["GRAVADA"] . '</cbc:PaidAmount>
                    <cbc:InstructionID>01</cbc:InstructionID>
                </sac:BillingPayment>';
                        
                        if (intval($detalle[$i]["EXONERADO"]) > 0) {
                        $xmlCPE = $xmlCPE . '<sac:BillingPayment>
                    <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["EXONERADO"] . '</cbc:PaidAmount>
                    <cbc:InstructionID>02</cbc:InstructionID>
                </sac:BillingPayment>';
                        }
                        
                        if (intval($detalle[$i]["INAFECTO"]) > 0) {
                        $xmlCPE = $xmlCPE . '<sac:BillingPayment>
                    <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["INAFECTO"] . '</cbc:PaidAmount>
                    <cbc:InstructionID>03</cbc:InstructionID>
                </sac:BillingPayment>';
                        }
                        
                        if (intval($detalle[$i]["EXPORTACION"]) > 0) {
                        $xmlCPE = $xmlCPE . '<sac:BillingPayment>
                    <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["EXPORTACION"] . '</cbc:PaidAmount>
                    <cbc:InstructionID>04</cbc:InstructionID>
                </sac:BillingPayment>';
                        }
                        
                        if (intval($detalle[$i]["GRATUITAS"]) > 0) {
                        $xmlCPE = $xmlCPE . '<sac:BillingPayment>
                    <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["GRATUITAS"] . '</cbc:PaidAmount>
                    <cbc:InstructionID>05</cbc:InstructionID>
                </sac:BillingPayment>';
                        }
                        /*
                        if (intval($detalle[$i]["MONTO_CARGO_X_ASIG"]) > 0) {
                            $xmlCPE = $xmlCPE . '<cac:AllowanceCharge>';
                            if ($detalle[$i]["CARGO_X_ASIGNACION"] == 1) {
                                $xmlCPE = $xmlCPE . '<cbc:ChargeIndicator>true</cbc:ChargeIndicator>';
                            }else{
                                $xmlCPE = $xmlCPE . '<cbc:ChargeIndicator>false</cbc:ChargeIndicator>';
                            }
                            $xmlCPE = $xmlCPE . '<cbc:Amount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["MONTO_CARGO_X_ASIG"] . '</cbc:Amount>
                            </cac:AllowanceCharge>';
                        }
                        */
                        if(intval($detalle[$i]["ISC"]) > 0){
                $xmlCPE = $xmlCPE . '<cac:TaxTotal>
                    <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["ISC"] . '</cbc:TaxAmount>
                    <cac:TaxSubtotal>
                        <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["ISC"] . '</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID>2000</cbc:ID>
                                <cbc:Name>ISC</cbc:Name>
                                <cbc:TaxTypeCode>EXC</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>
                </cac:TaxTotal>';
                        }
                        $xmlCPE = $xmlCPE . '<cac:TaxTotal>
                    <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["IGV"] . '</cbc:TaxAmount>
                    <cac:TaxSubtotal>
                        <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["IGV"] . '</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID>1000</cbc:ID>
                                <cbc:Name>IGV</cbc:Name>
                                <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>
                </cac:TaxTotal>';
                        
                        if(intval($detalle[$i]["OTROS"]) > 0){
                        $xmlCPE = $xmlCPE . '<cac:TaxTotal>
                    <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["OTROS"] . '</cbc:TaxAmount>
                    <cac:TaxSubtotal>
                        <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["OTROS"] . '</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID>9999</cbc:ID>
                                <cbc:Name>OTROS</cbc:Name>
                                <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>
                </cac:TaxTotal>';
                        }
            $xmlCPE = $xmlCPE . '</sac:SummaryDocumentsLine>';
            }
            $xmlCPE = $xmlCPE . '</SummaryDocuments>';

            $doc->loadXML($xmlCPE);
            $doc->save($ruta ."/". $nombre_archivo);
            $resp["xml_filename"] =  basename($nombre_archivo, '.XML').'.XML';
            $resp['respuesta'] = 'ok';
            $resp["creado"]  = "1";
            $resp["ruta"] = $ruta;
            return $resp;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}