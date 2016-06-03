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
        'empty' => ''
    ];
    
    public function testFirst()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $this->assertEquals([0 => 'great wall'], $field->first());
        
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $this->assertNull($field->first());
    }

    public function testLast()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $this->assertEquals([6 => 'morris'], $field->last());
        
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $this->assertNull($field->last());
    }

    public function testNth()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);
        $this->assertEquals([0 => 'great wall'], $field->nth(0));
        $this->assertEquals([2 => 'trabant'], $field->nth(2));
        
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);
        $this->assertNull($field->nth(0));

        $this->setExpectedException('JSONTextException');
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->nth('2'));
    }

    public function testFind()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->find('morris'));
        
        $this->setExpectedException('JSONTextException');
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);
        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->find(new StdClass));
        
    }
    
}
