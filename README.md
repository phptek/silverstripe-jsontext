# SilverStripe JSONText

This does pretty much does what it says on the tin: Provides a simple Text field into 
which JSON can be stored. 

Once stored, the module exposes a simple query API based on the [JSON operators found in Postgres v9.2+](https://www.postgresql.org/docs/9.5/static/functions-json.html).

Note: This module's query API is based on a relatively simple JSON to array conversion principle. 
It does *not* natively use Postgres' or MySQL's native JSON operators. The aim however 
is to allow dev's to use their preferred syntax, and to this end, you can set
the module into `mysql' or `postgres` mode using SS config:

```yml
JSONText:
  backend: mysql
```


Note: The default is to use `postgres`.

# Stability

This is currently *alpha software*. At time of writing (June 2016) there is
only partial support for the `->` and `->>` operators and although well-tested, 
they are far from complete (or even correct).

This leads me to..

# Contributing

If you've been using Postgres or MySQL with its JSON functions for some time,
I'm keen to hear from you. Some simple failing tests would be most welcome.

See: [CONTRIBUTING.md](CONTRIBUTING.md)

# Usage

```php
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
    }
```
    
# TODO

* Lose the fugly way that data is queried via `$this->dbObject()`

# Author

Russell Michell 2016 <russ@theruss.com>
