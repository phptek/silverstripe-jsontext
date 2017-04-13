<?php

/**
 * This {@link DataExtension} allows you to declare arbitrary input fields in
 * your getCMSFields() methods without them ever going near an equivalent DBField.
 * 
 * The SilverStripe default gives you one DBField for every input field declared
 * in getCMSFields(). This extension however allows you to use a single blob
 * of JSON, stored in a single {@link JSONTextField} and manage each key=>value pair
 * from individual input fields without needing to declare equivalent or further
 * database fields. All you need to do is add a `$json_field_map` static to
 * your model (See below).
 * 
 * Notes: 
 * - Untested on non CHAR DB field-types (v0.8)
 * - Only works for single dimensional JSON data
 * 
 * <code>
 * private static $db = [
 *      'TestJSON' => 'JSONText',
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
 * @package jsontext
 * @subpackage control
 * @author Russell Michell <russ@theruss.com>
 */

namespace JSONText\Extensions;

use JSONText\Fields\JSONText;
use JSONText\Exceptions\JSONTextException;

class JSONTextExtension extends \DataExtension
{    
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
        $controller = \Controller::curr();
        $postVars = $controller->getRequest()->postVars();
        $fieldMap = $owner->config()->get('json_field_map');
        $doUpdate = (
            count($postVars) &&
            in_array(get_class($controller), ['CMSPageEditController', 'FakeController']) && 
            !empty($fieldMap)
        );
        
        if (!$doUpdate) {
            return null;
        }
        
        foreach ($owner->db() as $field => $type) {
            if ($type === 'JSONText') {
                $this->updateJSON($postVars, $owner);
            }
        }
    }
    
    /**
     * Called from {@link $this->onBeforeWrote()}. Inserts or updates each available
     * JSONText DB field with the appropriate input-field data, as per the model's 
     * "json_field_map" config static.
     * 
     * @param array $postVars
     * @param DataObject $owner
     * @return void
     * @throws JSONTextException
     */
    public function updateJSON(array $postVars, $owner)
    {
        $jsonFieldMap = $owner->config()->get('json_field_map');
        
        foreach ($jsonFieldMap as $jsonField => $mappedFields) {
            $jsonFieldData = [];
            
            foreach ($mappedFields as $fieldName) {
                if (!isset($postVars[$fieldName])) {
                    $msg = sprintf('%s doesn\'t exist in POST data.', $fieldName);
                    throw new JSONTextException($msg);
                }
                
                $jsonFieldData[$fieldName] = $postVars[$fieldName];
            }
            
            $fieldValue = singleton('JSONText')->toJson($jsonFieldData);
            $owner->setField($jsonField, $fieldValue);
        }
    }
    
    /**
     * The CMS input fields declared in the json_field_map static, are not DB-backed,
     * by virtue of this extension, they are backed by specific values represented
     * in the relevant JSON data. Therefore we need to pre-populate each such field's
     * value.
     * 
     * @param FieldList $fields
     * @return void
     */
    public function updateCMSFields(\FieldList $fields)
    {
        $owner = $this->getOwner();
        $jsonFieldMap = $owner->config()->json_field_map;
        
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
