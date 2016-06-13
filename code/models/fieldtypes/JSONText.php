<?php

/**
 * Simple text-based database field for storing and querying JSON structured data. 
 * 
 * JSON sub-structures can be queried in a variety of ways using special    operators who's syntax closely mimics those used
 * in native JSON queries in PostGreSQL v9.2+.
 * 
 * Note: The extraction techniques employed here are simple key / value comparisons. They do not use any native JSON
 * features of your project's underlying RDBMS, e.g. those found either in PostGreSQL >= v9.2 or MySQL >= v5.7. As such
 * any JSON queries you construct will never be as performant as a native implementation. 
 *
 * Example definition via {@link DataObject::$db} static:
 * 
 * <code>
 * static $db = [
 *  'MyJSONStructure' => 'JSONText'
 * ];
 * </code>
 * 
 * See the README for example queries.
 * 
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 * @todo Make the current default of "strict mode" into ss config and default to strict.
 */

namespace JSONText\Fields;

use JSONText\Exceptions\JSONTextException;

class JSONText extends \StringField
{
    /**
     * Which RDBMS backend are we using? The value set here changes the actual operators and operator-routines for the
     * given backend.
     * 
     * @var string
     * @config
     */
    private static $backend = 'postgres';
    
    /**
     * @var array
     * @config
     * 
     * [<backend>] => [
     *  [<method> => <operator>]
     * ]; // For use in query() method.
     */
    private static $allowed_operators = [
        'postgres' => [
            'matchIfKeyIsInt'   => '->',
            'matchIfKeyIsStr'   => '->>',
            'matchOnPath'       => '#>'
        ]
    ];

    /**
     * @var string
     */
    protected $returnType = 'json';

    /**
     * Object cache for performance improvements.
     * 
     * @var \RecursiveIteratorIterator
     */
    protected $data;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @param string $val
     * @return void
     */
    public function updateCache($val) {
        $this->cache[] = $val;
    }

    /**
     * Taken from {@link TextField}.
     * 
     * @see DBField::requireField()
     * @return void
     */
    public function requireField()
    {
        $parts = [
            'datatype'      => 'mediumtext',
            'character set' => 'utf8',
            'collate'       => 'utf8_general_ci',
            'arrayValue'    => $this->arrayValue
        ];

        $values = [
            'type'  => 'text',
            'parts' => $parts
        ];

        DB::require_field($this->tableName, $this->name, $values);
    }

    /**
     * @param string $title
     * @return HiddenField
     */
    public function scaffoldSearchField($title = null)
    {
        return HiddenField::create($this->getName());
    }

    /**
     * @param string $title
     * @return HiddenField
     */
    public function scaffoldFormField($title = null)
    {
        return HiddenField::create($this->getName());
    }

    /**
     * Tell all class methods to return data as JSON or an array.
     * 
     * @param string $type
     * @return \JSONText
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function setReturnType($type)
    {
        if (!in_array($type, ['json', 'array'])) {
            $msg = 'Bad type: ' . $type . ' passed to ' . __FUNCTION__;
            throw new JSONTextException($msg);
        }
        
        $this->returnType = $type;
    }

    /**
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * Returns the value of this field as an iterable.
     * 
     * @return mixed
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function getValueAsIterable()
    {
        if (!$json = $this->getValue()) {
            return [];
        }
        
        if (!$this->isJson($json)) {
            $msg = 'DB data is munged.';
            throw new JSONTextException($msg);
        }

        if (!$this->data) {
            $this->data = new \RecursiveIteratorIterator(
                new \RecursiveArrayIterator(json_decode($json, true)),
                \RecursiveIteratorIterator::SELF_FIRST
            );
        }
        
        return $this->data;
    }

    /**
     * Returns the value of this field as a flattened array
     *
     * @return array
     */
    public function getValueAsArray()
    {
        return iterator_to_array($this->getValueAsIterable());
    }

    /**
     * Utility method to determine whether the data is really JSON or not.
     * 
     * @param string $value
     * @return boolean
     */
    public function isJson($value)
    {
        return !is_null(json_decode($value, true));
    }

    /**
     * @param array $value
     * @return mixed null|string
     */
    public function toJson($value)
    {
        if (!is_array($value)) {
            $value = (array) $value;
        }
        
        $opts = (
            JSON_UNESCAPED_SLASHES
        );
        
        return json_encode($value, $opts);
    }

