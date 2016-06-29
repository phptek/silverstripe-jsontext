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
    public function testIsValidExpression()
    {
        $field = JSONText::create('MyJSON');
        
        $this->assertTrue($field->isValidExpression('$..'));
        $this->assertTrue($field->isValidExpression('$.[2]'));
        $this->assertTrue($field->isValidExpression('$.cars.american[*]'));
        $this->assertFalse($field->isValidExpression('$'));
        $this->assertFalse($field->isValidExpression('$[2]'));
    }

    /**
     * @return void
     */
    public function testIsValidJson()
    {
        $field = JSONText::create('MyJSON');

        $this->assertFalse($field->isValidJson(''));
        $this->assertTrue($field->isValidJson('true'));
        $this->assertTrue($field->isValidJson('false'));
        $this->assertFalse($field->isValidJson('null'));
        $this->assertFalse($field->isValidJson("['one']"));
        $this->assertFalse($field->isValidJson('["one]'));
        $this->assertFalse($field->isValidJson('{{{'));
        $this->assertTrue($field->isValidJson('[]'));
        $this->assertTrue($field->isValidJson('["one"]'));
        $this->assertTrue($field->isValidJson('["one","two"]'));
        $this->assertTrue($field->isValidJson('{"cars":{"american":["buick","oldsmobile"]}}'));
    }


    /**
     * Ordinarily we can just use !is_null(json_decode($json)) but SS allows empty strings passed to setValue() so we need
     * to allow otherwise invalid JSON by means of an optional 2nd param
     *
     * @return void
     */
    public function testIsValidDBValue()
    {
        $field = JSONText::create('MyJSON');
        
        $this->assertFalse($field->isValidDBValue('true'));
        $this->assertFalse($field->isValidDBValue('false'));
        $this->assertFalse($field->isValidDBValue('null'));
        $this->assertTrue($field->isValidDBValue(''));
        $this->assertTrue($field->isValidJson('["one","two"]'));
        $this->assertTrue($field->isValidJson('{"cars":{"american":["buick","oldsmobile"]}}'));
    }
}
