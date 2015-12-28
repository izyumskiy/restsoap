<?php
namespace RestSoap\Api\Rest;

use RestSoap\Api;
use RestSoap\Api\Soap\Template;
use RestSoap\Api\Soap\Xslt;

class InputAnalyzer extends UrlAnalyzer {

    private $_data;
    private $_restObjectName;

    /**
     * @param string $data
     */
    private function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @return string
     */
    private function getData()
    {
        return $this->_data;
    }

    /**
     * @param string $restObject
     */
    private function setRestObjectName($restObject)
    {
        $this->_restObjectName = $restObject;
    }

    /**
     * @return string
     */
    private function getRestObjectName()
    {
        return $this->_restObjectName;
    }

    private $_moduleName;
    /**
     * Get WSDL-module name from URL.
     *
     * @return string
     */
    protected function getModuleName() {
        if( !isset($this->_moduleName) || empty($this->_moduleName) )
            throw new \InvalidArgumentException("InputAnalyzer; Parameter moduleName does not exist", self::ERROR_400);
        return $this->_moduleName;
    }

    private function setModuleName( $wsdlModuleName ) {
        $this->_moduleName = $wsdlModuleName;
    }

    /**
     * @param string $data
     * @param string $restObject
     * @param string array $wsdlModuleName
     * @param array $wsdlParams
     */
    public function __construct($data, $restObject, $wsdlModuleName, $wsdlParams = [] ) {
        $this->setData($data);
        $this->setRestObjectName($restObject);
        $this->setModuleName($wsdlModuleName);
        $this->setWsdlParams($wsdlParams);
    }

    /**
     * @param string $xmlData
     * @return bool
     * @throws \Exception
     */
    private function isValidXmlRequest($xmlData) {
        $tpl = $this->getCmsViewer();
        $xsl = $tpl->formOutput( dirname(__FILE__) . '/../views/api/xsl/get_xsd_schema.xsl', []);
        $wsdlViewPath = $this->getWsdlParams('view_path');
        $wsdl = $tpl->formOutput( $wsdlViewPath . $this->getModuleName() . '.wsdl', []);
        $xslt = new Api\Xslt\Transformer();
        $xmlObj = $xslt->transform($wsdl, $xsl);

        $xsd = $xmlObj->asXML();
        $result = $xslt->validateXmlByXsd($xmlData, $xsd);
        if( $result['validation'] === false )
            throw new \Exception($result['errors'][0], self::ERROR_500);
        return true;
    }

    /**
     * преобразовать XML в массив
     *
     * @param \SimpleXMLElement $xmlObject
     * @param array $out
     * @return array
     */
    public function xmlToArray( \SimpleXMLElement $xmlObject, $out = [] )
    {
        foreach ( (array)$xmlObject as $index => $node ) {
            if( $index == 'item' )
                $out[] = ( is_object($node) ) ? $this->xmlToArray($node) : $node;
            else
                $out[$index] = ( is_object($node) ) ? $this->xmlToArray($node) : $node;
        }

        return $out;
    }

    public function getJsonHttpBody($xmlRequestRootTitle) {
        try {
            $data = $this->getData();
            $encodedData['request'] = json_decode($data, true);
            if( is_null($encodedData['request']) || empty($encodedData['request']) )
                throw new \InvalidArgumentException("Input data has wrong format", self::ERROR_400);

            // JSON is converted to XML for validation by XSD-scheme that's contained in WSDL
            $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><" . $xmlRequestRootTitle . "></" . $xmlRequestRootTitle . ">");
            $this->arrayToXml($encodedData['request'], $xml);
            $this->isValidXmlRequest($xml->saveXML());

            return $encodedData;
        } catch(\Exception $ex) {
            throw new \Exception("JsonHttpBody Error: " . $ex->getMessage(), $ex->getCode());
        }
    }

    public function getXmlHttpBody() {
        try {
            $result = [];
            $data = $this->getData();
            $this->isValidXmlRequest($data);

            $xmlData = simplexml_load_string( $data );
            $resultFinal['request'] = $this->xmlToArray($xmlData, $result);

            return $resultFinal;
        } catch(\Exception $ex) {
            throw new \Exception("XmlHttpBody Error: " . $ex->getMessage(), $ex->getCode());
        }
    }
}