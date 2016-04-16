<?php

/**
 * Class ApiBase
 *
 * @package RestSoap
 */
namespace RestSoap;

class ApiBase {

    const STATUS_200 = 200;
    const ERROR_400 = 400;
    const ERROR_403 = 403;
    const ERROR_500 = 500;

    const RESP_SOAP = 'soap';
    const RESP_JSON = 'json';
    const RESP_XML = 'xml';
    const RESP_XML_TEST = 'xml_test'; // used for output XML without validation
    const RESP_RAW = 'raw';

	/**
     * Преобразовать PHP-массив в XML
     *
     * Метод также используется в PHPUnit тестах сервисов
     *
     * @param array $data
     * @param \SimpleXMLElement $xmlBlank
     */
    public function arrayToXml($data, \SimpleXMLElement &$xmlBlank, $itemName = 'item') {
        foreach($data as $key => $value) {
            if(is_array($value) || is_object($value)) {
                if(!is_numeric($key)){
                    $subnode = $xmlBlank->addChild((string)$key);
                    $this->arrayToXml($value, $subnode, $itemName);
                }
                else{
                    $subnode = $xmlBlank->addChild($itemName);
                    $this->arrayToXml($value, $subnode, $itemName);
                }
            }
            else {
                $val = $value;
                if( is_bool($value) )
                    $val = ($value) ? 'true' : 'false';
                else if( is_int($value) )
                    $val = (int)$value;
                else
                    $val = htmlspecialchars("$value");

                if(!is_numeric($key))
                    $xmlBlank->addChild("$key", $val );
                else {
                    $xmlBlank->addChild($itemName, $val );
                }
            }
        }
    }
}