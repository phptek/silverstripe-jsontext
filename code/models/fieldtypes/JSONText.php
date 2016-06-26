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
 *  'MyJSONStructure' => '\JSONText\Fields\JSONText'
 * ];
 * </code>
 * 
 * See the README for example queries.
 * 
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */

namespace JSONText\Fields;

use JSONText\Exceptions\JSONTextException;
use JSONText\Backends;
use Peekmo\JsonPath\JsonStore;

class JSONText extends \StringField
{
    /**
     * @var int
     */
    const JSONTEXT_QUERY_OPERATOR = 1;

    /**
     * @var int
     */
    const JSONTEXT_QUERY_JSONPATH = 2;
    
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
     * Legitimate query return types.
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
        
        \DB::require_field($this->tableName, $this->name, $values);
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
     * @return JSONText
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
     * Returns the value of this field as an iterable.
     * 
     * @return \Peekmo\JsonPath\JsonStore
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function getJSONStore()
    {
        if (!$value = $this->getValue()) {
            return new JsonStore('[]');
        }
        
        if (!$this->isJson($value)) {
            $msg = 'DB data is munged.';
            throw new JSONTextException($msg);
        }
        
        $this->jsonStore = new JsonStore($value);
        
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
     * @return string null|string
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
        
        if (empty($data)) {
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

        if (empty($data)) {
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

        if (empty($data)) {
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
     * @param string $operator One of the legitimate operators for the current backend or a valid JSONPath expression.
     * @param string $operand
     * @return mixed null|array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    public function query($operator, $operand = null)
    {
        $data = $this->getStoreAsArray();

        if (empty($data)) {
            return $this->returnAsType([]);
        }

        $isOp = ($operand && $this->isOperator($operator));
        $isEx = (is_null($operand) && $this->isExpression($operator));
        
        if ($isOp && !$this->isValidOperator($operator)) {
            $msg = 'JSON operator: ' . $operator . ' is invalid.';
            throw new JSONTextException($msg);
        }

        if ($isEx && !$this->isValidExpression($operator)) {
            $msg = 'JSON expression: ' . $operator . ' is invalid.';
            throw new JSONTextException($msg);
        }

        $validType = ($isEx ? self::JSONTEXT_QUERY_JSONPATH : self::JSONTEXT_QUERY_OPERATOR);
        if ($marshalled = $this->marshallQuery(func_get_args(), $validType, $this->getJSONStore())) {
            return $this->returnAsType($marshalled);
        }

        return $this->returnAsType([]);
    }

    /**
     * Based on the passed operator or expression, it marshalls the correct backend matcher method into account.
     *
     * @param array $args
     * @param integer $type
     * @return array
     * @throws \JSONText\Exceptions\JSONTextException
     */
    private function marshallQuery($args, $type = 1)
    {
        $backend = $this->config()->backend;
        $operator = $expression = $args[0];
        $operand = isset($args[1]) ? $args[1] : null;
        $operators = $this->config()->allowed_operators[$backend];
        $operatorParamIsValid = $type === self::JSONTEXT_QUERY_OPERATOR;
        $expressionParamIsValid = $type === self::JSONTEXT_QUERY_JSONPATH;
        
        if ($operatorParamIsValid) {
            $dbBackendInst = $this->createBackendInst($operand);
            foreach ($operators as $routine => $backendOperator) {
                if ($operator === $backendOperator && $result = $dbBackendInst->$routine()) {
                    return $result;
                }
            }
        } else if($expressionParamIsValid) {
            $dbBackendInst = $this->createBackendInst($expression);
            if ($result = $dbBackendInst->matchOnExpr()) {
                return $result;
            }
        }
        
        return [];
    }

    /**
     * Same as standard setValue() method except we can also accept a JSONPath expression. This expression will
     * conditionally update the parts of the field's source JSON referenced by $expr with $value
     * then re-set the entire JSON string as the field's new value.
     * 
     * Note: The $expr parameter can only accept JSONPath expressions. Using Postgres operators will not work and will
     * throw an instance of JSONTextException.
     *
     * @param mixed $value
     * @param array $record
     * @param string $expr  A valid JSONPath expression.
     * @return JSONText
     * @throws JSONTextException
     */
    public function setValue($value, $record = null, $expr = '')
    {
        if (empty($expr)) {
            $this->value = $value;
        } else {
            if (!$this->isValidExpression($expr)) {
                $msg = 'Invalid JSONPath expression: ' . $expr . ' passed to ' . __FUNCTION__;
                throw new JSONTextException($msg);
            }
            
            if (!$this->getJSONStore()->set($expr, $value)) {
                $msg = 'Failed to properly set custom data to the JSONStore in ' . __FUNCTION__;
                throw new JSONTextException($msg);
            }

            $this->value = $this->jsonStore->toString();
        }

        // Deal with standard SS behaviour
        parent::setValue($this->value, $record);
        
        return $this;
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
        $type = $this->returnType;
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
     * Create an instance of {@link JSONBackend} according to the value of JSONText::backend defined in SS config.
     * 
     * @param string operand
     * @return JSONBackend
     * @throws JSONTextException
     */
    protected function createBackendInst($operand)
    {
        $backend = $this->config()->backend;
        $dbBackendClass = '\JSONText\Backends\\' . ucfirst($backend) . 'JSONBackend';
        
        if (!class_exists($dbBackendClass)) {
            $msg = 'JSONText backend class ' . $dbBackendClass . ' not found.';
            throw new JSONTextException($msg);
        }
        
        return \Injector::inst()->createWithArgs(
            $dbBackendClass, [
            $operand,
            $this
        ]);
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

        return $operator && in_array(
            $operator, 
            $this->config()->allowed_operators[$backend],
            true
        );
    }

    /**
     * @param string $arg
     * @return bool
     */
    private function isExpression($arg)
    {
        return (bool) preg_match("#^\\$\.#", $arg);
    }

    /**
     * @param string $arg
     * @return bool
     */
    public function isOperator($arg)
    {
        return !$this->isExpression($arg);
    }
    
    /**
     * Is the passed JSON expression valid?
     *
     * @param string $expr
     * @return boolean
     */
    public function isValidExpression($expr)
    {
        return (bool) preg_match("#^\\$\.#", $expr);
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
            // Default to just returning empty val (castToDBField() is used exclusively from within a loop)
            return $val;
        }
    }

}
