<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */

use JSONText\Fields;
use JSONText\Exceptions;

class JSONTextTest extends SapphireTest
{
    /**
     * @var array
     */
    protected $fixtures = [
        'array_simple'  => 'tests/fixtures/json/array_simple.json',
        'hash_simple'   => 'tests/fixtures/json/hash_simple.json',
        'invalid'       => 'tests/fixtures/json/invalid.json',
        'hash_deep'     => 'tests/fixtures/json/hash_deep.json',
        'hash_dupes'    => 'tests/fixtures/json/hash_duplicated.json',
        'empty'         => 'tests/fixtures/json/empty.json',
        'minimal_bool'  => 'tests/fixtures/json/minimal_bool.json'
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

    public function testgetValueAsIterable()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('invalid'));
        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $this->assertEquals(['chinese' => 'great wall'], $field->getValueAsIterable());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $this->assertEquals([], $field->getValueAsIterable());
    }

    public function testFirst_AsArray()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('array_simple'));
        $field->setReturnType('array');
        $this->assertEquals([0 => 'great wall'], $field->first());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $field->setReturnType('array');
        $this->assertInternalType('array', $field->first());
        $this->assertCount(0, $field->first());
    }

    public function testFirst_AsJson()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('array_simple'));
        $field->setReturnType('json');
        $this->assertEquals('["great wall"]', $field->first());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $field->setReturnType('json');
        $this->assertInternalType('string', $field->first());
        $this->assertEquals('[]', $field->first());
    }

    public function testLast_AsArray()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('array_simple'));
        $field->setReturnType('array');
        $this->assertEquals([6 => 'morris'], $field->last());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $field->setReturnType('array');
        $this->assertInternalType('array', $field->first());
        $this->assertCount(0, $field->first());
    }

    public function testLast_AsJson()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('array_simple'));
        $field->setReturnType('json');
        $this->assertEquals('{"6":"morris"}', $field->last());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $field->setReturnType('json');
        $this->assertInternalType('string', $field->first());
        $this->assertEquals('[]', $field->first());
    }

    public function testNth_AsArray()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('array_simple'));
        $field->setReturnType('array');
        $this->assertEquals([0 => 'great wall'], $field->nth(0));
        $this->assertEquals([2 => 'trabant'], $field->nth(2));

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $field->setReturnType('array');
        $this->assertInternalType('array', $field->first());
        $this->assertCount(0, $field->first());

        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_simple'));
        $field->setReturnType('array');
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->nth('2'));
    }

    public function testNth_AsJson()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('array_simple'));
        $field->setReturnType('json');
        $this->assertEquals('["great wall"]', $field->nth(0));
        $this->assertEquals('{"2":"trabant"}', $field->nth(2));

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $field->setReturnType('json');
        $this->assertInternalType('string', $field->first());
        $this->assertEquals('[]', $field->first());

        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_simple'));
        $field->setReturnType('json');
        $this->assertEquals('{"british":["vauxhall","morris"]}', $field->nth('2'));
    }

    /**
     * Tests query() by means of the integer Postgres operator: '->'
     */
    public function testQuery_MatchOnKeyAsInt_AsArray()
    {
        // Hashed
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_simple'));
        $field->setReturnType('array');
        
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->query('->', 5));
        $this->assertEquals(['american' => ['buick', 'oldsmobile', 'ford']], $field->query('->', 1));
        $this->assertEquals([], $field->query('->', '6')); // strict handling

        // Empty
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $field->setReturnType('array');
        $this->assertEquals([], $field->query('->', 42));

        // Nested
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_deep'));
        $field->setReturnType('array');
        
        $this->assertEquals(['planes' => ['russian' => ['antonov', 'mig'], 'french' => 'airbus']], $field->query('->', 7));
        $this->assertEquals([], $field->query('->', '7')); // Attempt to match a string using the int matcher 
        $this->assertEquals([0 => 'buick'], $field->query('->', 2));
    }

    /**
     * Tests query() by means of the string Postgres operator: '->>'
     */
    public function testQuery_MatchOnKeyAsStr_AsArray()
    {
        // Hashed
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_simple'));
        $field->setReturnType('array');

        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->query('->>', 'british'));
        $this->assertEquals([], $field->query('->', '6')); // strict handling

        // Empty
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $field->setReturnType('array');
        $this->assertEquals([], $field->query('->>', 'british'));

        // Nested
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_deep'));
        $field->setReturnType('array');

        $this->assertEquals(['planes' => ['russian' => ['antonov', 'mig'], 'french' => 'airbus']], $field->query('->>', 'planes'));
        $this->assertEquals([], $field->query('->', '7')); // Attempt to match a string using the int matcher
    }

    /**
     * Tests query() by means of the string Postgres operator: '->>'
     */
    public function testQuery_MatchOnKeyAsStr_AsJSON()
    {
        // Hashed
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_simple'));
        $field->setReturnType('json');

        $this->assertEquals('{"british":["vauxhall","morris"]}', $field->query('->>', 'british'));
        $this->assertEquals('[]', $field->query('->', '6')); // strict handling

        // Empty
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('empty'));
        $field->setReturnType('json');
        $this->assertEquals('[]', $field->query('->>', 'british'));

        // Nested
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_deep'));
        $field->setReturnType('json');

        $this->assertEquals('{"planes":{"russian":["antonov","mig"],"french":"airbus"}}', $field->query('->>', 'planes'));
        $this->assertEquals('[]', $field->query('->', '7')); // Attempt to match a string using the int matcher
    }

    /**
     * Tests query() by means of path-matching using the Postgres path match operator: '#>'
     */
    public function testQuery_MatchPath_AsArray()
    {
        // Hashed
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_deep'));
        $field->setReturnType('array');
        
        $this->assertEquals(["fast" => ["Kawasaki" => "KR1S250"],"slow" => ["Honda" => "FS150"]], $field->query('#>', '{"bikes":"japanese"}'));

        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $field->query('#>', '{"bikes","japanese"}'); // Bad JSON
    }

    /**
     * Tests query() by means of path-matching using the Postgres path match operator: '#>'
     */
    public function testQuery_MatchPath_AsJSON()
    {
        // Hashed
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_deep'));
        $field->setReturnType('json');
        
        $this->assertEquals('{"fast":{"Kawasaki":"KR1S250"},"slow":{"Honda":"FS150"}}', $field->query('#>', '{"bikes":"japanese"}'));
    }

    /**
     * Tests query() by means of path-matching using the Postgres path match operator: '#>' but where duplicate keys exist 
     * for different parent structures in the source data
     */
    public function testQuery_MatchPathDuplicate_AsArray()
    {
        // Hashed
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_dupes'));
        $field->setReturnType('array');

        $this->assertEquals([["Subaru" => "Impreza"],["Kawasaki" => "KR1S250"]], $field->query('#>', '{"japanese":"fast"}'));
        $this->assertEquals([], $field->query('#>', '{"":"fast"}')); // No match
        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $this->assertEquals([], $field->query('#>', 1)); // Bad matcher used
    }

    /**
     * Tests query() by means of path-matching using the Postgres path match operator: '#>' but where duplicate keys exist
     * for different parent structures in the source data
     */
    public function testQuery_MatchPathDuplicate_AsJSON()
    {
        // Hashed
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->getFixture('hash_dupes'));
        $field->setReturnType('json');

        $this->assertEquals('[{"Subaru":"Impreza"},{"Kawasaki":"KR1S250"}]', $field->query('#>', '{"japanese":"fast"}'));
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
