<?php
namespace RestSoap\Rest;

use RestSoap;
use RestSoap\Template;
use RestSoap\Xslt;

class ControllerFront extends RestSoap\ApiBase {

    private $_phpServerParams;
    private $_httpBody;
    private $_wsdlParams;

    /**
     * @param array $wsdlParams
     */
    private function setWsdlParams($wsdlParams)
    {
        $this->_wsdlParams = $wsdlParams;
    }

    /**
     * @return array
     */
    private function getWsdlParams($paramName = '')
    {
        if( empty($paramName) )
            return $this->_wsdlParams;
        else {
            if( !isset($this->_wsdlParams[$paramName]) )
                throw new \InvalidArgumentException("RestSoap ControllerFront; " . $paramName . " parameter does not exist", self::ERROR_400);
            return $this->_wsdlParams[$paramName];
        }
    }

    /**
     * @param string $httpBody
     */
    private function setHttpBody($httpBody)
    {
        $this->_httpBody = $httpBody;
    }

    /**
     * @return string
     */
    public function getHttpBody()
    {
        return $this->_httpBody;
    }

    /**
     * @param array $phpServerParams
     */
    private function setPhpServerParams($phpServerParams)
    {
        $this->_phpServerParams = $phpServerParams;
    }

    /**
     * @return array
     */
    public function getPhpServerParams()
    {
        return $this->_phpServerParams;
    }

    /**
     * @param array $phpServerParams
     * @param string $httpBody
     * @param array $params
     */
    public function __construct( array $phpServerParams, $httpBody, $params = [] ) {
        $this->setPhpServerParams($phpServerParams);
        $this->setHttpBody($httpBody);
        $this->setWsdlParams($params);
    }

    protected function getRequestType() {
        $vars = $this->getPhpServerParams();
        if( !isset($vars['REQUEST_METHOD']) || !in_array($vars['REQUEST_METHOD'], ['GET', 'POST', 'PUT', 'DELETE']))
            throw new \InvalidArgumentException("RestSoap ControllerFront; Request method is not specified", self::ERROR_400);
        return $vars['REQUEST_METHOD'];
    }

    protected function parseURL() {
        $vars = $this->getPhpServerParams();
        $wsdlViewPath = $this->getWsdlParams('view_path');
        $ua = new UrlAnalyzer($vars['REQUEST_URI'], $this->getRequestType(), ['view_path' => $wsdlViewPath] );
        $urlStructure = $ua->getStructure();
        $this->setUrLStructure($urlStructure);
        return $urlStructure;
    }

    /**
     * Returns request type for checking exceptions
     *
     * @return string
     */
    private function getOutputType() {
        try {
            $vars = $this->getPhpServerParams();
            $ua = new UrlAnalyzer($vars['REQUEST_URI'], $this->getRequestType());
            $outputType = $ua->getOutputType();
        } catch(\Exception $ex) {
            return 'xml';
        }
        return $outputType;
    }

    private $_urLStructure;

    /**
     * @param array $urLStructure
     */
    private function setUrLStructure($urLStructure)
    {
        $this->_urLStructure = $urLStructure;
    }

    /**
     * @return array
     */
    private function getUrLStructure()
    {
        if( isset($this->_urLStructure) && is_array($this->_urLStructure) && !empty($this->_urLStructure) )
            return $this->_urLStructure;
        else
            return null;
    }

    /**
     * Get input array data from http body
     *
     * @param string $xmlRequestRootTitle
     * @param array $getParams
     * @return array
     */
    protected function getRequestBody($xmlRequestRootTitle, $getParams) {
        $requestType = $this->getRequestType();
        if( !in_array($requestType, ['POST', 'PUT']) ) {
            return [];
        }
        $urlStructure = $this->getUrLStructure();
        if( is_null($urlStructure) ) {
            throw new \InvalidArgumentException("ControllerFront; URL is not analyzed. Run parseURL method at first", self::ERROR_400);
        }
        if( !isset($urlStructure['outputType']) || !in_array($urlStructure['outputType'], [RestSoap\ApiBase::RESP_JSON, RestSoap\ApiBase::RESP_XML, RestSoap\ApiBase::RESP_RAW, RestSoap\ApiBase::RESP_XML_TEST]) ) {
            throw new \InvalidArgumentException("ControllerFront; Can not find outputType in url structure array", self::ERROR_400);
        }
        if( !isset($urlStructure['restObject']) || empty($urlStructure['restObject']) ) {
            throw new \InvalidArgumentException("ControllerFront; Can not find restObject in url structure array", self::ERROR_400);
        }
        if( !isset($urlStructure['module']) || empty($urlStructure['module']) ) {
            throw new \InvalidArgumentException("ControllerFront; Can not find module in url structure array", self::ERROR_400);
        }

        $result = [];
        $inputAnalyzer = new InputAnalyzer($this->getHttpBody(), $urlStructure['restObject'], $urlStructure['module'], ['view_path' => $this->getWsdlParams('view_path')]);
        switch($urlStructure['outputType']) {
            case RestSoap\ApiBase::RESP_RAW:
                $result = $inputAnalyzer->getRawHttpBody();
                break;
            case RestSoap\ApiBase::RESP_JSON:
                $result = $inputAnalyzer->getJsonHttpBody($xmlRequestRootTitle, $getParams);
                break;
            case RestSoap\ApiBase::RESP_XML:
            case RestSoap\ApiBase::RESP_XML_TEST:
                $result = $inputAnalyzer->getXmlHttpBody($xmlRequestRootTitle, $getParams);
                /**
                 *  process xml like
                 *
                 * <requestMethodName>
                 *  <param1>...</param1>
                 *  </param2>...</param2>
                 * </requestMethodName>
                 */
                break;
        }
        return $result;
    }

    /**
     * @return string
     */
    public function process() {
        try {
            $httpMethod = $this->getRequestType();
            $urlStructure = $this->parseURL();
            
            $inputParams = $urlStructure['request'];

            if( $httpMethod != $urlStructure['httpMethod'] )
                throw new \Exception("ControllerFront; Request HTTP Method " . $urlStructure['httpMethod'] . " is not the same as WSDL defined HTTP Method", self::ERROR_400);

            $xmlRequestRootTitle = $urlStructure['PHPmethod'] .  'RequestData';
            $xmlResponseRootTitle = $urlStructure['PHPmethod'] .  'ResponseData';

            $httpBodyParameters = $this->getRequestBody($xmlRequestRootTitle, $inputParams);

            $className = $urlStructure['PHPclass'];
            $method = $urlStructure['PHPmethod'];
            if(!class_exists ($className))
                throw new \Exception("Rest object does no exist", self::ERROR_500);


            $responseObj = new RestSoap\Response($urlStructure['outputType'], $this->getWsdlParams('view_path') . $urlStructure['module'], $xmlResponseRootTitle);

            if( $httpMethod != 'GET' && is_array($httpBodyParameters) ) {
                $inputParams = array_merge($inputParams, $httpBodyParameters['request']);
            }
            $inputObjects = $this->arrayToObject($inputParams);
            
            $class = new $className($this->getWsdlParams());
            $result = $class->$method($inputObjects);
            /**
             * Outstanding features are thrown with exceptions
             * HTTP Status 200 is taken from methods.
             * Attention! If you don't throw incorrect http code from method you'll get code 500.
             */
            return $responseObj->setHeader(self::STATUS_200)->getSuccessResponse($result);

        } catch( \Exception $ex ) {
            $responseObj = new RestSoap\Response($this->getOutputType());
            return $responseObj->setHeader($ex->getCode())->getErrorResponse($ex->getMessage(), $ex->getCode());
        }
    }

}