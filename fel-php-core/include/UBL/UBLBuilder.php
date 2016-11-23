<?php
require_once dirname(__FILE__).'/../../vendor/autoload.php';
use \archon810\SmartDOMDocument;

abstract class UBLBuilder {
    public $dom = NULL;
    var $root;
    var $current;
    var $stack = array();
    var $recent;
    var $file_name;
    var $namespaces;

    
    function attribute($name, $value) {
        $this->recent->setAttribute($name, $value);
        return $this;
    }

    function append_fix_date($element, $value=NULL, $cdata=false) {
        $value1 = $value;
        $matches = null;
        if (preg_match('@^(..)/(..)/(....)$@', $value, $matches)) {
            $value1 = "$matches[3]-$matches[2]-$matches[1]";
        }
        $this->append($element, $value1, $cdata);
        return $this;
    }

    function append_fix_document_number($element, $value=NULL, $cdata=false) {
        $value1 = $value;
        $matches = null;
        if (preg_match('@^(.+)-(.+)$@', $value, $matches)) {
            $serie=$matches[1];
            $numero=$matches[2];
            while (strlen($serie)<4) {
                $serie= '0'.$serie;
            }
            $value1="$serie-$numero";
        }
        $this->append($element, $value1, $cdata);
        return $this;
    }

    function append($element, $value=NULL, $cdata=false) {
        $node = NULL;
        if ($cdata) {
            $node = $this->dom->createElement($element);
            $data = $this->dom->createCDATASection($value);
            //$data = $this->dom->createCDATASection(utf8_encode($value));
            $node->appendChild($data);
        } else {
            $node = $this->dom->createElement($element, $value);
            //$node = $this->dom->createElement($element, utf8_encode($value));
        }
        $this->current->appendChild($node);
        $this->recent=$node;
        return $this;
    }

    function append_fw($element, $value=NULL, $cdata=false) {
        $this->append($element, $value, $cdata);
        $this->push();
        return $this;
    }

    function append_nnv($element, $value=NULL, $cdata=false){
        if (!!$value) {
            $this->append($element, $value, $cdata);
        }
        return $this;
    }

    function push() {
        $this->stack[]=$this->current;
        $this->current=$this->recent;
    }

    function pop($times=1) {
        for ($i = 0; $i < $times; $i++) {
            $this->current = array_pop($this->stack);
        }
        return $this;
    }

    function reset() {
        $this->stack = array();
        $this->current = $this->root;
        return $this;
    }

    function save ($file = null) {
        if (!$file) {
            return $this->dom->saveXML();
        } else {
            return $this->dom->save($file);
        }
    }

}

class InvoiceBuilder extends UBLBuilder {



