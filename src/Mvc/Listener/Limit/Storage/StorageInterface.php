<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Mvc\Listener\Limit\Storage;

use Spork\DateTime\DateInterval;
use Spork\Mvc\Listener\Limit\Limit;

/**
 * Provides storage for Limit
 */
interface StorageInterface
{
    /**
     * Increment the count for a limit type of a specific IP address
     * 
     * @param string $ip IP address
     * @param string $type Name of limit type
     */
    public function increment($ip, $type);
    
    /**
     * Checks if an IP has reached a limit
     * 
     * 
     * @param string $ip IP address
     * @param Limit $limit
     * @return boolean
     */
    public function check($ip, Limit $limit);
    
    /**
     * Cleans expired events from storage 
     */
    public function clean();
}