    /**
     * @param mixed $value
     * @return array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function toArray($value)
    {
        $decode = json_decode($value, true);
        
        if (is_null($decode)) {
            $msg = 'Decoded JSON is invalid.';
            throw new JSONTextException($msg);
        }
        
        return $decode;
    }
    
    /**
     * Return an array of the JSON key + value represented as first (top-level) JSON node. 
     *
     * @return array
     */
    public function first()
    {
        $data = $this->getValueAsIterable();
        
        if (!$data) {
            return $this->returnAsType([]);
        }

        $flattened = iterator_to_array($data, true);
        return $this->returnAsType([
                array_keys($flattened)[0] => array_values($flattened)[0]
            ]);
    }

    /**
     * Return an array of the JSON key + value represented as last JSON node.
     *
     * @return array
     */
    public function last()
    {
        $data = $this->getValueAsIterable();

        if (!$data) {
            return $this->returnAsType([]);
        }

        $flattened = iterator_to_array($data, true);
        return $this->returnAsType([
                array_keys($flattened)[count($flattened) -1] => array_values($flattened)[count($flattened) -1]
            ]);
    }

    /**
     * Return an array of the JSON key + value represented as the $n'th JSON node.
     *
     * @param int $n
     * @return mixed array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function nth($n)
    {
        $data = $this->getValueAsIterable();

        if (!$data) {
            return $this->returnAsType([]);
        }
        
        if (!is_int($n)) {
            $msg = 'Argument passed to ' . __FUNCTION__ . ' must be an integer.';
            throw new JSONTextException($msg);
        }

        $i = 0;
        foreach ($data as $key => $val) {
            if ($i === $n) {
                return $this->returnAsType([$key => $val]);
            }
            $i++;
        }
        
        return $this->returnAsType($data);
    }

    /**
     * Return the key(s) + value(s) represented by $operator extracting relevant result from the source JSON's structure.
     * N.b when using the path match operator '#>' with duplicate keys, an indexed array of results is returned.
     *
     * @param string $operator
     * @param string $operand
     * @return mixed null|array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function query($operator, $operand)
    {
        $data = $this->getValueAsIterable();
        
        if (!$data) {
            return $this->returnAsType([]);
        }
        
        if (!$this->isValidOperator($operator)) {
            $msg = 'JSON operator: ' . $operator . ' is invalid.';
            throw new JSONTextException($msg);
        }
        
        $i = 0;
        foreach ($data as $key => $val) {
            if ($marshalled = $this->marshallQuery($key, $val, $i, func_get_args())) {
                return $this->returnAsType($marshalled);
            }
            
            $i++;
        }

        return $this->returnAsType([]);
    }

    /**
     * Alias of self::query().
     * 
     * @param string $operator
     * @return mixed string|array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function extract($operator)
    {
        return $this->query($operator);
    }

    /**
     * Based on the passed operator, ensure the correct backend matcher method is called.
     * 
     * @param mixed $key
     * @param mixed $val
     * @param int $idx
     * @param array $args
     * @return array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    private function marshallQuery($key, $val, $idx, $args)
    {
        $backend = $this->config()->backend;
        $operator = $args[0];
        $operand = $args[1];
        $operators = $this->config()->allowed_operators[$backend];
        
        if (!in_array($operator, $operators)) {
            $msg = 'Invalid ' . $backend . ' operator: ' . $operator . ', used for JSON query.';
            throw new JSONTextException($msg);
        }
        
        foreach ($operators as $routine => $backendOperator) {
            $backendDBApiInst = \Injector::inst()->createWithArgs(
                '\JSONText\Backends\JSONBackend', [
                    $key, 
                    $val, 
                    $idx,
                    $operand,
                    $this
                ]);
            
            if ($operator === $backendOperator && $result = $backendDBApiInst->$routine()) {
               return $result;
            }
        }
        
        return [];
    }

    /**
     * Determine the desired userland format to return all query API method results in.
     * 
     * @param array $data
     * @return mixed
     */
    private function returnAsType(array $data)
    {
        if (($this->getReturnType() === 'array')) {
            return $data;
        }

        if (($this->getReturnType() === 'json')) {
            return $this->toJson($data);
        }
    }

    /**
     * Is the passed JSON operator valid?
     *
     * @param string $operator
     * @return boolean
     */
    private function isValidOperator($operator)
    {
        $backend = $this->config()->backend;

        return $operator && in_array($operator, $this->config()->allowed_operators[$backend], true);
    }

}

/**
 * @package silverstripe-jsontext
 * @author Russell Michell 2016 <russ@theruss.com>
 */

namespace JSONText\Exceptions;

class JSONTextException extends \Exception
{
}
