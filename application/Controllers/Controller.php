<?php

class Controller extends Container {

    protected $segment = array();//uri segments
    protected $sc;//service container created in index.php
    protected $post;//post variables
    protected $get;//get variables

    //provide the template engine to controllers $this->render(template, dataToBePassed);
    protected function render($template, $parameters ){

        require_once '/components/templating/lib/sfTemplateAutoloader.php';
        sfTemplateAutoloader::register();
        $loader = new sfTemplateLoaderFilesystem(dirname(__DIR__).'/Templates/%name%.php');
        $engine = new sfTemplateEngine($loader);
        echo $engine->render($template, $parameters);
        
    }

}
?>
