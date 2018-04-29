<?php

namespace PhpTek\JSONText\Dev\Fixture;

use PhpTek\JSONText\ORM\FieldType\JSONText;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

/**
 * @package silverstripe-jsontext
 */
class MyAwesomeJSONModel extends DataObject implements TestOnly
{
    private static $db = [
        'MyJSON' => JSONText::class
    ];

    /**
     * @var string
     * @config
     */
    private static $table_name = 'MyAwesomeJSONModel';

}
