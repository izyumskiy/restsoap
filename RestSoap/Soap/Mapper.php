<?php
namespace RestSoap\Soap;

use RestSoap;
use RestSoap\Template;
use RestSoap\Xslt;

class Mapper extends RestSoap\ApiBase {

    private $_wsdlTitle;
    private $_map;
    private $_viewParameters;

    /**
     * @param array $viewParameters
     */
    private function setViewParameters($viewParameters)
    {
        if( !is_array($viewParameters) ) {
            $this->_viewParameters = array();
        } else {
            $this->_viewParameters = $viewParameters;
        }
    }

    /**
     * @return array
     */
    private function getViewParameters($paramName = '')
    {
        if( empty($paramName) ) {
            return $this->_viewParameters;
        } else {
            if( !isset($this->_viewParameters[$paramName]) ) {
                throw new \Exception("Mapper; " . $paramName . " parameter does not exist", self::ERROR_400);
            }
            return $this->_viewParameters[$paramName];
        }
    }

    /**
     * @param mixed $wsdl
     */
    private function setWsdlTitle($wsdl)
    {
        $this->_wsdlTitle = $wsdl;
    }

    /**
     * @return mixed
     */
    public function getWsdlTitle()
    {
        if( !isset($this->_wsdlTitle) ) {
            throw new \Exception("Mapper; wsdlTitle parameter doesn't exist", self::ERROR_400);
        }
        
        return $this->_wsdlTitle;
    }

    /**
     * @param mixed $map
     */
    private function setMap($map)
    {
        $this->_map = $map;
    }

    public function getClassName() {
        if( !isset($this->_map) || !is_array($this->_map) || !isset($this->_map['class']) ) {
            throw new \Exception('Mapper; Parameter class did not map from wsdl to php object', self::ERROR_500);
        }
        
        return $this->_map['class'];
    }

    public function getMethodName() {
        if( !isset($this->_map) || !is_array($this->_map) || !isset($this->_map['method']) ) {
            throw new \Exception('Mapper; Parameter method did not map from wsdl to php object', self::ERROR_500);
        }
        
        return $this->_map['method'];
    }

    public function getServiceUri() {
        if( !isset($this->_map) || !is_array($this->_map) || !isset($this->_map['uri']) ) {
            throw new \Exception('Mapper; Parameter uri did not map from wsdl to php object', self::ERROR_500);
        }
        
        return $this->_map['uri'];
    }

    public function __construct($wsdlTitle, $viewParameters = array()) {
        $this->setWsdlTitle($wsdlTitle);
        $this->setViewParameters($viewParameters);
    }

    /**
     * Set SOAP mapping from WSDL to PHP
     *
     * @param $object
     */
    public function setWsdlSoapMap() {
        $tpl = new Template\Templater();
        $xsl = $tpl->get( dirname(__FILE__) . '/../xsl/get_soap_class.xsl', array());
        $wsdlContent = $tpl->get( $this->getViewParameters('view_path') . $this->getWsdlTitle() . '.wsdl', $this->getViewParameters());

        $xslt = new Xslt\Transformer();
        $xml = $xslt->transform($wsdlContent, $xsl);
        $map = array('class' => (string)$xml->php_class, 'uri' => (string)$xml->uri );
        $this->setMap($map);
        return $this;
    }

}