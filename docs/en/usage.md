# Usage

The module can be put into `mysql` or `postgres` query mode using SS config thus:


    JSONText:
      backend: mysql

You can also stipulate what format you want your query results back as; JSON or Array, thus:

    // JSON
    $field = JSONText\Fields\JSONText::create('MyJSON');
    $field->setValue('{"a": {"b":{"c": "foo"}}}');
    $field->setReturnType('json');
    
    // Array
    $field = JSONText\Fields\JSONText::create('MyJSON');
    $field->setValue('{"a": {"b":{"c": "foo"}}}');
    $field->setReturnType('array');

## Examples

In the examples below, if you pass invalid queries or malformed JSON (where applicable) an exception is thrown.


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
