# SilverStripe JSONText

[![Build Status](https://api.travis-ci.org/phptek/silverstripe-jsontext.svg?branch=master)](https://travis-ci.org/phptek/silverstripe-jsontext)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phptek/silverstripe-jsontext/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phptek/silverstripe-jsontext/?branch=master)
[![License](https://poser.pugx.org/phptek/jsontext/license.svg)](https://github.com/phptek/silverstripe-jsontext/blob/master/LICENSE.md)

Exposes a complete API that allows developers to write-to, and query-from JSON in a dedicated `DBField` subclass. 

In addition, if your project uses the `silverstripe/cms` package, then all `SiteTree` objects are automatically extended to allow multiple, arbitrary UI fields as declared in `getCMSFields()`, to write to a JSON object in a _single_ database field.

Using JSONPath (Think XPath but for JSON) and the module's extensive API, developers can selectively target specific JSON keys for modification.

## Requirements

### SilverStripe 4

* Use ^2.0
* PHP >=5.6, <=7.1

### SilverStripe 3

* Use ^1.0
* PHP >=5.4, <7.0

## Features

* Store JSON "object-strings" in a JSON-specific `DBField`  subclass.
* Query stored JSON via simple accessors: `first()`, `last()` & `nth()` or Postgres-like operators: `->`, `->>` & `#>` or even [JSONPath](http://goessner.net/articles/JsonPath/) expressions.
* Selectively return query-results as `JSON`, `Array` or cast to SilverStripe's `DBVarchar`, `DBInt`, `DBFloat` or `DBBoolean` objects.
* Selectively update portions of stored JSON using [JSONPath](http://goessner.net/articles/JsonPath/) expressions.
* Selectively transform one or more CMS input fields, to write to a single JSON store.

## Introduction

The module exposes a fully featured JSON query and update API allowing developers to use XPath-like queries via [JSONPath](http://goessner.net/articles/JsonPath/)
or [Postgres' JSON operators](https://www.postgresql.org/docs/9.5/static/functions-json.html) (with some differences, see below) to query and update JSON data.

### Why?

Project scenarios where storing 10s of terse configuration parameters as Booleans and Ints in separate database columns just seems crazy. 

When all you wanted was a simple key / value store but didn't want to muck about with the overhead of an RDBMS _and_ a NOSQL DB.

That Postgres, MySQL, Oracle and MSSQL 2016 all have, or at time of writing, are planning to have, Database level JSON field-types. This module plugs the gap for users of RDBMS'
_without_ native JSON support, while offering the a convenient scaffold on top of which native JSON support could be built.

### Postgres

In Postgres both the `->` and `->>` operators act as string and integer key matchers on a JSON array or JSON object respectively. The module
however treats both source types the same - they are after all *both JSON* so `->` is used as an **Integer Matcher** and `->>` as a **String Matcher**
*regardless* of the "type" of source JSON stored. The `#>` **Path Matcher** operator can act as an object or a text matcher, but the module wishes to simplify things and as such
the `#>` operator is *just a simple path matcher*.

### Return types

Regardless of the type of query you can set what type you'd like the data returned in via the `setReturnType()` method on a query by query basis. 

Legitimate types are:

* JSON
* Array
* SilverStripe

If using `SilverStripe` as the return type, the module will automatically cast the result(s) to one of SilverStripe's `DBObject` subtypes:

* `DBBoolean`
* `DBInt`
* `DBFloat`
* `DBVarchar`

If there are multiple results from a query, the output will be an indexed array containing a single-value array for each result found.

The module also allows developers to selectively *update* all, or just parts of the source JSON, via JSONPath expressions passed
to an overloaded `setValue()` method.

See [the usage docs](docs/en/usage.md) for examples of JSONPath and Postgres querying and updating.

Note: This module's query API is based on a relatively simple JSON to array conversion principle. 
It does *not* use Postgres' or MySQL's native JSON operators at or below the level of the ORM. The aim however 
is to allow dev's to use their preferred DB's syntax, and to this end you can set
the module into `mysql` or `postgres` mode using SS config, see [Configuration Docs](docs/en/configuration.md).

## Installation

    #> composer require phptek/jsontext

## Configuration

See: [Configuration Docs](docs/en/configuration.md).

## Usage

See: [Usage Docs](docs/en/usage.md). 

## Contributing

If you've been using Postgres or MySQL with its JSON functions for some time,
I'm keen to hear from you. Some simple failing tests would be most welcome.

See: [CONTRIBUTING.md](CONTRIBUTING.md).

## Reporting an issue

Please include all details, no matter how small. If it were *your module*, what would you need to know from a bug/feature request? :-)

## Credits

* [Axel Anceau](https://github.com/Peekmo/) for his packaging-up of the pretty amazing JSONPath implementation by [Stefan Goessner](https://code.google.com/archive/p/jsonpath/)
* [Stefan Goessner](https://code.google.com/archive/p/jsonpath/) for the original work on JSONPath dating back to 2005!

## Author

Russell Michell 2016-2018 <russ@theruss.com>

## TODO

* Add missing `prepValueForDB()` to `JSONText` class.
* See official list of issues on GitHub. 

## Support Me

If you like what you see, support me! I accept Bitcoin:

<table border="0">
	<tr>
		<td rowspan="2">
			<img src="https://bitcoin.org/img/icons/logo_ios.png" alt="Bitcoin" width="64" height="64" />
		</td>
	</tr>
	<tr>
		<td>
			<b>bc1qmg0jjtmu3fmm53mkvw69xz8kerq3l3lnh6529d</b>
		</td>
	</tr>
</table>

<p>&nbsp;</p>
<p>&nbsp;</p>
