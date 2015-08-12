<?php
namespace Spork\Exception;

class InvalidTypeException extends \Exception
{
    public function __construct($variable, $expected = null, $code = null, $previous = null)
    {
        $type = is_object($variable) ? get_class($variable) : gettype($variable);
        $message = "Invalid type $type" . (null == $expected ? '.' : "; expected $expected."); 
        parent::__construct($message, $code, $previous);
    }
}