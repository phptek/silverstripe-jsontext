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
     * @var string
     */
    protected $fixture = [
        'indexed' => '["great wall", "ford", "trabant", "oldsmobile", "buick", "vauxhall", "morris"]',
        'hashed' => '{"chinese":"great wall","american":["buick","oldsmobile","ford"],"british":["vauxhall","morris"]}',
        'invalid' => '{"chinese":"great wall","american":["buick","oldsmobile","ford"],"british":["vauxhall","morris]',
        'nested' => '{"cars":{"american":["buick","oldsmobile"],"british":["vauxhall","morris"]},"planes":{"russian":["antonov","mig"],"french":"airbus"}}',
        'multiple' => '{"cars":{"american":["buick","oldsmobile"],"british":["vauxhall","morris"]},"planes":{"british":"airbus","french":"airbus"}}',
        'empty' => ''
    ];
    
    public function testgetValueAsIterable()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['invalid']);
        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $this->assertEquals(['chinese' => 'great wall'], $field->getValueAsIterable());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $this->assertEquals([], $field->getValueAsIterable());
    }

    public function testFirst_AsArray()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('array');
        $this->assertEquals([0 => 'great wall'], $field->first());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('array');
        $this->assertInternalType('array', $field->first());
        $this->assertCount(0, $field->first());
    }

    public function testFirst_AsJson()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('json');
        $this->assertEquals('["great wall"]', $field->first());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('json');
        $this->assertInternalType('string', $field->first());
        $this->assertEquals('[]', $field->first());
    }

    public function testLast_AsArray()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('array');
        $this->assertEquals([6 => 'morris'], $field->last());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('array');
        $this->assertInternalType('array', $field->first());
        $this->assertCount(0, $field->first());
    }

    public function testLast_AsJson()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('json');
        $this->assertEquals('{"6":"morris"}', $field->last());

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('json');
        $this->assertInternalType('string', $field->first());
        $this->assertEquals('[]', $field->first());
    }

    public function testNth_AsArray()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('array');
        $this->assertEquals([0 => 'great wall'], $field->nth(0));
        $this->assertEquals([2 => 'trabant'], $field->nth(2));

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('array');
        $this->assertInternalType('array', $field->first());
        $this->assertCount(0, $field->first());

        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $field->setReturnType('array');
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->nth('2'));
    }

    public function testNth_AsJson()
    {
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('json');
        $this->assertEquals('["great wall"]', $field->nth(0));
        $this->assertEquals('{"2":"trabant"}', $field->nth(2));

        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('json');
        $this->assertInternalType('string', $field->first());
        $this->assertEquals('[]', $field->first());

        $this->setExpectedException('\JSONText\Exceptions\JSONTextException');
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $field->setReturnType('json');
        $this->assertEquals('{"british":["vauxhall","morris"]}', $field->nth('2'));
    }

    /**
     * Tests query() by means of the integer Postgres operator: ->
     */
    public function testquery_AsInt_AsArray()
    {
        // Hashed
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $field->setReturnType('array');
        
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->query('->', 5));
        $this->assertEquals(['american' => ['buick', 'oldsmobile', 'ford']], $field->query('->', 1));
        $this->assertEquals([], $field->query('->', '6')); // strict handling

        // Empty
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('array');
        $this->assertEquals([], $field->query('->', 42));

        // Nested
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['nested']);
        $field->setReturnType('array');
        
        $this->assertEquals(['planes' => ['russian' => ['antonov', 'mig'], 'french' => 'airbus']], $field->query('->', 7));
        $this->assertEquals([], $field->query('->', '7')); // Attempt to match a string using the int matcher 
        $this->assertEquals([0 => 'buick'], $field->query('->', 2));
    }

    /**
     * Tests query() by means of the string Postgres operator: ->>
     */
    public function testquery_AsStr_AsArray()
    {
        // Hashed
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $field->setReturnType('array');

        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->query('->>', 'british'));
        $this->assertEquals([], $field->query('->', '6')); // strict handling

        // Empty
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('array');
        $this->assertEquals([], $field->query('->>', 'british'));

        // Nested
        $field = JSONText\Fields\JSONText::create('MyJSON');
        $field->setValue($this->fixture['nested']);
        $field->setReturnType('array');

        $this->assertEquals(['planes' => ['russian' => ['antonov', 'mig'], 'french' => 'airbus']], $field->query('->>', 'planes'));
        $this->assertEquals([], $field->query('->', '7')); // Attempt to match a string using the int matcher
    }

}
