<?php 

                class symvcConfig{

                    public static function config(){
                        return array (
  'config' => 
  array (
    'ignoreUrl' => '/symvc',
  ),
  'database' => 
  array (
    'doctrine.dbal' => 
    array (
      'default_connection' => 'development',
      'connections' => 
      array (
        'production' => 
        array (
          'dbname' => 'doctrine',
          'user' => 'root',
          'password' => NULL,
          'host' => 'localhost',
          'driver' => 'pdo_mysql',
        ),
        'development' => 
        array (
          'dbname' => 'doctrine',
          'user' => 'root',
          'password' => NULL,
          'host' => 'localhost',
          'driver' => 'pdo_mysql',
        ),
      ),
    ),
  ),
  'routing' => 
  array (
    'index' => 
    array (
      'pattern' => '/',
      'defaults' => 
      array (
        '_controller' => 'IndexController',
      ),
    ),
    'index.index' => 
    array (
      'pattern' => '/index',
      'defaults' => 
      array (
        '_controller' => 'IndexController',
      ),
    ),
  ),
);
                    }
                }
                
 ?>