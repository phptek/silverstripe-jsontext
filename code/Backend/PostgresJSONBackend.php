<?php

/**
 * JSONText database backend that encapsulates a Postgres-like syntax for JSON querying.
 * 
 * @package silverstripe-jsontext
 * @author Russell Michell 2016-2019 <russ@theruss.com>
 * @see https://www.postgresql.org/docs/9.6/static/functions-json.html
 */

namespace PhpTek\JSONText\Backend;

use PhpTek\JSONText\Exception\JSONTextInvalidArgsException;

class PostgresJSONBackend extends JSONBackend
{
    /**
     * An array of acceptable operators for this backend.
     * 
     * @var array
     * @config
     */
    private static $allowed_operators = [
        'matchOnInt'    => '->',
        'matchOnStr'    => '->>',
        'matchOnPath'   => '#>'
    ];
    
    /**
     * @inheritdoc
     */
    public function matchOnInt()
    {
        if (!\is_int($this->operand)) {
            $msg = 'Non-integer passed to: ' . __FUNCTION__ . '()';
            throw new JSONTextInvalidArgsException($msg);
        }
        
        $expr = '$.[' . $this->operand . ']';
        $fetch = $this->jsonText->getJSONStore()->get($expr);
        $vals = \array_values($fetch);
        
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
        if (!\is_string($this->operand)) {
            $msg = 'Non-string passed to: ' . __FUNCTION__ . '()';
            throw new JSONTextInvalidArgsException($msg);
        }
        
        $expr = '$..' . $this->operand;
        $fetch = $this->jsonText->getJSONStore()->get($expr);
        $vals = \array_values($fetch);

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
        if (!\is_string($this->operand) || !$this->jsonText->isValidJson($this->operand)) {
            $msg = 'Invalid JSON passed as operand on RHS.';
            throw new JSONTextInvalidArgsException($msg);
        }
        
        $operandAsArray = $this->jsonText->toArray($this->operand);
        
        if (!\count($operandAsArray)) {
            return [];
        }

        $keys = \array_keys($operandAsArray);
        $vals = \array_values($operandAsArray);
        
        if (\count($keys) > 1 || \count($vals) > 1) {
            $msg = 'Sorry. I can\'t handle complex operands.';
            throw new JSONTextInvalidArgsException($msg);
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
