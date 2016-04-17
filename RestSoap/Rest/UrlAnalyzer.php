<?php
/**
 * Разбирает REST URL и маппит на параметры из wsdl
 * Договариваемся, что урлы будут иметь вид - /api/rest/{module}/json|xml/{object}/{param1}/{param2}/{param3}
 * где
 * module - имя wsdl без суффикса .wsdl.phtml в директории /views/front/api/wsdl
 * json|xml - формат запроса/ответа
 * object - название объекта с которым производится действие GET, POST или PUT
 * param1...n - значения входных параметров при GET запросе
 *
 * На выходе класс формирует соответствующую структуру, которая будет использоваться в Api_Rest_Controller
 *
 */
namespace RestSoap\Rest;

use RestSoap;
use RestSoap\Template;
use RestSoap\Xslt;

class UrlAnalyzer extends RestSoap\ApiBase {

    private $_url;

    /**
     * Get array of GET-parameters from URL
     *
     * @return array
     * @throws \Exception
     */
    private function extractGetParamsFromUrl() {
        if( !isset($this->_url) || empty($this->_url)  ) {
            throw new \InvalidArgumentException("UrlAnalyzer; Parameter url does not exist", self::ERROR_500 );
        }
        $uri = explode('?', $this->_url);
        if( !isset($uri[1]) ) {
            return [];
        }
        $getParamsSource = explode('&', $uri[1]);
        $getParams = [];
        foreach( $getParamsSource as $key => $value ) {
            $res = explode('=', $value);
            if( count($res) != 2 ) {
                continue;
            }
            $getParams[$res[0]] = $res[1];
        }
        return $getParams;
    }

    /**
     * @param string $url
     */
    private function setUrl($url)
    {
        $this->_url = $url;
    }

    /**
     * @return string
     */
    private function getUrlStructure()
    {
        if( !isset($this->_url) || empty($this->_url)  ) {
            throw new \InvalidArgumentException("UrlAnalyzer; Parameter url does not exist", self::ERROR_500 );
        }
        $uri = explode('?', $this->_url);
        $urlStructure = explode('/', $uri[0]);
        return $urlStructure;
    }

    private $_viewer;

    /**
     * @return Template\Templater
     */
    protected function getViewer() {
        if( $this->_viewer instanceof Template\Templater ) {
            return $this->_viewer;
        } else {
            $this->_viewer = new Template\Templater();
            return $this->_viewer;
        }
    }

    private $_wsdlParams;

    /**
     * @param array $wsdlParams
     */
    protected function setWsdlParams($wsdlParams)
    {
        $this->_wsdlParams = $wsdlParams;
    }

    /**
     * @return array
     */
    protected function getWsdlParams($paramName = '')
    {
        if( !isset($this->_wsdlParams) || empty($this->_wsdlParams) ) {
            return [];
        } else {
            if(!empty($paramName) && isset($this->_wsdlParams[$paramName])) {
                return $this->_wsdlParams[$paramName];
            } else {
                return $this->_wsdlParams;
            }
        }
    }

    private $_httpMethod;

    /**
     * @param string $httpMethod
     */
    protected function setHttpMethod($httpMethod)
    {
        $this->_httpMethod = $httpMethod;
    }

    /**
     * @return string
     */
    protected function getHttpMethod()
    {
        if( !isset($this->_httpMethod) || empty($this->_httpMethod) ) {
            return 'GET';
        } else {
            return $this->_httpMethod;
        }
    }


    /**
     * 
     * @param string $url
     * @param string $httpMethod
     * @param array $wsdlParams
     */
    public function __construct($url, $httpMethod, $wsdlParams = [] ) {
        $this->setUrl($url);
        $this->setHttpMethod($httpMethod);
        $this->setWsdlParams($wsdlParams);
    }

    /**
     * 
     * @return array
     */
    public function getStructure() {
        $module = $this->getModuleName();
        $outputType = $this->getOutputType();
        $restObject = $this->getRestObjectInfo();
        $params = $this->getParameters();

        return [ 'module'      => $module,
                 'outputType'    => $outputType,
                 'restObject'    => $restObject['call'],
                 'httpMethod'    => $restObject['http_method'],
                 'PHPclass'      => $restObject['class'],
                 'PHPmethod'     => $restObject['method'],
                 'request'       => $params ];
    }

