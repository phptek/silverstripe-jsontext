<?php

/**
 * JSON backend for Postgres using the {@link JSONText} DB field. Allows us to use Postgres JSON query syntax within
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
     * Not used.
     * 
     * @var string
     */
    protected $operator;

    /**
     * @var JSONText
     */
    protected $jsonText;

    /**
     * PostgresJSONBackend constructor.
     * 
     * @param mixed $key
     * @param mixed $val
     * @param int $idx
     * @param string $operand
     * @param JSONText $jsonText
     */
    public function __construct($key, $val, $idx, $operator, $operand, $jsonText)
    {
        $this->key = $key;
        $this->val = $val;
        $this->idx = $idx;
        $this->operator = $operator;
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
     * Match on path.
     *
     * @return array
     * @throws \JSONText\Exceptions\JSONTextException
     * @todo Naively only returns the first match. But what about where source JSON has legit duplicate keys? We need
     * to return an array of matches..
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
        
        if ($this->key === $keys[0] && is_array($this->val) && !empty($this->val[$vals[0]])) {
            return $this->val[$vals[0]];
        }

/*        if ($this->key === $keys[0] && is_array($this->val) && !empty($this->val[$vals[0]])) {
            $this->jsonText->updateCache($this->val[$vals[0]]);
        }*/
        
/*        if (count($this->jsonText->cache) === 1) {
            return $this->jsonText->cache[0];
        }*/
        
     //   return $this->jsonText->cache;
        
        return [];
    }
    
}
