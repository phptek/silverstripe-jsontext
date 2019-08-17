<?php

namespace PhpTek\JSONText\Dev\Fixture;

use PhpTek\JSONText\ORM\FieldType\JSONText;
use PhpTek\JSONText\Extension\JSONTextExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\CMS\Model\SiteTree;

/**
 * @package silverstripe-jsontext
 */
class MyJSONPageWithBoth extends SiteTree implements TestOnly
{
    private static $db = [
        'MyJSON' => JSONText::class
    ];

    /**
     * @var string
     * @config
     */
    private static $table_name = 'MyJSONPageWithBoth';
    
    /**
     * @var array
     * @config
     */
    private static $extensions = [
        JSONTextExtension::class,
    ];
    
    /**
     * @var array
     */
    private static $json_field_map = [
        'MyJSON' => [
            'Field_From_YML_1',
            'Field_From_YML_2',
        ]
    ];
    
    /**
     * @var array
     */
    public function jsonFieldMap()
    {
        return [
            'MyJSON' => [
                'Field_From_METHOD_1',
                'Field_From_METHOD_2',
            ]
        ];
    }
}
