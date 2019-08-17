<?php

namespace PhpTek\JSONText\Dev\Fixture;

use PhpTek\JSONText\ORM\FieldType\JSONText;
use PhpTek\JSONText\Extension\JSONTextExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\CMS\Model\SiteTree;

/**
 * @package silverstripe-jsontext
 */
class MyJSONPageWithNone extends SiteTree implements TestOnly
{
    private static $db = [
        'MyJSON' => JSONText::class
    ];

    /**
     * @var string
     * @config
     */
    private static $table_name = 'MyJSONPageWithNone';
    
    /**
     * @var array
     * @config
     */
    private static $extensions = [
        JSONTextExtension::class,
    ];
}
