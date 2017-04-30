<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\TestOnly;

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
        $fixture = $this->objFromFixture('JSONTextTestPage', 'jsontext-text');
        $this->session()->inst_set('loggedInAs', $member->ID);
        
        $this->setExpectedException('JSONTextException', "FooField doesn't exist in POST data.");
        $this->post('admin/pages/edit/EditForm', [
            'Title' => 'Dummy',
            'action_save' => 1,
            'ID' => 44,
        ]);
    }
}

class JSONTextTestPage extends Page implements TestOnly
{
    private static $db = [
        'MyJSON' => '\phptek\JSONText\Fields\JSONText'
    ];
    
    private static $json_field_map = [
        'MyJSON' => ['FooField']
    ];
}
