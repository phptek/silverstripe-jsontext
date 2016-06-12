# SilverStripe JSONText

[![Build Status](https://api.travis-ci.org/phptek/silverstripe-jsontext.svg?branch=master)](https://travis-ci.org/phptek/silverstripe-jsontext)

This module does pretty much does what it says on the tin: Provides a simple Text field into 
which JSON can be stored and from which it can be queried.

Once stored, the module exposes a simple query API based on the [JSON operators found in Postgres v9.2+](https://www.postgresql.org/docs/9.5/static/functions-json.html),
but with some modifications:

In Postgres both the `->` and `->>` operators act as a string and integer key matcherx on a JSON source as array or object. The module
however treats both source types the same - they are after all *both* JSON so `->` is used as an **Integer Matcher** and `->>` as a string matcher.

In Postgress the `#>` patch match operator can act as an object or text matcher, but again, the module wishes to simplify things and as such
the `#>` operator is *just a simple path matcher*.

I see nothing but confusion if the same operator were to be treated differently
depending on the format of the source data.

Note: This module's query API is based on a relatively simple JSON to array conversion principle. 
It does *not* use Postgres' or MySQL's native JSON operators. The aim however 
is to allow dev's to use their preferred DB's syntax, and to this end you can set
the module into `mysql` or `postgres` mode using SS config:

```yml
JSONText:
  backend: mysql
```


Note: The module default is to use `postgres` which is also the only backend that will work.

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

The module can be put into `mysql` or `postgres` query mode using SS config thus:

```yml
JSONText:
  backend: mysql
```

You can also stipulate what format you want your results back as; either JSON or Array, thus:

```php

        // JSON
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue('{"a": {"b":{"c": "foo"}}}');
        $field->setReturnType('json');
        
        // Array
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue('{"a": {"b":{"c": "foo"}}}');
        $field->setReturnType('array');

```

In the examples below, if you pass invalid queries or malformed JSON (where applicable) an exception is thrown.

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
        
        /**
         * Returns a value based on a strict string/int match of the key-as-array
         * Given source JSON ala: '{"a": {"b":{"c": "foo"}}}' will return '{"c": "foo"}'
         */
        public function getByPathMatch('{"a":"b"}')
        {
            return $this->dbObject('MyJSON')->query('#>', '{"a":"b"}'; 
        }
    }
```
    
# TODO

* Lose the fugly way that data is queried via `$this->dbObject()`

# Author

Russell Michell 2016 <russ@theruss.com>
