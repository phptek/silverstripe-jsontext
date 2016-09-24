<?php

/**
 * Add this DataExtension to your models if you wish to have specific DB fields
 * receive JSON data POSTed from particular input fields in the CMS. 
 * 
 * You'll need to declare declare a `$jsontext_field_map` config static on the desired
 * model(s) as follows:
 * 
 * <code>
 * private static $jsontext_field_map = [
 *  'MyDBField1' => [
 *      'MyInputField1',
 *      'MyInputField2'
 *  ],
 *  'MyDBField2' => [
 *       'MyInputField3',
         'MyInputField4'
 *  ]
 * ];
 * <code>
 * 
 * @package silverstripe-jsontext
 * @subpackage extensions
 * @author Russell Michell <russ@theruss.com>
 */
         
class JSONTextExtension extends \DataExtension
{
    /**
     * Manipulate POSTed data from pre-specified CMS fields and write their data
     * as JSON.
     * 
     * @return void
     */
    public function onBeforeWrite()
    {
        // Fields are declared in a config static on decorated models
        $fieldMap = $this->getOwner()->config()->get('jsontext_field_map');
        $postVars = Controller::curr()->getRequest()->postVars();
        $data = [];
        
        foreach ($fieldMap as $dbFieldName => $inputFields) {
            foreach ($inputFields as $inputField) {
                $data[$dbFieldName][] = [$inputField => $postVars[$inputField]];
            }
            
            $jsonTextField = $this->getOwner()->dbObject($dbFieldName);
            $this->getOwner()->setField($dbFieldName, $jsonTextField->toJSON($data[$dbFieldName]));
        }
        
        parent::onBeforeWrite();
    }
    
}
