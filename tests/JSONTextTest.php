<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */

use JSONText\Fields\JSONText;

class JSONTextTest extends SapphireTest
{
    /**
     * @todo There are a ton more permutations of a JSONPath regex
     * See the walk() method in JSONStore
     */
    public function testIsExpressionValid()
    {
        $field = JSONText::create('MyJSON');
        
        $this->assertTrue($field->isValidExpression('$..'));
        $this->assertTrue($field->isValidExpression('$.[2]'));
        $this->assertTrue($field->isValidExpression('$.cars.american[*]'));
        $this->assertFalse($field->isValidExpression('$'));
        $this->assertFalse($field->isValidExpression('$[2]'));
    }

    /**
     * Ordinarily we can just use !is_null(json_decode($json)) but SS allows empty strings passed to setValue() so we need
     * to allow otherwise invalid JSON by means of an optional 2nd param
     */
    public function testIsJson()
    {
        $field = JSONText::create('MyJSON');

        $this->assertFalse($field->isJson(''));
        $this->assertTrue($field->isJson('true'));
        $this->assertTrue($field->isJson('false'));
        $this->assertFalse($field->isJson('null'));
        $this->assertFalse($field->isJson("['one']"));
        $this->assertFalse($field->isJson('["one]'));
        $this->assertTrue($field->isJson('[]'));
        $this->assertTrue($field->isJson('["one"]'));
        $this->assertTrue($field->isJson('["one","two"]'));
        $this->assertTrue($field->isJson('{"cars":{"american":["buick","oldsmobile"]}}'));
        $this->assertTrue($field->isJson('', ['']));
    }
}
