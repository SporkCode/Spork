<?php
/**
 * 
 * Spork Zend Framework 2 Library
 * 
 * @author Chris Schreiber <chris@sporkcode.com>
 */
namespace Spork\View\Model;

use Zend\View\Model\JsonModel;
use Zend\InputFilter\InputFilterInterface;

/**
 * View Model which converts and Input Filter into a JSON response.
 * 
 * {
 *     isValid: BOOLEAN,
 *     inputs: {
 *         INPUT_NAME: {
 *             value: MIXED,
 *             message: {MESSAGE_TYPE: MESSAGE, ...},
 *         },
 *         ...
 *     }
 * }
 */
class InputFilter extends JsonModel
{
    /**
     * Initialize instance
     * 
     * @param InputFilterInterface $inputFilter
     * @param string $variables
     * @param string $options
     */
    public function __construct(InputFilterInterface $inputFilter, $variables = null, $options = null)
    {
        parent::__construct($variables, $options);
        
        $this->setVariable('isValid', $inputFilter->isValid());
        $messages = $inputFilter->getMessages();
        $inputs = array();
        foreach ($inputFilter->getValues() as $name => $value) {
            $inputs[$name] = array(
                'value' => $value,
                'messages' => array_key_exists($name, $messages) ? $messages[$name] : array(),
            );
        }
        $this->setVariable('inputs', $inputs);
    }
}