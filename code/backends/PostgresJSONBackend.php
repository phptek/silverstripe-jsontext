<?php

/**
 * DB backend for use with a Postgres RDBMS like JSON querying syntax.
 * 
 * @package silverstripe-jsontext
 * @subpackage models
 * @author Russell Michell <russ@theruss.com>
 * @see {@link JSONBackend}
 * @see https://github.com/Peekmo/JsonPath/blob/master/tests/JsonStoreTest.php
 * @see http://goessner.net/articles/JsonPath/
 */

namespace JSONText\Backends;

use JSONText\Exceptions\JSONTextException;
use JSONText\Fields\JSONText;

class PostgresJSONBackend extends JSONBackend
{
    /**
     * @inheritdoc
     */
    public function matchOnInt()
    {
        if (!is_int($this->operand)) {
            $msg = 'Non-integer passed to: ' . __FUNCTION__;
            throw new JSONTextException($msg);
        }
        
        $expr = '$.[' . $this->operand . ']';
        $fetch = $this->jsonText->getJSONStore()->get($expr);
        $vals = array_values($fetch);
        
        if (isset($vals[0])) {
            return [$this->operand => $vals[0]];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function matchOnStr()
    {
        if (!is_string($this->operand)) {
            $msg = 'Non-string passed to: ' . __FUNCTION__;
            throw new JSONTextException($msg);
        }
        
        $expr = '$..' . $this->operand;
        $fetch = $this->jsonText->getJSONStore()->get($expr);
        $vals = array_values($fetch);

        if (isset($vals[0])) {
            return [$this->operand => $vals[0]];
        }

        return [];
    }

    /**
     * @inheritdoc
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
        $vals = array_values($operandAsArray);
        if (count($keys) > 1 || count($vals) > 1) {
            $msg = 'Sorry. I can\'t handle complex operands.';
            throw new JSONTextException($msg);
        }

        $source = $this->jsonText->getStoreAsArray();
        $sourceAsIterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($source),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        $data = [];
        foreach ($sourceAsIterator as $sourceKey => $sourceVal) {
            if ($keys[0] === $sourceKey && is_array($sourceVal) && !empty($sourceVal[$vals[0]])) {
                $data[] = $sourceVal[$vals[0]];
            }
        }

        return $data;
    }
    
}
