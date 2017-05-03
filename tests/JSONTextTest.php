<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */

use phptek\JSONText\Fields\JSONText;
use SilverStripe\Dev\SapphireTest;

class JSONTextTest extends SapphireTest
{
    /**
     * @var array
     */
    protected $fixtures = [
        'array'     => 'tests/fixtures/json/array.json',
        'object'    => 'tests/fixtures/json/object.json'
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
     * @todo There are a ton more permutations of a JSONPath regex
     * See the trace() method in JSONPath for more examples to work from
     */
    public function testIsValidExpression()
    {
        $field = JSONText::create('MyJSON');
        
        $this->assertTrue($field->isValidExpression('$..'));
        $this->assertTrue($field->isValidExpression('*'));
        $this->assertTrue($field->isValidExpression('$.[2]'));
        $this->assertTrue($field->isValidExpression('$.cars.american[*]'));
        $this->assertTrue($field->isValidExpression('[0:1:1]'));
        $this->assertFalse($field->isValidExpression('[0:1:]'));
        $this->assertFalse($field->isValidExpression('[0:1:1'));
        $this->assertFalse($field->isValidExpression(''));
        $this->assertFalse($field->isValidExpression('$.1.cars.american[*]'));
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
        $this->assertTrue($field->isValidDBValue('["one","two"]'));
        $this->assertTrue($field->isValidDBValue('{"cars":{"american":["buick","oldsmobile"]}}'));
    }
    
    /**
     * Properly excercise our internal SS type conversion.
     */
    public function testToSSTypes()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->getFixture('array'));
        $field->setReturnType('silverstripe');
        
        $data = $field->last()[6];
        $this->assertInstanceOf('\SilverStripe\ORM\FieldType\DBFloat', $data);
        
        $data = $field->first()[0];
        $this->assertInstanceOf('\SilverStripe\ORM\FieldType\DBVarchar', $data);
        
        $data = $field->nth(5)[5];
        $this->assertInstanceOf('\SilverStripe\ORM\FieldType\DBInt', $data);
        
        $data = $field->nth(1)[1];
        $this->assertInstanceOf('\SilverStripe\ORM\FieldType\DBBoolean', $data);
        
        $field->setValue('["true"]');
        $data = $field->first()[0];
        $this->assertInstanceOf('\SilverStripe\ORM\FieldType\DBVarchar', $data);
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
