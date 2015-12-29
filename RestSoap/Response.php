<?php
namespace RestSoap;

use RestSoap\Template;
use RestSoap\Xslt;

class Response extends ApiBase {

    private $_response;
    private $_outputType;
    private $_rootNode;
    private $_wsdl;

    /**
     * @param string $wsdl
     */
    private function setWsdl($wsdl)
    {
        $this->_wsdl = $wsdl;
    }

    /**
     * @return string
     */
    private function getWsdl()
    {
        return $this->_wsdl;
    }

    /**
     * Назначить корневой элемент для XML-ответа
     *
     * @param string $rootNode
     */
    public function setRootNode($rootNode)
    {
        $this->_rootNode = $rootNode;
    }

    /**
     * @return string
     */
    private function getRootNode()
    {
        if( !isset($this->_rootNode) || empty($this->_rootNode) )
            $this->setRootNode('response');
        return $this->_rootNode;
    }

	/**
	 * @return string
	 */
	private function getNameSpaces(){
		$attr_namespaces = "";
		$response = $this->getResponse();
		if(!empty($response['namespaces']) && is_array($response['namespaces'])){
			foreach($response['namespaces'] as $namespace => $url){
				$attr_namespaces .= $namespace.'="'.$url.'" ';
			};
		}
		return $attr_namespaces;
	}

	/**
	 * @return string
	 */
	private function getItemName(){
		$response = $this->getResponse();
		return (!empty($response['item_name'])) ? $response['item_name'] : 'item';
	}

    /**
     * @param array $response
     * @return Response
     */
    private function setResponse($response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * @return array
     */
    private function getResponse()
    {
        if( !isset($this->_response) || empty($this->_response) )
            throw new \Exception("Response does not exist", self::ERROR_500);
        return $this->_response;
    }

    /**
     * @param string $outputType
     * @return Response
     */
    private function setOutputType($outputType)
    {
        $this->_outputType = $outputType;
        return $this;
    }

    /**
     * @return string
     */
    private function getOutputType()
    {
        if( !isset($this->_outputType) )
            throw new \Exception("Output type does not defined", self::ERROR_500);
        if( !in_array($this->_outputType, array(self::RESP_SOAP, self::RESP_XML, self::RESP_JSON, self::RESP_XML_TEST) ) )
            throw new \Exception("Output type " . $this->_outputType . " is wrong", self::ERROR_500);
        return $this->_outputType;
    }

    /**
     * @param string $outputType
     * @param string $wsdl
     * @param string $rootNode
     */
    public function __construct($outputType, $wsdl = '', $rootNode = 'response') {
        $this->setOutputType($outputType);
        $this->setWsdl($wsdl);
        $this->setRootNode($rootNode);
    }

    /**
     * API response validation
     *
     * Response format:
     * status: 200|400|403|500
     * error: message
     * data: data array|string|integer
     *
     * @param array $response
     * @return Response
     */
    private function checkApiResponseCorrection() {
        $response = $this->getResponse();
        if( !is_array($response) )
            throw new \Exception("Empty response", self::ERROR_500);
        if( !isset($response['status']) || (int)$response['status'] == 0 )
            throw new \Exception("Response status is not defined", self::ERROR_500);

        if( !isset($response['data']) )
            throw new \Exception("Incorrect response", self::ERROR_500);
        return $this;
    }

    /**
     *
     * @param string $outputType
     * @return Response
     */
    private function validateXmlResponse($outputType) {
        if( $outputType != self::RESP_XML )
            return $this;
        $tpl = new Template\Templater();
        $xsl = $tpl->formOutput(dirname(__FILE__) . '/xsl/get_xsd_schema.xsl', array());
        $wsdl = $tpl->formOutput($this->getWsdl() . '.wsdl', array());
        $xslt = new Xslt\Transformer();
        $xmlObj = $xslt->transform($wsdl, $xsl );

        $xsd = $xmlObj->asXML();
        $result = $xslt->validateXmlByXsd($this->getResponse(), $xsd);
        if( $result['validation'] === false )
            throw new \Exception("Output error: " . $result['errors'][0], self::ERROR_500);
        return $this;
    }

    /**
     * Convert response according to request type - soap, json, xml
     *
     * @return Response
     */
    private function formatResponse() {
        try {
            $outputType = $this->getOutputType();
            switch($outputType) {
                case self::RESP_SOAP:
                    break;
                case self::RESP_JSON:
                    $this->setResponse(json_encode($this->getResponse()));
                    break;
                case self::RESP_XML:
                case self::RESP_XML_TEST:
                    $resp = $this->getResponse();
                    $rootNode = $this->getRootNode();
                    $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><" . $rootNode . "></" . $rootNode . ">");
                    $this->arrayToXml($resp, $xml);
                    $this->setResponse($xml->asXML());
                    break;
            }
        } catch(\Exception $ex) {
            throw new \Exception("Response formatting error", self::ERROR_500);
        }
        return $this;
    }

    /**
     * @param array $response
     * @return array
     */
    public function getSuccessResponse($response) {
        return $this->setResponse($response)
                    ->checkApiResponseCorrection()
                    ->formatResponse()
                    ->validateXmlResponse($this->getOutputType())
                    ->getResponse();
    }

    /**
     * @param string $errorMessage
     * @return array
     */
    public function getErrorResponse($errorMessage, $code) {
        if( !in_array($code, array(self::ERROR_400, self::ERROR_403, self::ERROR_500) )) {
            $code = self::ERROR_500;
        }
        $errorResponse = array('status' => (int)$code, 'error' => (string)$errorMessage, 'data' => array());
        return $this->setResponse($errorResponse)
                    ->checkApiResponseCorrection()
                    ->formatResponse()
                    ->getResponse();
    }

    public function setHeader($httpCode) {
        $outputType = $this->getOutputType();
        switch($outputType) {
            case self::RESP_JSON:
                header('HTTP/1.1 ' . $this->getHttpHeaderByCode($httpCode));
                header('Content-type: application/json; charset=utf-8');
                break;
            case self::RESP_XML:
            case self::RESP_XML_TEST:
                header('HTTP/1.1 ' . $this->getHttpHeaderByCode($httpCode));
                header('Content-type: text/xml; charset=utf-8');
                break;
        }
        return $this;
    }

    /**
     * @param int $code
     * @return string
     */
    private function getHttpHeaderByCode($code) {
        switch($code) {
            case self::STATUS_200:
                $header = '200 OK';
                break;
            case self::ERROR_400:
                $header = '400 Bad Request';
                break;
            case self::ERROR_403:
                $header = '403 Forbidden';
                break;
            case self::ERROR_500:
                $header = '500 Internal Server Error';
                break;
            default:
                $header = '500 Internal Server Error';
                break;
        }
        return $header;
    }
}