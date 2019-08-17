# JSONPath

* All the information on querying your JSON using JSONPath you can eat [can be found here](http://goessner.net/articles/JsonPath/).
* For manipulating JSON on the command line, the excellent [jq](https://stedolan.github.io/jq/) tool is a great addition to your toolbox.

## Quick Reference

| XPath     | JSONPath           | Description                 |
|-----------|--------------------|-----------------------------|
| /         | $                  | The root object/element     |
| .         | @                  | The current object/element  |
| /         | . or []            | Child operator              |
| ..        | N/A                | Parent operator             |
| //        | ..                 | Recursive descent           |
| *         | *                  | Wildcard                    |
| @         | N/A                | Attribute access            |
| []        | []                 | Subscript operator          |
| \|        | [,]                | Union operator              |
| N/A       | [start:end:step]   | Array slice operator        |
| []        | ?()                | Applies a filter (script) expression |
| N/A       | ()                 | Script expression           |
| ()        | N/A                | Grouping in Xpath           |
|-----------|--------------------|-----------------------------|
