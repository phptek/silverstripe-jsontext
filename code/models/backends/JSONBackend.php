<?php

/**
 * DB backend for use with the {@link JSONText} DB field. Allows us to use DB-specific JSON query syntax within
 * the module.
 * 
 * @package silverstripe-jsontext
 * @subpackage models
 * @author Russell Michell <russ@theruss.com>
 * @see https://github.com/Peekmo/JsonPath/blob/master/tests/JsonStoreTest.php
 * @see http://goessner.net/articles/JsonPath/
 */

namespace JSONText\Backends;

use JSONText\Exceptions\JSONTextException;
use JSONText\Fields\JSONText;

abstract class JSONBackend
{
    
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
     * @param string $operand
     * @param JSONText $jsonText
     */
    public function __construct($operand, $jsonText)
    {
        $this->operand = $operand;
        $this->jsonText = $jsonText;
    }
    
    /**
     * Match on keys by INT.
     *
     * @return array
     * @throws JSONTextException
     */
    abstract public function matchOnInt();

    /**
     * Match on keys by STRING.
     *
     * @return array
     * @throws JSONTextException
     */
    abstract function matchOnStr();

    /**
     * Match on path. If >1 matches are found, an indexed array of all matches is returned.
     *
     * @return array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    abstract public function matchOnPath();
    
}
