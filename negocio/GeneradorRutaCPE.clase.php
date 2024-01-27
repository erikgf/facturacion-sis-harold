<?php

class GeneradorRutaCPE{
    public $emisor_ruc;
    public $id_tipo_comprobante;
    public $tipo_proceso;
    public $fecha_comprobante;

    private $nombre_carpeta_comprobante = "comprobante";
    private $nombre_carpeta_comprobante_firmado = "comprobante_firmado";
    private $nombre_carpeta_comprobante_cdr = "cdr";

    private $nombre_carpeta_certificados = "certificados";

    private $nombre_carpeta_factura = "FA";
    private $nombre_carpeta_boleta = "BV";
    private $nombre_carpeta_notacredito = "NC";
    private $nombre_carpeta_notadebido = "ND";
    private $nombre_carpeta_resumencomprobantes = "RC";

    public function getRuta($nombre_carpeta_comprobante){
        try {
            
            //$ruta = "../".F_CARPETA_PRINCIPAL_FACTURACION."/".F_FOLDER_XML."/".$this->emisor_ruc;
            $ruta = "../".F_FOLDER_XML."/".$this->emisor_ruc;
            $ruta_proceso = $ruta."/".($this->tipo_proceso == "1" ? "produccion" : "beta");
            $ruta_proceso_tipo = $ruta_proceso."/".$nombre_carpeta_comprobante;
            $ruta_tipo_proceso_fecha = $ruta_proceso_tipo."/".str_replace('-','',$this->fecha_comprobante);
            
            if(!is_dir($ruta_tipo_proceso_fecha)) {
                mkdir($ruta_tipo_proceso_fecha , 0777);     
            }

            $parcial_ruta_comprobante = "";
            switch($this->id_tipo_comprobante){
                case "01":
                    $parcial_ruta_comprobante = $this->nombre_carpeta_factura;
                break;
                case "03":
                    $parcial_ruta_comprobante = $this->nombre_carpeta_boleta;
                break;
                case "07":
                    $parcial_ruta_comprobante = $this->nombre_carpeta_notacredito;
                break;
                case "08":
                    $parcial_ruta_comprobante = $this->nombre_carpeta_notadebido;
                break;
                case "RC":
                    $parcial_ruta_comprobante = $this->nombre_carpeta_resumencomprobantes;
                break;
            }

            if ($parcial_ruta_comprobante == ""){
                throw new Exception("Comprobante no valido", 1);
            }

            $ruta_tipo_proceso_fecha_comprobante = $ruta_tipo_proceso_fecha ."/". $parcial_ruta_comprobante;
            if(!is_dir($ruta_tipo_proceso_fecha_comprobante)) {
                mkdir($ruta_tipo_proceso_fecha_comprobante , 0777);     
            }

            return $ruta_tipo_proceso_fecha_comprobante;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 1);
        }
    }

    public function getRutaComprobante(){
        return $this->getRuta($this->nombre_carpeta_comprobante);
    }

    public function getRutaComprobanteFirmado(){
        return $this->getRuta($this->nombre_carpeta_comprobante_firmado);
    }

    public function getRutaCDR(){
        return $this->getRuta($this->nombre_carpeta_comprobante_cdr);
    }

    public function getRutaFirma(){
        try {
            
            return "../".$this->nombre_carpeta_certificados."/".($this->tipo_proceso == "1" ? "produccion" : "beta");
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 1);
        }
    }
    
    public function getRutaExterna($ruta){
        $urlBaseName = sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            "/andreitababy-facturacion"
        );

        return $urlBaseName.substr($ruta, 2); 
    }

}

