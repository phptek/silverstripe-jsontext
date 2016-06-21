# Usage

You can stipulate what format you want your query results back as via passing one of **json** or **array** to `setReturnType()`, thus:

    // JSON
    $field = JSONText\Fields\JSONText::create('MyJSON');
    $field->setValue('{"a": {"b":{"c": "foo"}}}');
    $field->setReturnType('json');
    
    // Array
    $field = JSONText\Fields\JSONText::create('MyJSON');
    $field->setValue('{"a": {"b":{"c": "foo"}}}');
    $field->setReturnType('array');

In the examples below, if you pass invalid queries or malformed JSON (where applicable) an instnce of `JSONTextException` is thrown.

    class MyDataObject extends DataObject
    {
    
        private static $db = [
            'MyJSON'    => 'JSONText'
        ];
    
        /*
         * Returns the first key=>value pair found in the source JSON
         */
        public function getFirstJSONVal()
        {
            return $this->dbObject('MyJSON')->first();
        }
    
        /*
         * Returns the last key=>value pair found in the source JSON
         */
        public function getLastJSONVal()
        {
            return $this->dbObject('MyJSON')->last();
        }
    
        /*
         * Returns the Nth key=>value pair found in the source JSON (Top-level only)
         * For nested hashes use the int matcher ("->") or string matcher ("->>").
         */
        public function getNthJSONVal($n)
        {
            return $this->dbObject('MyJSON')->nth($n);
        }
        
        /**
         * Returns a key=>value pair based on a strict integer -> key match.
         * If a string is passed, an empty array is returned.
         */
        public function getNestedByIntKey($int)
        {
            return $this->dbObject('MyJSON')->query('->', $int);
        }
        
        /**
         * Returns a key=>value pair based on a strict string -> key match.
         * If an integer is passed, an empty array is returned.
         */
        public function getNestedByStrKey($str)
        {
            return $this->dbObject('MyJSON')->query('->>', $str);
        }
        
        /**
         * Returns a value based on a strict string/int match of the key-as-array
         * Given source JSON ala: '{"a": {"b":{"c": "foo"}}}' will return '{"c": "foo"}'
         */
        public function getByPathMatch('{"a":"b"}')
        {
            return $this->dbObject('MyJSON')->query('#>', '{"a":"b"}'; 
        }
        
    }
