<?php

/**
 * @package silverstripe-jsontext
 * @author Russell Michell 2016-2019 <russ@theruss.com>
 */
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBFloat;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\FieldType\DBInt;
use PhpTek\JSONText\ORM\FieldType\JSONText;

class JSONTextTest extends SapphireTest
{
    /**
     * @var array
     */
    protected $fixtures = [
        'array'     => 'fixtures/json/array.json',
        'object'    => 'fixtures/json/object.json'
    ];

    /**
     * JSONTextTest constructor.
     *
     * Modify fixtures property to be able to run on PHP <5.6 without use of constant in class property which 5.6+ allows
     */
    public function __construct()
    {
        foreach($this->fixtures as $name => $path) {
            $this->fixtures[$name] = realpath(__DIR__) . '/' . $path;
        }
        
        parent::__construct();
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
        $this->assertInstanceOf(DBFloat::class, $data);

        $data = $field->first()[0];
        $this->assertInstanceOf(DBVarchar::class, $data);

        $data = $field->nth(5)[5];
        $this->assertInstanceOf(DBInt::class, $data);

        $data = $field->nth(1)[1];
        $this->assertInstanceOf(DBBoolean::class, $data);

        $field->setValue('["true"]');
        $data = $field->first()[0];
        $this->assertInstanceOf(DBVarchar::class, $data);
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
