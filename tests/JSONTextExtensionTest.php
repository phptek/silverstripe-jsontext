<?php

/**
 * @package silverstripe-jsontext
 * @subpackage fields
 * @author Russell Michell <russ@theruss.com>
 */

use PhpTek\JSONText\Exception\JSONTextException;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\Member;
use PhpTek\JSONText\Dev\Fixture\MyAwesomeJSONPage;

class JSONTextExtensionTest extends FunctionalTest
{
    /**
     * @var string
     */
    protected static $extra_dataobjects = [
        MyAwesomeJSONPage::class,
    ];
    
    /**
     * @var string
     */
    protected static $fixture_file;
    
    /**
     * Modifies fixtures property to be able to run on PHP <5.6 without use of constant in class property which 5.6+ allows
     */
    public function __construct()
    {
        $dir = realpath(__DIR__);
        
        self::$fixture_file = $dir . '/fixtures/yml/JSONTextExtension.yml';
        
        parent::__construct();
    }
    
    /**
     * Is an exception thrown when no POSTed vars are available for
     * non DB-backed fields declared on a SiteTree class?
     */
    public function testExceptionThrownOnBeforeWrite()
    {
        $member = $this->objFromFixture(Member::class, 'admin');
        $fixture = $this->objFromFixture(MyAwesomeJSONPage::class, 'dummy');
        
        $member->logIn();
        $fixture->config()->update('json_field_map', ['MyJSON' => ['FooField']]);
        $fixture->write();
        
        // Submit a CMS POST request _without_ JSON data
        $this->setExpectedException(JSONTextException::class);
        $this->post('admin/pages/edit/EditForm/44/', [
            'ParentID' => '0',
            'action_save' => 'Saved',
            'ID' => '44',
        ]);
    }
}
