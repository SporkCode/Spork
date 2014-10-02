<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Authentication\Adapter;

use Zend\Authentication\Result;
use Zend\Authentication\Adapter\AdapterInterface;

/**
 * Authenticate an identity without testing a credential. Use cases are to
 * directly authenticate a member when they register or during unit testing. 
 */
class Dummy implements AdapterInterface
{
    /**
     * Identity to authenticate
     * @var mixed
     */
    protected $identity;
    
    /**
     * Initialize instance
     * @param string $identity
     */
    public function __construct($identity = null)
    {
        if (null !== $identity) {
            $this->setIdentity($identity);
        }
    }
    
    /**
     * Set identity to authenticate
     * @param mixed $identity
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }
    
    /**
     * Authenticate identity
     * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
     * @return \Zend\Authentication\Result
     */
    public function authenticate()
    {
        return new Result(Result::SUCCESS, $this->identity);
    }
}