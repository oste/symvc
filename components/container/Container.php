<?php

// Container is the only default component tha tis homemade
// And it extends a symfony component!
class Container extends sfServiceContainer {

    //parse all yaml config files and place them in applicaiton/config/symvcConfig.php
    public function parseYaml(){
        
        require_once 'components/yaml/lib/sfYamlParser.php';
        $parser = new sfYamlParser();
        $dir = "application/config/";
        $dh = opendir($dir);
        //for every file parse its yaml ande place it in an array called $symvcConfig
        while (($file = readdir($dh)) !== false) {
            if(filetype($dir . $file) == 'file' && strpos($file, 'php') === false){

               $parsedYaml = $parser->parse(file_get_contents($dir.$file));
               $file = str_replace('.yml', '', $file);
               //create the array
               $symvcConfig[$file] = $parsedYaml;

               
            }
        }
        closedir($dh);
        //place $symvcConfig in the symvcConfig.php file so we dont have to parse yaml in production
        $symvcConfig = var_export($symvcConfig, 1);
        file_put_contents('application/config/symvcConfig.php',
                "<?php \n
                class symvcConfig{

                    public static function config(){
                        return $symvcConfig;
                    }
                }
                \n ?>");

    }

    //uses sfServiceContainer to return a sfServiceContainerBuilder object
    public function serviceContainerBuilder($parameters = array()){
        return new sfServiceContainerBuilder($parameters);
    }

    //register the controller and pass it the service container and any get/post variables
    public function registerController($sc, $post, $get){

        //get the $config array from application/config/symvcConfig.php
        $config = symvcConfig::config();

        //parse the url and remove the ignoreUrl found in symvcConfig.php
        $parsedUrl = parse_url("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $path =  str_replace($config['config']['ignoreUrl'], '', $parsedUrl['path']);
        $pathArr = explode('/', $path);

        //use the path to set the controller, action and segments
        $pathCount = count($pathArr) - 1;
        ($pathArr[1]) ? $controller = '/'.$pathArr[1] : $controller = '/';
        ($pathArr[2]) ? $action = $pathArr[2] : $action = false;

        //populate segment array if there are segements
        $segment = array();
        $count = 3;
        if($pathCount >= 3){
            foreach($path as $p){
               ($count <= $pathCount) ? array_push($segment, $pathArr[$count]) : '';
                $count++;
            }
        }

        $routes = $config['routing'];

        //check all routes and see if there is a controller to match
        foreach($routes as $route){
            if($controller == $route['pattern']){
                $controller = $route['defaults']['_controller'];
            }
        }

        //create a controller and pass it the segments and service container
        //and then execute the action
        if($controller){

            $action = ($action) ? $action.'Action' : 'indexAction';
            //include the controller files
            require_once '/application/Controllers/Controller.php';
            require_once '/application/Controllers/'.$controller.'.php';

            //create the  controller and pass it segments, the service container and any get/post variables then invoke the action
            $displayController = new $controller();
            $displayController->segment = $segment;
            $displayController->sc = $sc;
            $displayController->post = $post;
            $displayController->get = $get;
            $displayController->$action();

        }

    }
    
    //provide a connection to controllers and models $this->conn()->
    protected function conn($conn = null){

        //get database config and connectionType
        $databaseConfig = $this->databaseConfig($conn);
        $connectionType = strtolower($databaseConfig['connectionType'][0]);

        //if they are using doctrine return that
        //TODO: make this work for php 5.2
        if($connectionType == 'doctrine'){

            $connectionType = $databaseConfig['connectionType'][1];

            require_once '/components/doctrine-common/lib/Doctrine/Common/ClassLoader.php';
            $classLoader = new Doctrine\Common\ClassLoader('Doctrine\Common', '/components/doctrine-common/lib');
            $classLoader->register();
            $classLoader = new Doctrine\Common\ClassLoader('Doctrine', '/components/doctrine-'.$connectionType.'/lib');
            $classLoader->register();
           ($connectionType == 'dbal') ? $config = new \Doctrine\DBAL\Configuration() : $config = new \Doctrine\ORM\Configuration();

            return \Doctrine\DBAL\DriverManager::getConnection($databaseConfig['connectionParams']);

        }
        //if they are using pdo return that
        if($connectionType == 'pdo'){

            $connectionType = $databaseConfig['connectionType'][1];
            $host = $databaseConfig['connectionParams']['host'];
            $dbName = $databaseConfig['connectionParams']['dbname'];


            $dsn = "$connectionType:host=$host;dbname=$dbName";
            return new PDO($dsn, $databaseConfig['connectionParams']['user'], $databaseConfig['connectionParams']['password']);
        }

    }

    //parses /config/database.yml to get the datbase config
    private function databaseConfig($conn = null){

        $config = symvcConfig::config();
        $dbConf = $config['database'];
        //convert $dbConf to an interator
        $arrIt = new RecursiveIteratorIterator(new RecursiveArrayIterator($dbConf));

        //get the default_connection
        //use default_connection to get the connection params
         foreach ($arrIt as $arr) {
            $subArray = $arrIt->getSubIterator();
            if(!$conn){
                $conn = $subArray['default_connection'];
            }
            if ($subArray['connections'][$conn]) {
                $connectionParams = $subArray['connections'][$conn];
            }
        }

        //create an array with the connectionType and connectionParams
        $connectionType = array_keys($dbConf);
        $connectionType = explode('.', $connectionType[0]);

        $databaseConfig['connectionType'] = $connectionType;
        $databaseConfig['connectionParams'] = $connectionParams;
        return $databaseConfig;

    }
    
}
?>
