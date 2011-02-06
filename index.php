<?php
    //debugging/ developing?
    $symvcDebug = true;

    //autoload Container, DI classes and Model classes
    function __autoload($class){
        if($class == 'Container'){
            require 'components/Container/Container.php';
        }
        else if(strpos($class, 'sfService') === 0){
            require 'components/dependency-injection/lib/'.$class.'.php';
        }
        else{
            require 'application/Model/'.$class.'.php';
        }
    }

    //create the container
    $container = new Container();

    //if debugging create symvcConfig class otherwise just include it
    if($symvcDebug){
        $container->parseYaml();
        require_once 'application/config/symvcConfig.php';
    }else{
        require_once 'application/config/symvcConfig.php';
    }

    //create a sfServiceContainerBuilder
    $sc = $container->serviceContainerBuilder();
    //register services here
    //....
    //....

    //register the controller and pass it the service container and any get/post variables
    $container->registerController($sc, $_POST, $_GET);


?>
