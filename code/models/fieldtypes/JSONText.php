<?php

/**
 * Simple text-based database field for storing and querying JSON structured data. 
 * 
 * JSON sub-structures can be queried in a variety of ways using special operators who's syntax closely mimics those used
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
 * @todo Rename query() to getValue() that accepts optional param $expr (for JSONPath queries)
 * @todo Add tests for "minimal" yet valid JSON types e.g. `true`
 * @todo Incorporate Currency class in castToDBField()
 */

namespace JSONText\Fields;

use JSONText\Exceptions\JSONTextException;
use JSONText\Backends;
use Peekmo\JsonPath\JsonStore;

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
            'matchOnInt'    => '->',
            'matchOnStr'    => '->>',
            'matchOnPath'   => '#>'
        ]
    ];

    /**
     * Legitimate query return types
     * 
     * @var array
     */
    private static $return_types = [
        'json', 'array', 'silverstripe'
    ];

    /**
     * @var string
     */
    protected $returnType = 'json';

    /**
     * @var \Peekmo\JsonPath\JsonStore
     */
    protected $jsonStore;

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
     * Tell all class methods to return data as JSON , an array or an array of SilverStripe DBField subtypes.
     * 
     * @param string $type
     * @return \JSONText
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function setReturnType($type)
    {
        if (!in_array($type, $this->config()->return_types)) {
            $msg = 'Bad type: ' . $type . ' passed to ' . __FUNCTION__;
            throw new JSONTextException($msg);
        }
        
        $this->returnType = $type;
        
        return $this;
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
     * @return \Peekmo\JsonPath\JsonStore
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function getJSONStore()
    {
        if (!$json = $this->getValue()) {
            return [];
        }
        
        if (!$this->isJson($json)) {
            $msg = 'DB data is munged.';
            throw new JSONTextException($msg);
        }

        $this->jsonStore = new \Peekmo\JsonPath\JsonStore($json);
        
        return $this->jsonStore;
    }

    /**
     * Returns the JSON value of this field as an array.
     *
     * @return array
     */
    public function getStoreAsArray()
    {
        $store = $this->getJSONStore();
        if (!is_array($store)) {
            return $store->toArray();
        }
        
        return $store;
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
     * Convert an array to JSON via json_encode().
     * 
     * @param array $value
     * @return mixed null|string
     */
    public function toJson(array $value)
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
     * Convert an array's values into an array of SilverStripe DBField subtypes ala:
     * 
     * - {@link Int}
     * - {@link Float}
     * - {@link Boolean}
     * - {@link Varchar}
     * 
     * @param array $data
     * @return array
     */
    public function toSSTypes(array $data)
    {
        $newList = [];
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $newList[$key] = $this->toSSTypes($val);
            } else {
                $newList[$key] = $this->castToDBField($val);
            }
        }
        
        return $newList;
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
        $data = $this->getStoreAsArray();
        
        if (!$data) {
            return $this->returnAsType([]);
        }

        $key = array_keys($data)[0];
        $val = array_values($data)[0];

        return $this->returnAsType([$key => $val]);
    }

    /**
     * Return an array of the JSON key + value represented as last JSON node.
     *
     * @return array
     */
    public function last()
    {
        $data = $this->getStoreAsArray();

        if (!$data) {
            return $this->returnAsType([]);
        }

        $count = count($data) -1;
        $key = array_keys($data)[$count];
        $val = array_values($data)[$count];

        return $this->returnAsType([$key => $val]);
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
        $data = $this->getStoreAsArray();

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
        $data = $this->getStoreAsArray();
        
        if (!$data) {
            return $this->returnAsType([]);
        }
        
        if (!$this->isValidOperator($operator)) {
            $msg = 'JSON operator: ' . $operator . ' is invalid.';
            throw new JSONTextException($msg);
        }

        if ($marshalled = $this->marshallQuery(func_get_args())) {
            return $this->returnAsType($marshalled);
        }

        return $this->returnAsType([]);
    }

    /**
     * Based on the passed operator, ensure the correct backend matcher method is called.
     *
     * @param array $args
     * @return array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    private function marshallQuery($args)
    {
        $backend = $this->config()->backend;
        $operator = $args[0];
        $operand = $args[1];
        $operators = $this->config()->allowed_operators[$backend];
        $dbBackend = ucfirst($backend) . 'JSONBackend';
        
        if (!in_array($operator, $operators)) {
            $msg = 'Invalid ' . $backend . ' operator: ' . $operator . ', used for JSON query.';
            throw new JSONTextException($msg);
        }
        
        foreach ($operators as $routine => $backendOperator) {
            $backendDBApiInst = \Injector::inst()->createWithArgs(
                '\JSONText\Backends\\' . $dbBackend, [
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
     * @param mixed
     * @return mixed
     * @throws \JSONText\Exceptions\JSONTextException
     */
    private function returnAsType($data)
    {
        $data = (array) $data;
        $type = $this->getReturnType();
        if ($type === 'array') {
            if (!count($data)) {
                return [];
            }
            
            return $data;
        }

        if ($type === 'json') {
            if (!count($data)) {
                return '[]';
            }
            
            return $this->toJson($data);
        }

        if ($type === 'silverstripe') {
            if (!count($data)) {
                return null;
            }
            
            return $this->toSSTypes($data);
        }
        
        $msg = 'Bad argument passed to ' . __FUNCTION__;
        throw new JSONTextException($msg);
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
    
    /**
     * Casts a value to a {@link DBField} subclass.
     * 
     * @param mixed $val
     * @return mixed DBField|array
     */
    private function castToDBField($val)
    {
        if (is_float($val)) {
            return \DBField::create_field('Float', $val);
        } else if (is_bool($val)) {
            $value = ($val === true ? 1 : 0); // *mutter....*
            return \DBField::create_field('Boolean', $value);
        } else if (is_int($val)) {
            return \DBField::create_field('Int', $val);
        } else if (is_string($val)) {
            return \DBField::create_field('Varchar', $val);
        } else {
            // Default to just returnign empty val (castToDBField() is used exclusively from within a loop)
            return $val;
        }
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
