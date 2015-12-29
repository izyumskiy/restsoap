<?php
namespace RestSoap\Soap;

use RestSoap;
use RestSoap\Template;
use RestSoap\Xslt;

class ControllerFront extends RestSoap\ApiBase {

    private $_objectName;
    private $_showWSDL;
    private $_wsdlParams;

    /**
     * @param array $wsdlParams
     */
    public function setWsdlParams($wsdlParams)
    {
        $this->_wsdlParams = $wsdlParams;
    }

    /**
     * @return array
     */
    public function getWsdlParams($paramName = '')
    {
        if( empty($paramName) )
            return $this->_wsdlParams;
        else {
            if( !isset($this->_wsdlParams[$paramName]) )
                throw new \InvalidArgumentException("ControllerFront; " . $paramName . " parameter does not exist", self::ERROR_400);
            return $this->_wsdlParams[$paramName];
        }
    }

    /**
     * @param string $objectName
     */
    private function setObjectName($objectName)
    {
        $this->_objectName = $objectName;
    }

    /**
     * @return string
     */
    private function getObjectName()
    {
        if( !isset($this->_objectName) || empty($this->_objectName) )
            throw new \InvalidArgumentException("ControllerFront; WSDL does not exist", self::ERROR_400);
        $wsdl = $this->getWsdlParams('view_path') . $this->_objectName . '.wsdl';
        if( !file_exists($wsdl) )
            throw new \InvalidArgumentException("ControllerFront; WSDL " . $this->_objectName . $wsdl . " does not exist", self::ERROR_400);

        return $this->_objectName;
    }

    /**
     * @param string $showWSDL
     */
    private function setShowWSDL($showWSDL)
    {
        $this->_showWSDL = $showWSDL;
    }

    /**
     * @return string
     */
    private function getShowWSDL()
    {
        if( empty($this->_showWSDL) || $this->_showWSDL != 'show' )
            return '';
        return $this->_showWSDL;
    }

    /**
     * @param string $objectName
     * @param string $showWsdl
     * @param array $wsdlParams
     */
    public function __construct( $objectName, $showWsdl = null, $wsdlParams = array() ) {
        $this->setObjectName($objectName);
        $this->setShowWSDL($showWsdl);
        $this->setWsdlParams($wsdlParams);
    }


    protected function renderWsdlFile( $objectName ) {
        header('HTTP/1.1 200 OK');
        header('Content-type: text/xml; charset=utf-8');
        $tpl = new Template\Templater();
        $tpl->doHtml( $this->getWsdlParams('view_path') . $objectName . '.wsdl', $this->getWsdlParams());
    }

    protected function runServiceMethod( $objectName, $wsdlParams ) {
        try {
            $rest = new Mapper( $objectName, $this->getWsdlParams() );
            $map = $rest->setWsdlSoapMap();
            $this->checkXmlRequestValidation($this->getRequestFromEnvelope());
            $this->soapRequest( $map->getServiceUri(), $map->getClassName(), $wsdlParams );
        } catch( \Exception $ex ) {
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }

    private function checkXmlRequestValidation($xmlData) {
        if( empty($xmlData) )
            return true;
        $tpl = new Template\Templater();
        $xsl = $tpl->formOutput( dirname(__FILE__) . '/../xsl/get_xsd_schema.xsl' );
        $wsdl = $tpl->formOutput( $this->getWsdlParams('view_path') . $this->getObjectName() . '.wsdl' );
        $xslt = new Xslt\Transformer();
        $xmlObj = $xslt->transform($wsdl, $xsl);

        $xsd = $xmlObj->asXML();
        $result = $xslt->validateXmlByXsd($xmlData, $xsd);
        if( $result['validation'] === false )
            throw new \Exception("Output error: " . $result['errors'][0], self::ERROR_500);
        return true;
    }

    /**
     * get clear XML from SOAP envelop
     *
     * @return string
     */
    private function getRequestFromEnvelope() {
        $soapEnvelope = file_get_contents("php://input");
        $objNamePart = substr($this->getObjectName(), 0, 3);
        $soapEnvelope = substr($soapEnvelope, strpos($soapEnvelope, '<typ:'), strpos($soapEnvelope, '</' . $objNamePart) - strpos($soapEnvelope, '<typ:'));
        $soapEnvelope = str_replace('<typ:', '<', $soapEnvelope);
        $request = str_replace('</typ:', '</', $soapEnvelope);
        return $request;
    }

    public function soapRequest( $uri, $apiClass, $wsdlParams ) {
        try {
            $server = new \SoapServer(null, array('uri' => $uri));
            $server->setClass($apiClass, $wsdlParams);
            $server->handle();
        } catch( \Exception $ex ) {

            throw new \Exception($ex->getMessage(), self::ERROR_500);
        }
    }

    public function process() {
        try {
            if ( $this->getShowWSDL() == 'show' )
                $this->renderWsdlFile( $this->getObjectName() );
            else
                $this->runServiceMethod( $this->getObjectName(), $this->getWsdlParams() );
        } catch(\Exception $ex) {
            $responseObj = new RestSoap\Response('xml');
            $response = $responseObj->setHeader($ex->getCode())->getErrorResponse($ex->getMessage(), $ex->getCode());
            echo $response;
        }
    }
}