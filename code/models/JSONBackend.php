<?php

/**
 * DB backend for use with the {@link JSONText} DB field. Allows us to use DB-specific JSON query syntax within
 * the module.
 * 
 * @package silverstripe-jsontext
 * @subpackage models
 * @author Russell Michell <russ@theruss.com>
 */

namespace JSONText\Backends;

use JSONText\Exceptions\JSONTextException;
use JSONText\Fields\JSONText;

class JSONBackend
{
    /**
     * @var mixed
     */
    protected $key;

    /**
     * @var mixed
     */
    protected $val;

    /**
     * @var int
     */
    protected $idx;
    
    /**
     * @var string
     */
    protected $operand;

    /**
     * @var JSONText
     */
    protected $jsonText;

    /**
     * JSONBackend constructor.
     * 
     * @param mixed $key
     * @param mixed $val
     * @param int $idx
     * @param string $operand
     * @param JSONText $jsonText
     */
    public function __construct($key, $val, $idx, $operand, $jsonText)
    {
        $this->key = $key;
        $this->val = $val;
        $this->idx = $idx;
        $this->operand = $operand;
        $this->jsonText = $jsonText;
    }
    
    /**
     * Match on keys by INT.
     *
     * @return array
     */
    public function matchIfKeyIsInt()
    {
        if (is_int($this->operand) && $this->idx === $this->operand) {
            return [$this->key => $this->val];
        }
        
        return [];
    }

    /**
     * Match on keys by STRING.
     *
     * @return array
     */
    public function matchIfKeyIsStr()
    {
        // operand can be a numeric string if it wants here
        if (is_string($this->operand) && $this->key === $this->operand) {
            return [$this->key => $this->val];
        }

        return [];
    }

    /**
     * Match on path. If >1 matches are found, an indexed array of all matches is returned.
     *
     * @return array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function matchOnPath()
    {
        if (!is_string($this->operand) || !$this->jsonText->isJson($this->operand)) {
            $msg = 'Invalid JSON passed as operand on RHS.';
            throw new JSONTextException($msg);
        }
        
        $operandAsArray = $this->jsonText->toArray($this->operand);
        
        // Empty is OK..could've been accidental...
        if (!count($operandAsArray)) {
            return [];
        }

        $keys = array_keys($operandAsArray);
        if (count($keys) >1) {
            $msg = 'Sorry. I can\'t handle complex operands.';
            throw new JSONTextException($msg);
        }

        $vals = array_values($operandAsArray);
        if (count($vals) >1) {
            $msg = 'Sorry. I can\'t handle complex operands.';
            throw new JSONTextException($msg);
        }
        
        // TODO Move this back to JSONText no use in iterating twice over source data 
        $data = [];
        foreach ($this->jsonText->getValueAsIterable() as $sourceKey => $sourceVal) {
            if ($keys[0] === $sourceKey && is_array($sourceVal) && !empty($sourceVal[$vals[0]])) {
                $data[] = $sourceVal[$vals[0]];
            }
        }

        if (count($data) === 1) {
            return $data[0];
        }

        return $data;
    }
    
}
