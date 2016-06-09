<?php

/**
 * JSON backend for Postgres using the {@link JSONText} DB field. Allows us to use Postgres JSON query syntax within
 * the module.
 * 
 * @package silverstripe-jsontext
 * @subpackage models
 * @author Russell Michell <russ@theruss.com>
 */
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
     * PostgresJSONBackend constructor.
     * 
     * @param mixed $key
     * @param mixed $val
     * @param int $idx
     * @param string $operand
     */
    public function __construct($key, $val, $idx, $operator, $operand)
    {
        $this->key = $key;
        $this->val = $val;
        $this->idx = $idx;
        $this->operator = $operator;
        $this->operand = $operand;
    }
    
    /**
     * Generic RDBMS agnostic integer based matcher.
     * 
     * @return array
     */
    public function intKeyMatcher()
    {
        if (is_int($this->operand) && $this->idx === $this->operand) {
            return [$this->key => $this->val];
        }
        
        return [];
    }

    /**
     * Generic RDBMS agnostic string based matcher.
     * 
     * @return array
     */
    public function strKeyMatcher()
    {
        if (is_string($this->operand) && $this->key === $this->operand) {
            return [$this->key => $this->val];
        }

        return [];
    }
    
}
