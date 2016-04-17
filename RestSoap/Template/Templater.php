<?php

namespace RestSoap\Template;

class Templater {
    
    /**
     * Returns template data to output
     *
     * @param string $tplPath
     * @param array $parameters
     */
    public function render( $tplPath, $parameters = [] ) {
        foreach ($parameters as $name => $value) {
            $$name = $value;
        }

        if (file_exists($tplPath)) {
            include $tplPath;
        }
    }

    /**
     * Returns generated template code
     *
     * @param string $tplPath
     * @param array $parameters
     */
    public function get( $tplPath, $parameters = [] ) {
        ob_start();
        $this->render($tplPath, $parameters);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}