    /**
     * Get WSDL-module name from URL. Position 3 in URL
     *
     * @return string
     */
    protected function getModuleName() {
        $url = $this->getUrlStructure();
        if( !isset($url[3]) || empty($url[3]) )
            throw new \Exception("UrlAnalyzer; Module does not exist", self::ERROR_400);
        $moduleName = $url[3];

        $wsdlViewPath = $this->getWsdlParams('view_path');
        $wsdl =  $wsdlViewPath . $moduleName . '.wsdl';
        if( !file_exists($wsdl) )
            throw new \Exception("UrlAnalyzer; Module " . $moduleName. " does not exist", self::ERROR_400);
        return $moduleName;
    }

    /**
     * Get request type from URL - xml or json. Position 4 in URL
     *
     * @return string
     */
    public function getOutputType() {
        $url = $this->getUrlStructure();
        if( !isset($url[4]) || empty($url[4]) ) {
            throw new \InvalidArgumentException("UrlAnalyzer; outputType does not exist", self::ERROR_400);
        }
        $outputType = $url[4];

        if( !in_array($outputType, [self::RESP_JSON, self::RESP_XML, self::RESP_XML_TEST, self::RESP_RAW]) ) {
            throw new \InvalidArgumentException("UrlAnalyzer; outputType " . $outputType. " is not provided", self::ERROR_400);
        }

        return $outputType;
    }

    /**
     * Get object name. Position 5 in URL
     *
     * @return string
     */
    private function getRestObjectInfo() {
        $url = $this->getUrlStructure();
        if( !isset($url[5]) || empty($url[5]) ) {
            throw new \InvalidArgumentException("UrlAnalyzer; rest object name does not exist", self::ERROR_400);
        }
        $restObjectName = $url[5];

        $tpl = $this->getViewer();
        $xsl = $tpl->get( dirname(__FILE__) . '/../xsl/rest_mapper.xsl', []);
        $wsdlViewPath = $this->getWsdlParams('view_path');
        $wsdlContent = $tpl->get( $wsdlViewPath . $this->getModuleName() . '.wsdl', $this->getWsdlParams());

        $xslt = new Xslt\Transformer();
        $xml = $xslt->transform($wsdlContent, $xsl);

        $result = [];
        $exception = true;
        if( !$xml ) {
            throw new \Exception("UrlAnalyzer; WSDL-document is invalid", self::ERROR_500);
        }
        foreach( $xml as $xmlRestTag ) {
            if( !empty($xmlRestTag['call'])
                &&(string)$xmlRestTag['call'] == $restObjectName
                && !empty($xmlRestTag['http_method'])
                && $xmlRestTag['http_method'] == $this->getHttpMethod()
                && !empty($xmlRestTag['method']) ) {

                $exception = false;
                $result['call'] = (string)$xmlRestTag['call'];
                $result['http_method'] = (string)$xmlRestTag['http_method'];
                $result['class'] = (string)$xmlRestTag['class'];
                $result['method'] = (string)$xmlRestTag['method'];
                break;
            }
        }

        if( $exception ) {
            throw new \InvalidArgumentException("UrlAnalyzer; rest object " . $restObjectName . " does not exist", self::ERROR_400);
        }

        return $result;
    }

