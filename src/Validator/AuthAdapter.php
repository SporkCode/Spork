<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\Validator;

use Zend\Authentication\Adapter\AbstractAdapter as AuthAbstractAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result as AuthResult;
use Zend\Validator\AbstractValidator;

/**
 * Uses an authentication adapter to validate identity and credential values.
 */
class AuthAdapter extends AbstractValidator
{
    const IDENTITY_EMPTY = 'identityEmpty';
    
    const CREDENTIAL_EMPTY = 'credentialEmpty';
    
    /**
     * Authentication service
     * 
     * @var \Zend\Authentication\AuthenticationService
     */
    protected $auth;
    
    /**
     * Authentication result
     * 
     * @var \Zend\Authentication\Result
     */
    protected $authResult;
    
    /**
     * Name of identity field
     * 
     * @var string
     */
    protected $identity = 'identity';
    
    /**
     * Name of credential field
     * 
     * @var string
     */
    protected $credential = 'credential';
    
    /**
     * List of validator message templates
     * 
     * @var array
     */
    protected $messageTemplates = array(
        AuthResult::FAILURE => 'Authentication failed',
        AuthResult::FAILURE_CREDENTIAL_INVALID => 'Supplied credential is invalid.',
        AuthResult::FAILURE_IDENTITY_AMBIGUOUS => 'Supplied identity is ambiguous',
        AuthResult::FAILURE_IDENTITY_NOT_FOUND => 'Supplied identity could not be found',
        AuthResult::FAILURE_UNCATEGORIZED => 'Authentication failed for an unknown reason',
        self::IDENTITY_EMPTY => 'You must provide an identity',
        self::CREDENTIAL_EMPTY => 'You must provide a credential',
    );
    
    /**
     * Initialize validator
     * 
     * @param AuthenticationService $auth
     * @param string $options
     * @throws \Exception on authentication service does not have adapter
     */
    public function __construct(AuthenticationService $auth, $options = null)
    {
        parent::__construct($options);
        
        $authAdapter = $auth->getAdapter();
        if (null === $authAdapter) {
            throw new \Exception('Authentication Adapter not set in Authentication Service');
        }
        
        if (!$authAdapter instanceof AuthAbstractAdapter
                && (!method_exists($authAdapter, 'setIdentity')
                    || !method_exists($authAdapter, 'setCredential'))) {
            throw new \Exception('Authentication adapter must implement setIdentity() and setCredential()');
        }
        
        $this->auth = $auth;
    }
    
    /**
     * Set identity field name
     * 
     * @param string $name
     */
    public function setIdentity($name)
    {
        $this->identity = $name;
    }
    
    /**
     * Get identity field name
     * 
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }
    
    /**
     * Set credential field name
     * 
     * @param string $name
     */
    public function setCredential($name)
    {
        $this->credential = $name;
    }
    
    /**
     * Get credential field name
     * 
     * @return string
     */
    public function getCredential()
    {
        return $this->credential;
    }
    
    /**
     * Get the authentication result
     * 
     * @return \Zend\Authentication\Result
     */
    public function getAuthResult()
    {
        return $this->authResult;
    }
    
    /**
     * Test if authentication is valid
     * 
     * @see \Zend\Validator\ValidatorInterface::isValid()
     * @param mixed $value
     * @param array $context
     * @return boolean
     */
    public function isValid($value, array $context = null)
    {
        $context = (array) $context;

         if (!array_key_exists($this->identity, $context) || empty($context[$this->identity])) {
//             $this->error(self::IDENTITY_EMPTY);
             return false;
         }
        
         if (!array_key_exists($this->credential, $context) || empty($context[$this->credential])) {
//             $this->error(self::CREDENTIAL_EMPTY);
             return false;
         }
        
        $authAdapter = $this->auth->getAdapter();
        $authAdapter->setIdentity($context[$this->identity]);
        $authAdapter->setCredential($context[$this->credential]);
        
        $result = $this->authResult = $this->auth->authenticate();
        
        if (!$result->isValid()) {
            $this->error($result->getCode());
            return false;
        }
        
        return true;
    }
}