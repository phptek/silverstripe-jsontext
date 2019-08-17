<?php

/**
 * This {@link DataExtension} allows you to declare arbitrary input fields in
 * your getCMSFields() methods without them ever going near an equivalent DBField.
 *
 * The SilverStripe default gives you one DBField for every input field declared
 * in getCMSFields(). This extension however allows you to use a single blob
 * of JSON, stored in a single {@link JSONTextField} and manage each key=>value pair
 * from individual form input fields, without needing to declare equivalent or further
 * database fields. All you need to do is add a `$json_field_map` config static to
 * your model or declare a method named `jsonFieldMap()`. See below.
 *
 * Notes:
 * - Untested on non CHAR DB field-types (v0.8)
 * - Only works for single dimensional JSON data
 *
 * <code>
 * private static $db = [
 *      'TestJSON' => JSONText::class,
 * ];
 *
 * private static $json_field_map = [
 *      'TestJSON' => ['TestField1', 'TestField2']
 * ];
 *
 * public function getCMSFields()
 * {
 *      $fields = parent::getCMSFields();
 *      $fields->addFieldsToTab('Root.Main', [
 *          TextField::create('TestField1', 'Test 1'),
 *          TextField::create('TestField2', 'Test 2'),
 *          TextField::create('TestJSON', 'Some JSON') // Uses a TextField for demo, normally this would be hidden from CMS users
 *      ]);
 *
 *      return $fields;
 * }
 * </code>
 *
 * @package silverstripe-jsontext
 * @author Russell Michell 2016-2019 <russ@theruss.com>
 */

namespace PhpTek\JSONText\Extension;

use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Core\ClassInfo;
use PhpTek\JSONText\Exception\JSONTextException;
use PhpTek\JSONText\Exception\JSONTextConfigException;
use PhpTek\JSONText\ORM\FieldType\JSONText;

class JSONTextExtension extends DataExtension
{

    public function __construct()
    {
        // Helpful class applicability message
        if (!Director::is_cli() && !class_exists(CMSPageEditController::class)) {
            $msg = 'Please install the silverstripe/cms package in order to use this extension.';
            throw new JSONTextException($msg);
        }

        return parent::__construct();
    }
    
    /**
     * Deal with userland declaration of a config static or a method for obtaining
     * an array of CMS input fields. Existence of a method takes precedence over
     * a config static.
     * 
     * @return array
     * @throws JSONTextConfigException When no field-mapping config is found.
     */
    public function getJSONFields()
    {
        $owner = $this->getOwner();
        
        if (ClassInfo::hasMethod($owner, 'jsonFieldMap')) {
            return $owner->jsonFieldMap();
        }
        
        if (!$owner->config()->get('json_field_map')) {
            throw new JSONTextConfigException('No field map found.');
        }
        
        return $owner->config()->get('json_field_map');
    }

    /**
     * Pre-process incoming CMS POST data, and modify any available {@link JSONText}
     * data for presentation in "headless" input fields.
     *
     * @return null
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $owner = $this->getOwner();
        $controller = Controller::curr();
        $postVars = $controller->getRequest()->postVars();

        if (!count($postVars)) {
            return null;
        }

        // Could also use DataObject::getSchema()->fieldSpecs()
        foreach ($owner->config()->get('db') as $type) {
            if ($type === JSONText::class) {
                $this->updateJSON($postVars, $owner);
            }
        }
    }

    /**
     * Called from {@link $this->onBeforeWrote()}. Inserts or updates each available
     * JSONText DB field with the appropriate input-field data, as per the model's
     * "json_field_map" config static or jsonFieldMap() method.
     *
     * @param  array $postVars
     * @param  DataObject $owner
     * @return void
     * @throws JSONTextException
     */
    public function updateJSON(array $postVars, $owner)
    {
        $jsonFieldMap = $this->getJSONFields();

        foreach ($jsonFieldMap as $jsonField => $mappedFields) {
            $jsonFieldData = [];

            foreach ($mappedFields as $fieldName) {
                if (!isset($postVars[$fieldName])) {
                    $msg = sprintf('%s doesn\'t exist in POST data.', $fieldName);
                    throw new JSONTextException($msg);
                }

                $jsonFieldData[$fieldName] = $postVars[$fieldName];
            }

            $fieldValue = singleton(JSONText::class)->toJson($jsonFieldData);
            $owner->setField($jsonField, $fieldValue);
        }
    }

    /**
     * The CMS input fields declared in the json_field_map static or via any
     * declared jsonFieldMap() method, are not DB-backed, by virtue of this extension,
     * they are backed by specific values represented in the relevant JSON data.
     * Therefore we need to pre-populate each such field's value.
     *
     * @param  FieldList $fields
     * @return void
     */
    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->getOwner();
        $jsonFieldMap = $this->getJSONFields();

        if (empty($jsonFieldMap)) {
            return;
        }

        foreach ($jsonFieldMap as $jsonField => $mappedFields) {
            if (!$owner->getField($jsonField)) {
                continue;
            }

            $jsonFieldObj = $owner->dbObject($jsonField);

            foreach ($mappedFields as $fieldName) {
                if (!$fieldValue = $jsonFieldObj->query('->>', $fieldName)) {
                    continue;
                }

                if ($fieldValue = array_values($jsonFieldObj->toArray($fieldValue))) {
                    $fieldValue = $fieldValue[0];
                    $owner->setField($fieldName, $fieldValue);
                }
            }
        }
    }
}
