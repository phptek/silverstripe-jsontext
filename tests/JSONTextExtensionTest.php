<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */

class JSONTextExtensionTest extends FunctionalTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'jsontext/tests/fixtures/yml/JSONTextExtension.yml';
    
    /**
     * Ensure our TestOnly DO's are usable as fixtures in the test DB
     * 
     * @var array
     */
	protected $extraDataObjects = [
		'JSONTextTestPage',
	];
    
    /**
     * Is an exception thrown when no POSTed vars are available for
     * non DB-backed fields declared on a SiteTree class?
     */
    public function testExceptionThrownOnBeforeWrite()
    {
        $member = $this->objFromFixture('Member', 'admin');
        $this->session()->inst_set('loggedInAs', $member->ID);
        $fixture = $this->objFromFixture('JSONTextTestPage', 'jsontext-text');
        
        $this->setExpectedException('\JSONText\Exceptions\JSONTextException', "FooField doesn't exist in POST data.");
        
        $response = $this->post('admin/pages/edit/EditForm', [
            'Title' => 'Dummy',
            'URLSegment' => 'dummy',
			'action_save' => 1,
			'ID' => 44,
            'ParentID' => 1,
		]);
    }
}

class JSONTextTestPage extends Page implements TestOnly
{
    private static $db = [
        'MyJSON' => 'JSONText'
    ];
    
    private static $json_field_map = [
        'MyJSON' => ['FooField']
    ];
    
}
