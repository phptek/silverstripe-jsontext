<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */
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
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['invalid']);
        $this->setExpectedException('JSONTextException');
        $this->assertEquals(['chinese' => 'great wall'], $field->getValueAsIterable());

        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $this->assertEquals([], $field->getValueAsIterable());
    }

    public function testFirst_AsArray()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('array');
        $this->assertEquals([0 => 'great wall'], $field->first());

        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('array');
        $this->assertInternalType('array', $field->first());
        $this->assertCount(0, $field->first());
    }

    public function testFirst_AsJson()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('json');
        $this->assertEquals('["great wall"]', $field->first());

        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('json');
        $this->assertInternalType('string', $field->first());
        $this->assertEquals('[]', $field->first());
    }

    public function testLast_AsArray()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('array');
        $this->assertEquals([6 => 'morris'], $field->last());

        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('array');
        $this->assertInternalType('array', $field->first());
        $this->assertCount(0, $field->first());
    }

    public function testLast_AsJson()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('json');
        $this->assertEquals('{"6":"morris"}', $field->last());

        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('json');
        $this->assertInternalType('string', $field->first());
        $this->assertEquals('[]', $field->first());
    }

    public function testNth_AsArray()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('array');
        $this->assertEquals([0 => 'great wall'], $field->nth(0));
        $this->assertEquals([2 => 'trabant'], $field->nth(2));

        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('array');
        $this->assertInternalType('array', $field->first());
        $this->assertCount(0, $field->first());

        $this->setExpectedException('JSONTextException');
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $field->setReturnType('array');
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->nth('2'));
    }

    public function testNth_AsJson()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $field->setReturnType('json');
        $this->assertEquals('["great wall"]', $field->nth(0));
        $this->assertEquals('{"2":"trabant"}', $field->nth(2));

        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $field->setReturnType('json');
        $this->assertInternalType('string', $field->first());
        $this->assertEquals('[]', $field->first());

        $this->setExpectedException('JSONTextException');
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $field->setReturnType('json');
        $this->assertEquals('{"british":["vauxhall","morris"]}', $field->nth('2'));
    }

    public function testExtract_AsArray()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $field->setReturnType('array');

        // By key
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->extract('->', 'british'));
        $this->assertEquals([0 => 'vauxhall'], $field->extract('->', 6));
        $this->assertEquals([], $field->extract('->', '6')); // strict handling

        // By value
        // TODO: Use rewind() to get he tp-level key > val from the original iterator
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->extract('<-', 'morris'));
/*        $this->assertEquals([0 => 'vauxhall'], $field->extract('<-', 6));
        $this->assertEquals([], $field->extract('->', '6')); // strict handling*/
    }

}
