<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

class JSONTextIntegrationTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '/tests/fixtures/yml/MyAwesomeJSONModel.yml';

    /**
     * Modifies fixtures property to be able to run on PHP <5.6 without use of constant in class property which 5.6+ allows
     */
    public function __construct()
    {
        self::$fixture_file = MODULE_DIR . '/tests/fixtures/yml/MyAwesomeJSONModel.yml';
    }

    /**
     * Tests JSONText::setValue() by means of a simple JSONPath expression operating on a nested JSON array
     * on a saved DataObject record.
     */
    public function testSetValueOnNestedArray()
    {
        $model = $this->objFromFixture('MyAwesomeJSONModel', 'json-as-object');
        $expression = '$.cars.american.[0]';
        $field = $model->dbObject('MyJSON');

        // Primary assertion (JSON as return type is the default)
        $this->assertEquals('["buick"]', $field->query($expression));
        
        // How about now?
        $field->setValue('ford', null, $expression);
        $model->setField('MyJSON', $field->getValue());
        $model->write();
        $this->assertEquals('["ford"]', $field->query($expression));

        // Secondary assertion
        $field
            ->setValue('chrysler', null, $expression)
            ->setReturnType('array');

        // With chaining
        $model->setField('MyJSON', $field->getValue());
        $model->write();
        $this->assertEquals(['chrysler'], $field->query($expression));
    }

    /**
     * Tests JSONText::setValue() by means of a simple JSONPath expression operating on a simple, un-nested JSON array
     * on a saved DataObject record.
     */
    public function testSetValueOnUnNestedArray()
    {
        $model = $this->objFromFixture('MyAwesomeJSONModel', 'json-as-array');
        $expression = '$.[0]';
        $field = $model->dbObject('MyJSON');

        // Primary assertion (JSON as return type is the default)
        $this->assertEquals('["buick"]', $field->query($expression));

        // Secondary assertion
        $field->setValue('ford', null, $expression);
        $model->setField('MyJSON', $field->getValue());
        $model->write();
        $this->assertEquals('["ford"]', $field->query($expression));
        
        // With chaining
        $field
            ->setValue('chrysler', null, $expression)
            ->setReturnType('array');
        
        $model->setField('MyJSON', $field->getValue());
        $model->write();
        $this->assertEquals(['chrysler'], $field->query($expression));
    }

}

/**
 * @package silverstripe-jsontext
 */
class MyAwesomeJSONModel extends DataObject implements TestOnly
{
    private static $db = [
        'MyJSON' => '\phptek\JSONText\Fields\JSONText'
    ];
}