# SilverStripe JSONText

Pretty much does what it says on the tin: Provides a simple TEXT field into which JSON can be stored.
This allows basic updates and "queries" to performed on the JSON data itself.

# Usage

    class MyDataObject extends DataObject
    {
        private static $db = [
            'MyJSON'    => 'SimpleJSONText'
        ];
 
        public function getMyJSON($key)
        {
            return $this->dbObject('MyJSON')->getValueForKey($key);
        }
        
        public function getFirstJSONVal()
        {
            return $this->dbObject('MyJSON')->first();
        }
        
        public function getLastJSONVal()
        {
            return $this->dbObject('MyJSON')->last();
        }
        
        public function getNthJSONVal($n)
        {
            return $this->dbObject('MyJSON')->nth($n);
        }
    }
    
# TODO

* Lose the fugly way that data is queried via `$this->dbObject()`
* Add basic set of tests for `first()`, `last()` etc
* Add a new field class that allows for deeper ORM interactions on JSON data

# Author

Russell Michell <russ@theruss.com>
