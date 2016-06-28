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
}
