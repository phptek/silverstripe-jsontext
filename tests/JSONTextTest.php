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

        $this->assertEquals('great wall', $field->first());

        $fixture = '';
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);

        $this->assertNull($field->first());
    }

    public function testLast()
    {
        
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);

        $this->assertEquals('morris', $field->last());

        $fixture = '';
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);

        $this->assertNull($field->last());
    }

    public function testNth()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['indexed']);

        $this->assertEquals('great wall', $field->nth(0));
        $this->assertEquals('trabant', $field->nth(2));
        
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['empty']);

        $this->assertNull($field->nth(0));
    }

    public function testFind()
    {
        $field = JSONText::create('MyJSON');
        $field->setValue($this->fixture['hashed']);

        $this->assertEquals(['british' => ['vauxhall', 'morris']], $field->find('morris'));
    }
    
}
