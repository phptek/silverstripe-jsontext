<?php

/**
 * Simple text-based database field for storing and querying JSON formatted data. The field is "simple" in that it does not 
 * allow for multidimensional data.
 * 
 * Note: All getXX(), first(). nth() and last() methods will return `null` if no result is found. This behaviour 
 * may change in future versions, but will likely be governed by config settings.
 *
 * Example definition via {@link DataObject::$db}:
 * 
 * <code>
 * static $db = array(
 * 	"MyJSONStructure" => "SimpleJSONText",
 * );
 * </code>
 * 
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 * @todo Create a method asJSON() that converts scalar function output to valid JSON
 * ....run isJSON() over the result and throw an exception if that check fails
 */
class JSONText extends StringField
{
    /**
     * @var array
     * 
     * internal-name => operator (for use in extract() method).
     */
    private static $operators = [
        'get_element' => '->'  // Strict: Search source JSON for matching key of given type.
    ];

    /**
     * @var string
     */
    protected $returnType = 'json';
    
    /**
     * Returns an input field.
     *
     * @param string $name
     * @param null|string $title
     * @param string $value
     */
    public function __construct($name, $title = null, $value = '')
    {
        parent::__construct($name, $title, $value);
    }

    /**
     * Tell all class methods to return data as JSON or JSON converted to an array.
     * 
     * @param string $type
     * @return JSONText
     * @throws JSONTextException
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
     * @param mixed $data
     * @return mixed
     */
    protected function returnAsType($data)
    {
        if (($this->getReturnType() === 'array') && is_array($data)) {
            return $data;
        }

        if (($this->getReturnType() === 'json') && $this->isJson($data)) {
            return $this->toJson($data);
        }
    }

    /**
     * Is the passed JSON operator valid?
     * 
     * @param string $operator
     * @return boolean
     */
    protected function isValidOperator($operator)
    {
        return $operator && in_array($operator, $this->config()->operators, true);
    }
    
    /**
     * Taken from {@link TextField}.
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

        DB::require_field($this->tableName, $this->name, $values, $this->default);
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
     * Returns the value of this field as an iterable.
     * 
     * @return RecursiveIteratorIterator
     * @throws JSONTextException
     * @todo Cache this to an object field for performance
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

        return new RecursiveIteratorIterator(
            new RecursiveArrayIterator(json_decode($json, true)),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    /**
     * @param mixed string|int
     * @return mixed
     */
    public function getValueForKey($key)
    {
        $currentData = $this->getValueAsIterable();
        if (isset($currentData[$key])) {
            return $currentData[$key];
        }

        return null;
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
     * Return an array of the JSON key + value represented as first JSON node. 
     *
     * @return array
     */
    public function first()
    {
        $data = $this->getValueAsIterable();
        
        if (!$data) {
            return null;
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
            return null;
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
     * @return mixed null|array
     * @throws JSONTextException
     */
    public function nth($n)
    {
        $data = $this->getValueAsIterable();

        if (!$data) {
            return null;
        }
        
        if (!is_int($n)) {
            $msg = 'Argument passed to ' . __FUNCTION__ . ' must be numeric.';
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
     * Return an array of the JSON key(s) + value(s) represented by $operator extracting relevant result in a JSON 
     * node's value.
     *
     * @param string $operator
     * @param string $operand
     * @return mixed null|array
     * @throws JSONTextException
     * @todo Move operator-specific logic to own methods
     */
    public function extract($operator, $operand)
    {
        $data = $this->getValueAsIterable();
        
        if (!$data) {
            return null;
        }
        
        if (!$this->isValidOperator($operator)) {
            $msg = 'JSON operator: ' . $operator . ' in invalid.';
            throw new JSONTextException($msg);
        }
        
        $i = 0;
        foreach ($data as $key => $val) {
            switch ($operator) {
                default:
                    // TODO: This case should become own protected method...
                case '->':
                    if (!is_int($operand)) {
                        $msg = 'Incorrect type used as operand with operator: ' . $operator;
                        throw new JSONTextException($msg);
                    }

                    if ($i === $operand) {
                        return $this->returnAsType([$key => $val]);
                    }
            }
            
            $i++;
        }
    }

    /**
     * Alias of self::extract().
     * 
     * @param string $path
     * @return mixed null|array
     * @throws JSONTextException
     */
    public function find($path)
    {
        return $this->extract($path);
    }
    
    /**
     * Converts special JSON characters in incoming data. Use the $invert param to convert strings coming back out.
     * 
     * @param string $value
     * @param boolean $invert 
     * @return string
     */
    public function jsonSafe($value, $invert = false)
    {
        $map = [
            '{' => '%7B',
            '}' => '%7D',
            '"' => '&quot;'
        ];
        
        if ($invert) {
            $map = array_flip($map);
        }
        
        return str_replace(array_keys($map), array_values($map), $value);
    }

}

/**
 * @package silverstripe-advancedcontent
 * @author Russell Michell 2016 <russ@theruss.com>
 */
class JSONTextException extends Exception
{
}