    function __construct($data, $dsSignature = false) {

        $montos = $data['montos'];
        $notas = $data['notas'];
        $impuestos = $data['impuestos'];

        $this->namespaces = [
            'cac'  => "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2",
            'cbc'  => "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2",
            'ccts' => "urn:un:unece:uncefact:documentation:2",
            'ds'   => "http://www.w3.org/2000/09/xmldsig#",
            'ext'  => "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2",
            'qdt'  => "urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2",
            'sac'  => "urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1",
            'udt'  => "urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2",
            'xsi'  => "http://www.w3.org/2001/XMLSchema-instance"
        ];
        $this->dom = new SmartDOMDocument('1.0', 'iso-8859-1');
        $this->root = $this->dom->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        foreach ($this->namespaces as $pfx => $namespace) {
            $this->root->setAttributeNS('http://www.w3.org/2000/xmlns/','xmlns:'.$pfx,$namespace);
        }
        $this->dom->appendChild($this->root);
        $this->current = $this->root;
        $this->append_fw('ext:UBLExtensions');

        $this
            ->append_fw('ext:UBLExtension')
                ->append_fw('ext:ExtensionContent')
                    ->append_fw('sac:AdditionalInformation');
        foreach ($montos as $key => $monto) {
            $this->append_fw('sac:AdditionalMonetaryTotal')
                ->append('cbc:ID', $monto['id'])
                ->append_nnv('cbc:Name', $monto['nombre'])
                ->append_nnv('sac:ReferenceAmount', $monto['valor']['referencia'])
                ->append('cbc:PayableAmount', $monto['valor']['pagable'])
                ->append_nnv('cbc:Percent', $monto['porcentaje'])
                ->append_nnv('sac:TotalAmount', $monto['valor']['total'])
                ->pop();
        }
        foreach ($notas as $key => $nota) {
            $this->append_fw('sac:AdditionalProperty')
                ->append('cbc:ID', $nota['id'])
                ->append_nnv('cbc:Name', $nota['nombre'])
                ->append('cbc:Value', $nota['valor'])
                ->pop();
        }
        //TODO AGREGAR DATOS DE FACTURA GUIA
        if (isset($data['documento']['tipo_transaccion'])) {
            $this
                ->append_fw('sac:SUNATTransaction')
                    ->append('cbc:ID',$data['documento']['tipo_transaccion'])
                    ->pop();
        }
        $this->pop()->pop()->pop();
        //espacio para el adjunto de la firma
        if ($dsSignature){
            $this
                ->append_fw('ext:UBLExtension')
                    ->append_fw('ext:ExtensionContent')
            ;
        }

        $this
            ->reset()
            //cabecera UBL
            ->append('cbc:UBLVersionID','2.0')
            ->append('cbc:CustomizationID','1.0')
            //datos del documento
            ->append('cbc:ID', $data['documento']['numero'])
            ->append('cbc:IssueDate',$data['documento']['fecha_emision'])
            ->append('cbc:InvoiceTypeCode',$data['documento']['tipo_factura'])
            ->append('cbc:DocumentCurrencyCode',$data['documento']['moneda'])
            //datos del firmante
            ->append_fw('cac:Signature')
                ->append('cbc:ID','IDSignKG')
                ->append_fw('cac:SignatoryParty')
                    ->append_fw('cac:PartyIdentification')->append('cbc:ID', $data['emisor']['documento']['numero'])->pop()
                    ->append_fw('cac:PartyName')->append('cbc:Name', $data['emisor']['datos']['razon_social'], true)->pop()
                    ->pop()
                ->append_fw('cac:DigitalSignatureAttachment')->append_fw('cac:ExternalReference')->append('cbc:URI','#signatureKG')->pop(2)
                ->reset()
            //datos del emisor
            ->append_fw('cac:AccountingSupplierParty')
                ->append('cbc:CustomerAssignedAccountID', $data['emisor']['documento']['numero'])
                ->append('cbc:AdditionalAccountID', $data['emisor']['documento']['tipo'] )
                ->append_fw('cac:Party')
                    ->append_fw('cac:PostalAddress')
                        ->append_nnv('cbc:ID',$data['emisor']['ubicacion']['ubigeo'])
                        ->append_nnv('cbc:StreetName',$data['emisor']['ubicacion']['direccion'])
                        ->append_nnv('cbc:CitySubdivisionName',$data['emisor']['ubicacion']['urbanizacion'])
                        ->append_nnv('cbc:CityName',$data['emisor']['ubicacion']['provincia'])
                        ->append_nnv('cbc:CountrySubentity',$data['emisor']['ubicacion']['departamento'])
                        ->append_nnv('cbc:District',$data['emisor']['ubicacion']['distrito'])
                        ->append_fw('cac:Country')->append_nnv('cbc:IdentificationCode',$data['emisor']['ubicacion']['pais'])->pop()
                        ->pop()
                    ->append_fw('cac:PartyLegalEntity')
                        ->append('cbc:RegistrationName',$data['emisor']['datos']['razon_social'],true)
                ->reset()
            ->append_fw('cac:AccountingCustomerParty')
                ->append('cbc:CustomerAssignedAccountID', $data['cliente']['documento']['numero'])
                ->append('cbc:AdditionalAccountID', $data['cliente']['documento']['tipo'] )
                ->append_fw('cac:Party')
                    ->append_fw('cac:PostalAddress')
                        ->append_nnv('cbc:ID',$data['cliente']['ubicacion']['ubigeo'])
                        ->append_nnv('cbc:StreetName',$data['cliente']['ubicacion']['direccion'])
                        ->append_nnv('cbc:CitySubdivisionName',$data['cliente']['ubicacion']['urbanizacion'])
                        ->append_nnv('cbc:CityName',$data['cliente']['ubicacion']['provincia'])
                        ->append_nnv('cbc:CountrySubentity',$data['cliente']['ubicacion']['departamento'])
                        ->append_nnv('cbc:District',$data['cliente']['ubicacion']['distrito'])
                        ->append_fw('cac:Country')->append_nnv('cbc:IdentificationCode',$data['cliente']['ubicacion']['pais'])->pop()
                        ->pop()
                    ->append_fw('cac:PartyLegalEntity')
                        ->append('cbc:RegistrationName',$data['cliente']['datos']['razon_social'],true)
                ->reset()
            ;
        foreach($impuestos as $idx => $impuesto) {
            $this
                ->append_fw('cac:TaxTotal')
                    ->append('cbc:TaxAmount', $impuesto['monto'])->attribute('currencyID', $data['documento']['moneda'])
                    ->append_fw('cac:TaxSubtotal')
                        ->append('cbc:TaxAmount', $impuesto['monto'])->attribute('currencyID', $data['documento']['moneda'])
                        ->append_fw('cac:TaxCategory')
                            ->append_fw('cac:TaxScheme')
                                ->append('cbc:ID',$impuesto['id'])
                                ->append('cbc:Name',$impuesto['nombre'])
                                ->append('cbc:TaxTypeCode',$impuesto['codigo'])
                    ->reset();
                ;

        }
        $this->append_fw('cac:LegalMonetaryTotal');

        if (isset($data['total']['lineas']))    $this->append('cbc:LineExtensionAmount',  $data['total']['lineas'])    ->attribute('currencyID',$data['documento']['moneda']);
        if (isset($data['total']['descuento'])) $this->append('cbc:AllowanceTotalAmount', $data['total']['descuento']) ->attribute('currencyID',$data['documento']['moneda']);
        if (isset($data['total']['cargo']))     $this->append('cbc:ChargeTotalAmount',    $data['total']['cargo'])     ->attribute('currencyID',$data['documento']['moneda']);
        if (isset($data['total']['prepagado'])) $this->append('cbc:PrepaidAmount',        $data['total']['prepagado']) ->attribute('currencyID',$data['documento']['moneda']);
        if (isset($data['total']['pagable']))   $this->append('cbc:PayableAmount',        $data['total']['pagable'])   ->attribute('currencyID',$data['documento']['moneda']);
        
    } 

}

