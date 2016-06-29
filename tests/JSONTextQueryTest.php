<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 * @todo Add tests where source data is a JSON array, not just a JSON object
 * 
 *
 */

use JSONText\Fields\JSONText;
use JSONText\Exceptions;

class JSONTextQueryTest extends SapphireTest
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
     * @var \JSONText\Fields\JSONText
     */
    protected $sut;

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
     * Setup the System Under Test for this test suite.
     */
    public function setUp()
    {
        parent::setUp();

        $this->sut = JSONText::create('MyJSON');
    }

    /**
     * Tests query() by means of the integer Postgres Int match operator: '->'
     * 
     * @todo Use same source data instead of repeating..
     */
    public function testQueryWithMatchOnInt()
    {
        $field = $this->sut;
        
        // Data Source: Array
        // Return Type: ARRAY
        // Operator: "->" (Int)
        $field->setReturnType('array');
        $field->setValue($this->getFixture('array'));
        $this->assertEquals([2 => 'trabant'], $field->query('->', 2));
        
        // Data Source: Array
        // Return Type: JSON
        // Operator: "->" (Int)
        $field->setReturnType('json');
        $field->setValue($this->getFixture('array'));
        $this->assertEquals('{"2":"trabant"}', $field->query('->', 2));
        $this->assertEquals('{"5":101}', $field->query('->', 5));
        
        // Data Source: Array
        // Return Type: SILVERSTRIPE
        // Operator: "->" (Int)
        // SS Type: Float
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('array'));
        $this->assertInternalType('array', $field->query('->', 3));
        $this->assertInstanceOf('Float', $field->query('->', 3)[3]);
        $this->assertEquals(44.6, $field->query('->', 3)[3]->getValue());

        // Data Source: Array
        // Return Type: SILVERSTRIPE
        // Operator: "->" (Int)
        // SS Type: Boolean
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('array'));
        $this->assertInternalType('array', $field->query('->', 1));
        $this->assertInstanceOf('Boolean', $field->query('->', 1)[1]);
        $this->assertEquals(1, $field->query('->', 1)[1]->getValue());

        // Data Source: Array
        // Return Type: SILVERSTRIPE
        // Operator: "->" (Int)
        // SS Type: Int
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('array'));
        $this->assertInternalType('array', $field->query('->', 5));
        $this->assertInstanceOf('Int', $field->query('->', 5)[5]);
        $this->assertEquals(101, $field->query('->', 5)[5]->getValue());

        // Data Source: Array
        // Return Type: SILVERSTRIPE
        // Operator: "->" (Int)
        // SS Type: Varchar
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('array'));
        $this->assertInternalType('array', $field->query('->', 4));
        $this->assertInstanceOf('Varchar', $field->query('->', 4)[4]);
        $this->assertEquals('buick', $field->query('->', 4)[4]->getValue());

        // Test: Empty #1
        $field->setReturnType('array');
        $field->setValue('');
        $this->assertInternalType('array', $field->query('->', 3));
        $this->assertCount(0, $field->query('->', 3));

        // Test: Invalid #1
        $field->setReturnType('array');
        $field->setValue('["morris"]');
        $this->assertEquals([], $field->query('->', 17));

        // Test: Invalid #2
        $field->setReturnType('array');
        $field->setValue('["ass"]');
        $this->assertEquals(['ass'], $field->query('->', 0));
    }

    /**
     * Tests query() by means of the integer Postgres String match operator: '->>'
     */
    public function testQueryWithMatchOnStr()
    {
        $field = $this->sut;
        
        // Data Source: Object
        // Return Type: ARRAY
        // Operator: "->>" (String)
        $field->setReturnType('array');
        $field->setValue($this->getFixture('object'));
        $this->assertEquals(['Subaru' => 'Impreza'], $field->query('->>', 'Subaru'));
        $this->assertEquals(
            ['japanese' => ['fast' => ['Subaru' => 'Impreza'], 'slow' => ['Honda' => 'Civic']]],
            $field->query('->>', 'japanese')
        );

        // Data Source: Object
        // Return Type: JSON
        // Operator: "->>" (String)
        $field->setReturnType('json');
        $field->setValue($this->getFixture('object'));
        $this->assertEquals('{"Subaru":"Impreza"}', $field->query('->>', 'Subaru'));
        $this->assertEquals(
            '{"japanese":{"fast":{"Subaru":"Impreza"},"slow":{"Honda":"Civic"}}}',
            $field->query('->>', 'japanese')
        );

        // Data Source: Object
        // Return Type: SilverStripe
        // Operator: "->>" (String)
        // SS Type: Varchar
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        $this->assertInternalType('array', $field->query('->>', 'Subaru'));
        $this->assertInstanceOf('Varchar', $field->query('->>', 'Subaru')['Subaru']);
        $this->assertEquals('Impreza', $field->query('->>', 'Subaru')['Subaru']->getValue());

        // Data Source: Object
        // Return Type: SilverStripe
        // Operator: "->>" (String)
        // SS Type: Boolean
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        $this->assertInternalType('array', $field->query('->>', 'beer tastes good'));
        $this->assertInstanceOf('Boolean', $field->query('->>', 'beer tastes good')['beer tastes good']);
        $this->assertEquals(1, $field->query('->>', 'beer tastes good')['beer tastes good']->getValue());

        // Data Source: Object
        // Return Type: SilverStripe
        // Operator: "->>" (String)
        // SS Type: Float
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        $this->assertInternalType('array', $field->query('->>', 'how sure are you'));
        $this->assertInstanceOf('Float', $field->query('->>', 'how sure are you')['how sure are you']);
        $this->assertEquals(99.99, $field->query('->>', 'how sure are you')['how sure are you']->getValue());

        // Data Source: Object
        // Return Type: SilverStripe
        // Operator: "->>" (String)
        // SS Type: Int
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        $this->assertInternalType('array', $field->query('->>', 'how high'));
        $this->assertInstanceOf('Int', $field->query('->>', 'how high')['how high']);
        $this->assertEquals(6, $field->query('->>', 'how high')['how high']->getValue());

        // Data Source: Object
        // Return Type: SilverStripe
        // Operator: "->>" (String)
        // SS Type: N/A Nested sub-array example, expect an array
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        $this->assertInternalType('array', $field->query('->>', 'planes')['planes']);
        $this->assertInternalType('array', $field->query('->>', 'planes')['planes']['russian']);
        $this->assertCount(2, $field->query('->>', 'planes')['planes']['russian']);
        $this->assertInstanceOf('Varchar', $field->query('->>', 'planes')['planes']['russian'][0]);
        $this->assertInstanceOf('Varchar', $field->query('->>', 'planes')['planes']['russian'][1]);

        // Test: Empty #1
        $field->setReturnType('array');
        $field->setValue('');
        $this->assertInternalType('array', $field->query('->>', 'planes'));
        $this->assertCount(0, $field->query('->', 3));

        // Test: Empty #2
        $field->setReturnType('array');
        $field->setValue('["morris"]');
        $this->assertEquals([], $field->query('->', 17));

        // Test: Invalid #1
        $field->setReturnType('array');
        $field->setValue('["trabant"]');
        $this->assertEquals([], $field->query('->', 1));
    }

    /**
     * Tests query() by means of the Postgres path-match operator: '#>'
     */
    public function testQueryWithMatchOnPath()
    {
        $field = $this->sut;
        
        // Data Source: Object
        // Return Type: ARRAY
        // Operator: "#>" (Path)
        // Expect: Array due to duplicate keys in different parts of the source data
        $field->setReturnType('array');
        $field->setValue($this->getFixture('object'));
        $this->assertEquals(
            [['Subaru' => 'Impreza'],['Kawasaki' => 'KR1S250']],
            $field->query('#>', '{"japanese":"fast"}')
        );

        // Data Source: Object
        // Return Type: JSON
        // Operator: "#>" (Path)
        // Expect: Array due to duplicate keys in different parts of the source data
        $field->setReturnType('json');
        $field->setValue($this->getFixture('object'));
        $this->assertEquals(
            '[{"Subaru":"Impreza"},{"Kawasaki":"KR1S250"}]',
            $field->query('#>', '{"japanese":"fast"}')
        );

        // Data Source: Object
        // Return Type: SILVERSTRIPE
        // Operator: "#>" (Path)
        // Expect: Array due to duplicate keys in different parts of the source data
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        $this->assertInternalType('array', $field->query('#>', '{"japanese":"fast"}'));
        $this->assertCount(2, $field->query('#>', '{"japanese":"fast"}'));
        
        $one = $field->query('#>', '{"japanese":"fast"}')[0];
        $two = $field->query('#>', '{"japanese":"fast"}')[1];
        
        $this->assertInternalType('array', $one);
        $this->assertInternalType('array', $two);
        $this->assertInstanceOf('Varchar', array_values($one)[0]);
        $this->assertInstanceOf('Varchar', array_values($two)[0]);
        $this->assertEquals('Impreza', array_values($one)[0]->getValue());
        $this->assertEquals('KR1S250', array_values($two)[0]->getValue());

        // Data Source: Object
        // Return Type: ARRAY
        // Operator: "#>" (Path)
        // Expect: Direct scalar comparison assertion
        $field->setReturnType('array');
        $field->setValue($this->getFixture('object'));
        $this->assertEquals(['airbus'], $field->query('#>', '{"planes":"french"}'));

        // Data Source: Object
        // Return Type: JSON
        // Operator: "#>" (Path)
        // Expect: Direct scalar comparison assertion
        $field->setReturnType('json');
        $field->setValue($this->getFixture('object'));
        $this->assertEquals('["airbus"]', $field->query('#>', '{"planes":"french"}'));

        // Data Source: Object
        // Return Type: SILVERSTRIPE
        // Operator: "#>" (Path)
        // Expect: Direct scalar comparison assertion (Varchar)
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        $this->assertInternalType('array', $field->query('#>', '{"planes":"french"}'));
        $this->assertInstanceOf('Varchar', $field->query('#>', '{"planes":"french"}')[0]);
        $this->assertEquals('airbus', $field->query('#>', '{"planes":"french"}')[0]->getValue());

        // Data Source: Object
        // Return Type: SILVERSTRIPE
        // Operator: "#>" (Path)
        // Expect: Direct scalar comparison assertion (Float)
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        
        $res = $field->query('#>', '{"floats":"0"}');
        
        $this->assertInternalType('array', $res);
        $this->assertInternalType('array', $res[0]); // Why? Because value of "floats" key is a JSON array
        $this->assertInstanceOf('Float', array_values($res[0])[0]);
        $this->assertEquals(99.99, array_values($res[0])[0]->getValue());

        // Data Source: Object
        // Return Type: SILVERSTRIPE
        // Operator: "#>" (Path)
        // Expect: Direct scalar comparison assertion (Int)
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));

        $res = $field->query('#>', '{"ints":"0"}');

        $this->assertInternalType('array', $res);
        $this->assertInternalType('array', $res[0]); // Why? Because value of "floats" key is a JSON array
        $this->assertInstanceOf('Int', array_values($res[0])[1]);
        $this->assertEquals(6, array_values($res[0])[1]->getValue());

        // Data Source: Object
        // Return Type: SILVERSTRIPE
        // Operator: "#>" (Path)
        // Expect: Direct scalar comparison assertion (Boolean)
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));

        $res = $field->query('#>', '{"booleans":"0"}');

        $this->assertInternalType('array', $res);
        $this->assertInternalType('array', $res[0]); // Why? Because value of "booleans" key is a JSON array
        $this->assertInstanceOf('Boolean', array_values($res[0])[0]);
        $this->assertEquals(1, array_values($res[0])[0]->getValue());

        // #1 Empty source data
        $field->setReturnType('array');
        $field->setValue('');
        $this->assertEquals([], $field->query('#>', '{"japanese":"fast"}'));

        // #2 JSON path not found
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        $this->assertNull($field->query('#>', '{"ints":"1"}')); // The "ints" key only has a single array as a value

        // #3 Invalid operand on RHS
        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $field->setReturnType('array');
        $field->setValue($this->getFixture('object'));
        $this->assertEquals(['Kawasaki' => 'KR1S250'], $field->query('#>', '{"japanese":"fast"'));
    }

    /**
     * Tests query() by means of JSONPath expressions.
     * N.b. only a minimum no. tests should be required, given that the 3rd party lib via which this functionality
     * is derived, is itself well tested.
     */
    public function testQueryWithMatchOnExpr()
    {
        $field = $this->sut;
        
        // Data Source: Object
        // Return Type: ARRAY
        // Expression: "$.." (Everything)
        // Expect: Array, obviously due to no. nodes in the source JSON
        $field->setReturnType('array');
        $field->setValue($this->getFixture('object'));
        $this->assertInternalType('array', $field->query('$..'));
        $this->assertCount(25, $field->query('$..'));

        // Data Source: Object
        // Return Type: ARRAY
        // Expression: "$..japanese[*]" (An array of children of all keys matching "japanese")
        // Expect: Array
        $field->setReturnType('array');
        $field->setValue($this->getFixture('object'));
        $this->assertCount(4, $field->query('$..japanese[*]'));
        $this->assertEquals([
            ['Subaru' => 'Impreza'],
            ['Honda' => 'Civic'],
            ['Kawasaki' => 'KR1S250'],
            ['Honda' => 'FS1']
        ], $field->query('$..japanese[*]'));

        // Data Source: Object
        // Return Type: JSON
        // Expression: "$..japanese[*]" (An array of children of all keys matching "japanese")
        // Expect: JSON Array of JSON objects
        $field->setReturnType('json');
        $field->setValue($this->getFixture('object'));
        $this->assertEquals(
            '[{"Subaru":"Impreza"},{"Honda":"Civic"},{"Kawasaki":"KR1S250"},{"Honda":"FS1"}]',
            $field->query('$..japanese[*]')
        );

        // Data Source: Object
        // Return Type: Array
        // Expression: "$.cars.american[*]" (All entries in the american cars node)
        // Expect: Array
        $field->setReturnType('array');
        $field->setValue($this->getFixture('object'));
        $this->assertEquals(['buick', 'oldsmobile'], $field->query('$.cars.american[*]'));
        $this->assertEquals(['buick'], $field->query('$.cars.american[0]'));

        // Data Source: Object
        // Return Type: Array
        // Expression: "$.cars.american[*]" (All entries in the american cars node)
        // Expect: Array 0f SilverStripe types
        $field->setReturnType('silverstripe');
        $field->setValue($this->getFixture('object'));
        $this->assertInternalType('array', $field->query('$.cars.american[*]'));
        $this->assertCount(2, $field->query('$.cars.american[*]'));
        $this->assertInstanceOf('Varchar', $field->query('$.cars.american[*]')[0]);
        $this->assertEquals('buick', $field->query('$.cars.american[*]')[0]->getValue());
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
