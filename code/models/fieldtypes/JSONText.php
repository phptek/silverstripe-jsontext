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
     * Returns the value of this field as an associative array.
     * 
     * @return array
     * @throws SimpleJSONException 
     */
    public function getValueAsArray()
    {
        if (!$value = $this->getValue()) {
            return [];
        }
        
        if (!$this->isJson($value)) {
            $msg = 'DB data is munged.';
            throw new SimpleJSONException($msg);
        }
        
        if (!$decoded = json_decode($value, true)) {
            return [];
        }

        if (!is_array($decoded)) {
            $decoded = (array) $decoded;
        }
        
        return $decoded;
    }

    /**
     * @param mixed string|int
     * @return mixed
     */
    public function getValueForKey($key)
    {
        $currentData = $this->getValueAsArray();
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
     * @return mixed null|array
     */
    public function first()
    {
        $data = $this->getValueAsArray();
        
        if (!$data) {
            return null;
        }
        
        $data = array_slice($data, 0, 1, true);

        return reset($data);
    }

    /**
     * Return an array of the JSON key + value represented as last JSON node.
     *
     * @return mixed null|array
     */
    public function last()
    {
        $data = $this->getValueAsArray();

        if (!$data) {
            return null;
        }

        $data = array_slice($data, -1, 1, true);
        
        return reset($data);
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
        $data = $this->getValueAsArray();

        if (!$data) {
            return null;
        }
        
        if (!is_numeric($n)) {
            $msg = 'Argument passed to ' . __FUNCTION__ . ' must be numeric.';
            throw new JSONTextException($msg);
        }
        
        if (!isset(array_values($data)[$n])) {
            return null;
        }

        $data = array_slice($data, $n, 1, true);
        
        if (empty($data)) {
            return null;
        }
        
        return reset($data);
    }

    /**
     * Return an array of the JSON key(s) + value(s) represented when $value is found in a JSON node's value
     *
     * @param string $value
     * @return mixed null|array
     * @throws JSONTextException
     * @todo Allow $value to be a PCRE
     */
    public function find($value)
    {
        $data = $this->getValueAsArray();
        
        if (!$data) {
            return null;
        }
        
        if (!is_scalar($value)) {
            $msg = 'Argument passed to ' . __FUNCTION__ . ' must be a scalar.';
            throw new JSONTextException($msg);
        }
        
        $found = null;
        array_walk($data, function($k, $v) use(&$found, $value) {
            if ($v == $value) {
                $found = [$k => $v];
            }
        });
        
        if (empty($found)) {
            return null;
        }
        
        return $found;
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