class VoidedDocumentsBuilder extends UBLBuilder {
    
    function __construct($data, $dsSignature = false) {
        $this->namespaces = [
            'cac'=>'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
            'cbc'=>'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
            'ds'=>'http://www.w3.org/2000/09/xmldsig#',
            'ext'=>'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2',
            'sac'=>'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1',
            'xsi'=>'http://www.w3.org/2001/XMLSchema-instance'
        ];
        $this->file_name = $data['emisor']['documento']['numero'].'-RA-'.$data['documento']['numero'];

        $this->dom = new SmartDOMDocument('1.0', 'iso-8859-1');
        $this->root = $this->dom->createElementNS('urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1', 'VoidedDocuments');
        foreach ($this->namespaces as $pfx => $namespace) {
            $this->root->setAttributeNS('http://www.w3.org/2000/xmlns/','xmlns:'.$pfx,$namespace);
        }
        $this->dom->appendChild($this->root);
        $this->current = $this->root;
        $this->append_fw('ext:UBLExtensions');
        //espacio para el adjunto de la firma
        if ($dsSignature){
            $this
                ->append_fw('ext:UBLExtension')
                    ->append_fw('ext:ExtensionContent')
            ;
        }
        $this
           ->reset()
            //cabecera UBL
            ->append('cbc:UBLVersionID','2.0')->append('cbc:CustomizationID','1.0')->reset()
            //datos del documento
            ->append('cbc:ID','RA-'.$data['documento']['numero'])
            ->append_fix_date('cbc:ReferenceDate',$data['documento']['fecha_referencia'])
            ->append_fix_date('cbc:IssueDate',$data['documento']['fecha_emision'])
            //datos del firmante
            ->append_fw('cac:Signature')
                ->append('cbc:ID','IDSignKG')
                ->append_fw('cac:SignatoryParty')
                    ->append_fw('cac:PartyIdentification')->append('cbc:ID', $data['emisor']['documento']['numero'])->pop()
                    ->append_fw('cac:PartyName')->append('cbc:Name', $data['emisor']['datos']['razon_social'], true)->pop()
                    ->pop()
                ->append_fw('cac:DigitalSignatureAttachment')->append_fw('cac:ExternalReference')->append('cbc:URI','#signatureKG')->pop(2)
                ->reset()
            //datos del emisor
            ->append_fw('cac:AccountingSupplierParty')
                ->append('cbc:CustomerAssignedAccountID', $data['emisor']['documento']['numero'])
                ->append('cbc:AdditionalAccountID', $data['emisor']['documento']['tipo'] )
                ->append_fw('cac:Party')
                    ->append_fw('cac:PartyLegalEntity')
                        ->append('cbc:RegistrationName',$data['emisor']['datos']['razon_social'],true)
                ->reset();
        
        foreach ($data['items'] as $idx => $line) {
            $this
                ->append_fw('sac:VoidedDocumentsLine')
                    ->append('cbc:LineID', $line['id'])
                    ->append('cbc:DocumentTypeCode', $line['documento']['tipo'])
                    ->append('sac:DocumentSerialID', $line['documento']['serie'])
                    ->append('sac:DocumentNumberID', $line['documento']['numero'])
                    ->append('sac:VoidReasonDescription', $line['motivo'])
                ->reset();
        }
    }

}

