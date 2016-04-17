<?php
/**
 * XSLT transforms
 */
namespace RestSoap\Xslt;

class Transformer {

    /**
     * На вход подается содержимое трансформируемого XML и XSL шаблона трансформации
     *
     * @param string $xml
     * @param string $xslt
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function transformToDocument( $xml, $xslt ) {
        try {

            $xmlObj = new \DOMDocument( '1.0', 'UTF-8' );
            $xmlObj->loadXML($xml);

            $xslObj = new \DOMDocument( '1.0', 'UTF-8' );
            $xslObj->loadXML($xslt);

            $proc = new \XSLTProcessor();
            $proc->importStyleSheet( $xslObj );

            $xmlObject = $proc->transformToDoc($xmlObj);

        } catch( \Exception $ex ) {
            throw new \Exception('Xslt_Transformer; XSLT Error: ' . $ex->getMessage());
        }
        return $xmlObject;
    }

    /**
     * На вход подается содержимое трансформируемого XML и XSL шаблона трансформации
     *
     * @param string $xml
     * @param string $xslt
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function transform( $xml, $xslt ) {
        try {
            $xmlObj = new \DOMDocument( '1.0', 'UTF-8' );
            $xmlObj->loadXML($xml);

            $xslObj = new \DOMDocument( '1.0', 'UTF-8' );
            $xslObj->loadXML($xslt);

            $proc = new \XSLTProcessor();
            $proc->importStyleSheet( $xslObj );

            $result = $proc->transformToXML($xmlObj);
            $xmlObject = simplexml_load_string( $result );

        } catch( \Exception $ex ) {
            throw new \Exception('Xslt_Transformer; XSLT Error: ' . $ex->getMessage(), self::ERROR_500);
        }
        return $xmlObject;
    }

    /**
     * На вход подается содержимое XML и XSD-схемы.
     * Далее входной XML валидируется по заданной XSD-схеме
     *
     * @param $xml
     * @param $xsd
     * @return array
     */
    public function validateXmlByXsd( $xml, $xsd ) {
        $isValid = false;
        $err = [];
        
        libxml_use_internal_errors(true);
        
        $validator = new \DOMDocument( '1.0', 'UTF-8' );
        $validator->loadXML( $xml );

        if (!$validator->schemaValidateSource($xsd)) {
            $errors = libxml_get_errors();
            $i = 1;
            foreach ($errors as $error) {
                $err[] = $error->message;
                $i++;
            }
            libxml_clear_errors();
        } else {
            $isValid = true;
        }
        libxml_use_internal_errors(false);
        return ['validation' => $isValid, 'errors' => $err];
    }
}