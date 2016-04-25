<?php

/*
 * Example controller that provides access to REST and SOAP access to API methods
 */
class ApiController{

    /**
     * REST API
     */
    public function restAction() 
    {
        $httpBody = file_get_contents("php://input");
        // view_path - required parameter which set path to directory that contains wsdl file
	$view_path = APPLICATION_PATH . '/src/Api/Example/views/';
        $controller = new \RestSoap\Rest\ControllerFront($_SERVER, $httpBody, ['view_path' => $view_path] );
        $response = $controller->process();
        echo $response;
    }

    /**
     * SOAP API
     */
    public function soapAction()
    {
        $objectName = isset($_GET['module']) ? $_GET['module'] : null;
        $showWsdl = isset($_GET['wsdl']) ? $_GET['wsdl'] : false;
        // view_path - required parameter which set path to directory that contains wsdl file
	$view_path = APPLICATION_PATH . '/src/Api/Example/views/';
        $soap = new \RestSoap\Soap\ControllerFront( $objectName, $showWsdl, ['view_path' => $view_path] );
        $soap->process();
    }

    /**
     * Automatic documentation example
     */
    public function documentationAction() 
    {
        $service = 'example';
        $template = 'example_doc.xsl';

        $tpl = new \RestSoap\Template\Templater();
        $xsl = $tpl->get( APPLICATION_PATH . '/src/Api/Example/views/' . $template, []);
        $wsdlContent = $tpl->get(APPLICATION_PATH . '/src/Api/Example/views/' . $service . '.wsdl', []);

        $xslt = new \RestSoap\Xslt\Transformer();
        $xml = $xslt->transformToDocument($wsdlContent, $xsl);
        echo $xml->saveXML();
    }
}