class RetentionBuilder extends UBLBuilder {
    
    function __construct($data, $dsSignature = false) {
        $this->namespaces = [
            'cac'=>'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
            'cbc'=>'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
            'ccts'=>'urn:un:unece:uncefact:documentation:2',
            'ds'=>'http://www.w3.org/2000/09/xmldsig#',
            'ext'=>'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2',
            'qdt'=>'urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2',
            'sac'=>'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1',
            'udt'=>'urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2',
            'xsi'=>'http://www.w3.org/2001/XMLSchema-instance'
        ];

        $this->file_name = $data['emisor']['documento']['numero'].'-20-'.$data['documento']['numero'];


        $this->dom = new SmartDOMDocument('1.0', 'iso-8859-1');
        $this->root = $this->dom->createElementNS('urn:sunat:names:specification:ubl:peru:schema:xsd:Retention-1', 'Retention');
        foreach ($this->namespaces as $pfx => $namespace) {
            $this->root->setAttributeNS('http://www.w3.org/2000/xmlns/','xmlns:'.$pfx,$namespace);
        }
        $this->dom->appendChild($this->root);
        $this->current = $this->root;
        $this->append_fw('ext:UBLExtensions');
        //espacio para el adjunto de la firma
        if ($dsSignature){
            $this
                ->append_fw('ext:UBLExtension')
                    ->append_fw('ext:ExtensionContent')
            ;
        }
        $this
            ->reset()
            //cabecera UBL
            ->append('cbc:UBLVersionID','2.0')->append('cbc:CustomizationID','1.0')->reset()
            //datos del firmante
            ->append_fw('cac:Signature')
                ->append('cbc:ID','IDSignKG')
                ->append_fw('cac:SignatoryParty')
                    ->append_fw('cac:PartyIdentification')->append('cbc:ID', $data['emisor']['documento']['numero'])->pop()
                    ->append_fw('cac:PartyName')->append('cbc:Name', $data['emisor']['datos']['razon_social'], true)->pop()
                    ->pop()
                ->append_fw('cac:DigitalSignatureAttachment')->append_fw('cac:ExternalReference')->append('cbc:URI','#signatureKG')->pop(2)
                ->reset()
            //datos del documento
            ->append('cbc:ID',$data['documento']['numero'])
            ->append_fix_date('cbc:IssueDate',$data['documento']['fecha_emision'])
            //datos del agente retenedor
            ->append_fw('cac:AgentParty')
                ->append_fw('cac:PartyIdentification')->append('cbc:ID',$data['emisor']['documento']['numero'])->attribute('schemeID',$data['emisor']['documento']['tipo'])->pop()
                ->append_fw('cac:PartyName')->append('cbc:Name',$data['emisor']['datos']['nombre_comercial'], true)->pop()
                ->append_fw('cac:PostalAddress')
                    ->append_nnv('cbc:ID',$data['emisor']['ubicacion']['ubigeo'])
                    ->append_nnv('cbc:StreetName',$data['emisor']['ubicacion']['direccion'])
                    ->append_nnv('cbc:CitySubdivisionName',$data['emisor']['ubicacion']['urbanizacion'])
                    ->append_nnv('cbc:CityName',$data['emisor']['ubicacion']['provincia'])
                    ->append_nnv('cbc:CountrySubentity',$data['emisor']['ubicacion']['departamento'])
                    ->append_nnv('cbc:District',$data['emisor']['ubicacion']['distrito'])
                    ->append_fw('cac:Country')->append_nnv('cbc:IdentificationCode',$data['emisor']['ubicacion']['pais'])->pop()
                    ->pop()
                ->append_fw('cac:PartyLegalEntity')->append_fw('cbc:RegistrationName',$data['emisor']['datos']['razon_social'],true)->pop()
                ->reset()
            //datos del proveedor
            ->append_fw('cac:ReceiverParty')
                ->append_fw('cac:PartyIdentification')->append('cbc:ID',$data['proveedor']['documento']['numero'])->attribute('schemeID',$data['proveedor']['documento']['tipo'])->pop()
                ->append_fw('cac:PartyName')->append('cbc:Name',$data['proveedor']['datos']['nombre_comercial'], true)->pop()
                ->append_fw('cac:PostalAddress')
                    ->append_nnv('cbc:ID',$data['proveedor']['ubicacion']['ubigeo'])
                    ->append_nnv('cbc:StreetName',$data['proveedor']['ubicacion']['direccion'])
                    ->append_nnv('cbc:CitySubdivisionName',$data['proveedor']['ubicacion']['urbanizacion'])
                    ->append_nnv('cbc:CityName',$data['proveedor']['ubicacion']['provincia'])
                    ->append_nnv('cbc:CountrySubentity',$data['proveedor']['ubicacion']['departamento'])
                    ->append_nnv('cbc:District',$data['proveedor']['ubicacion']['distrito'])
                    ->append_fw('cac:Country')->append_nnv('cbc:IdentificationCode',$data['proveedor']['ubicacion']['pais'])->pop()
                    ->pop()
                ->append_fw('cac:PartyLegalEntity')->append_fw('cbc:RegistrationName',$data['proveedor']['datos']['razon_social'],true)
                ->reset()
            //datos finales de la cabecera
             ->append('sac:SUNATRetentionSystemCode', $data['retencion']['regimen'])
             ->append('sac:SUNATRetentionPercent', $data['retencion']['tasa'])
             ->append_nnv('cbc:Note', $data['retencion']['observaciones'])
             ->append('cbc:TotalInvoiceAmount',$data['retencion']['total']['retencion']['monto'])->attribute('currencyID',$data['retencion']['total']['retencion']['moneda'])
             ->append('sac:SUNATTotalPaid',$data['retencion']['total']['pago']['monto'])->attribute('currencyID',$data['retencion']['total']['pago']['moneda'])
             ->reset()
        ;
        foreach ($data['items'] as $idx => $line) {
            $this
                ->append_fw('sac:SUNATRetentionDocumentReference')
                    ->append_fix_document_number('cbc:ID',$line['referencia']['documento']['serie_numero'])->attribute('schemeID',$line['referencia']['documento']['tipo'])
                    ->append_fix_date('cbc:IssueDate',$line['referencia']['documento']['fecha_emision'])
                    ->append('cbc:TotalInvoiceAmount',$line['referencia']['total']['monto'])->attribute('currencyID',$line['referencia']['total']['moneda'])
                    ->append_fw('cac:Payment')
                        ->append('cbc:ID',$line['pago']['numero'])
                        ->append('cbc:PaidAmount',$line['pago']['monto'])->attribute('currencyID',$line['pago']['moneda'])
                        ->append_fix_date('cbc:PaidDate',$line['pago']['fecha'])
                        ->pop()
                    ->append_fw('sac:SUNATRetentionInformation')
                        ->append('sac:SUNATRetentionAmount',$line['retencion']['valor_retenido']['monto'])->attribute('currencyID',$line['retencion']['valor_retenido']['moneda'])
                        ->append_fix_date('sac:SUNATRetentionDate',$line['retencion']['fecha'])
                        ->append('sac:SUNATNetTotalPaid',$line['retencion']['neto_pagado']['monto'])->attribute('currencyID',$line['retencion']['neto_pagado']['moneda'])
                        ->append_fw('cac:ExchangeRate')
                            ->append('cbc:SourceCurrencyCode',$line['tipo_cambio']['moneda']['origen'])
                            ->append('cbc:TargetCurrencyCode',$line['tipo_cambio']['moneda']['destino'])
                            ->append('cbc:CalculationRate',$line['tipo_cambio']['tasa'])
                            ->append_fix_date('cbc:Date',$line['tipo_cambio']['fecha'])
                            ->pop()
                        ->pop()
                ->reset()
            ;
        }
    }

}