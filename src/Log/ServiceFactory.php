<?php
/**
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */

namespace Spork\Log;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service factory to create and configure a Logger instance optionally overriding default class
 * 
 * Example configuration
 * array(
 *     'log' => array(
 *         'class' => 'My\Logger\Class',
 *         ...
 *     ),
 * );
 * 
 */
class ServiceFactory implements FactoryInterface
{
    /**
     * Creates a Logger service
     * 
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Zend\Log\LoggerInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $appConfig = $serviceLocator->get('config');
        $config = array_key_exists('log', $appConfig) ? $appConfig['log'] : array();
        $class = array_key_exists('class', $config) ? $config['class'] : 'Zend\Log\Logger';
        $logger = new $class($config);
        return $logger;
    }
}