    protected function getParamsWithDescriptions() {
        $restObjectInfo = $this->getRestObjectInfo();
        $tpl = $this->getViewer();
        $xsl = $tpl->get( dirname(__FILE__) . '/../xsl/get_request_params.xsl', ['call' => $restObjectInfo['call'], 'httpMethod' => $restObjectInfo['http_method']] );
        $wsdlViewPath = $this->getWsdlParams('view_path');
        $wsdlContent = $tpl->get( $wsdlViewPath . $this->getModuleName() . '.wsdl', $this->getWsdlParams());

        $xslt = new Xslt\Transformer();
        $xml = $xslt->transform($wsdlContent, $xsl);

        $paramsDesc = [];
        foreach( $xml as $xmlRestTag ) {
            if( (string)$xmlRestTag->type == 'NMTOKEN' ) {
                $enum = [];
                foreach($xmlRestTag->enum->item as $enumItem) {
                    $enum[] = (string)$enumItem;
                }
                $paramsDesc[(string)$xmlRestTag->name] = [ 'type' => (string)$xmlRestTag->type,
                                                           'enum' => $enum,
                                                           'required' => (bool)((string)$xmlRestTag->required) ];
            } else {
                $paramsDesc[(string)$xmlRestTag->name] = [ 'type' => (string)$xmlRestTag->type,
                                                           'required' => (bool)((string)$xmlRestTag->required) ];
            }
        }
        return $paramsDesc;
    }

    /**
     * Get array of parameters from URL. Positions 6...n in URL
     *
     * @return array
     */
    private function getParameters() {
        $getParams = $this->extractGetParamsFromUrl();
        $paramsDesc = $this->getParamsWithDescriptions();

        $params = [];
        $url = $this->getUrlStructure();
        // URL offset
        $urlOffset = 6;
        foreach( $paramsDesc as $paramName => $paramType ) {
            $val = null;
            if( isset($url[$urlOffset]) && !empty($url[$urlOffset]) ) {
                $val = $url[$urlOffset];
            }
            $params[$paramName] = $this->getParamValue($paramName, $paramType, $val);
            $urlOffset++;

            // Override parameters in URL with parameters form GET-array.
            // Example: /param1/param2/?param2=xxx&param6=yyy returns param1, param2=xxx, param6
            if( isset($getParams[$paramName]) && !empty($getParams[$paramName]) ) {
                $params[$paramName] = $this->getParamValue($paramName, $paramType, $getParams[$paramName]);
            }
            if( (!isset($params[$paramName]) || $params[$paramName] == null) && $paramType['required'] === true && $this->getHttpMethod() == 'GET' ) {
                throw new \Exception("Element '" . $paramName . "' is required.", self::ERROR_403);
            }
        }
        return $params;
    }

    protected function getParamValue($paramName, $paramType, $value = null) {
        $resultValue = null;
        if( $value == null && $paramType['required'] === true ) {
            return null;
        }
        
        switch($paramType['type']) {
            case 'int':
                if( $value != "0" &&  (int)$value == 0 && $paramType['required'] === true ) {
                    throw new \Exception("Element '" . $paramName . "': '" . $value . "' is not a valid value of the atomic type 'xs:int'.", self::ERROR_403);
                }
                if( !is_null($value) ) {
                    $resultValue = (int)$value;
                } else {
                    $resultValue = 0;
                }
                break;
            case 'string':
                if( !is_null($value) && !empty($value) ) {
                    $resultValue = $value;
                } else {
                    $resultValue = '';
                }
                break;
            case 'float':
                if( !is_null($value) ) {
                    $resultValue = (float)$value;
                } else {
                    $resultValue = 0;
                }
                break;
            case 'datetime':
                if( !is_null($value) ) {
                    $resultValue = $value;
                } else {
                    $resultValue = date("d.m.Y");
                }
                break;
            case 'boolean':
                if( $value != "true" ||  $value != 'false' ) {
                    throw new \Exception("Element '" . $paramName . "': '" . $value . "' is not a valid value of the atomic type 'xs:boolean'.", self::ERROR_403);
                }
                if( !is_null($value) ) {
                    $resultValue = (bool)$value;
                } else {
                    $resultValue = true;
                }
                break;
            case 'NMTOKEN':
                if( !in_array($value, $paramType['enum']) ) {
                    $list = implode('"|"', $paramType['enum']);
                    throw new \Exception("Wrong input value for parameter '" . $paramName . "'. Expected value is '" . $list . "'", self::ERROR_403);
                }
                $resultValue = $value;
                break;
            default:
                $resultValue = $value;
                if( is_null($value) ) {
                    $resultValue = '';
                }
                break;
        }
        return $resultValue;
    }
}