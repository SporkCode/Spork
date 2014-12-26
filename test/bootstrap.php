<?php
namespace SporkTest;

use Zend\Mvc\Service\ServiceManagerConfig;
use Itt\Lib\ServiceManager\ServiceManager;

class Bootstrap
{
	public static function init()
	{
	    /* @var $autoloader \Zend\Loader\StandardAutoloader */
	    $autoloader = require __DIR__ . '/../autoloader.php';
	    $autoloader->registerNamespace('Spork', __DIR__ . '/../src');
	    $autoloader->registerPrefixes(array(
	        'PHPUnit'   => '/usr/share/php/PHPUnit',
	        'File'      => '/usr/share/php/File',
	        'PHP'       => '/usr/share/php/PHP',
	        'Text'      => '/usr/share/php/Text',
	    ));
	    
	    $autoloader->register();
	} 
}

Bootstrap::init();