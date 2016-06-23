<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */

use JSONText\Fields;
use JSONText\Exceptions;

class JSONTextSetValueTest extends SapphireTest
{
    /**
     * @var array
     */
    protected $fixtures = [
        'array'     => 'tests/fixtures/json/array.json',
        'object'    => 'tests/fixtures/json/object.json',
        'invalid'   => 'tests/fixtures/json/invalid.json'
    ];

    /**
     * JSONTextTest constructor.
     * 
     * Modify fixtures property to be able to run on PHP <5.6 without use of constant in class property which 5.6+ allows
     */
    public function __construct()
    {
        foreach($this->fixtures as $name => $path) {
            $this->fixtures[$name] = MODULE_DIR . '/' . $path;
        }
    }

    /**
     * Tests JSONText::setValue() by means of a simple JSONPath expression operating on a JSON array
     */
    public function testSetValueOnSourceArray()
    {
        // Data Source: Array
        // Return Type: ARRAY
        // Expression: '$.[2]' The third item
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setReturnType('array');
        $field->setValue($this->getFixture('array'));
        // Assert current value
        $this->assertEquals(['trabant'], $field->query('$.[2]'));
        // Now update it...
        $field->setValue('lada', null, '$.[2]');
        // Assert new value
        $this->assertEquals(['lada'], $field->query('$.[2]'));

        // Data Source: Array
        // Return Type: ARRAY
        // Expression: '$.[6]' The seventh item
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setReturnType('array');
        $field->setValue($this->getFixture('array'));
        // Assert current value
        $this->assertEquals([33.3333], $field->query('$.[6]'));
        // Now update it...
        $field->setValue(99.99, null, '$.[6]');
        // Assert new value
        $this->assertEquals([99.99], $field->query('$.[6]'));
        
        // Invalid #1
        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $field->setValue(99.99, null, '$[6]'); // Invalid JSON path expression
    }
    
    /**
     * Get the contents of a fixture
     * 
     * @param string $fixture
     * @return string
     */
    private function getFixture($fixture)
    {
        $files = $this->fixtures;
        return file_get_contents($files[$fixture]);
    }

}
