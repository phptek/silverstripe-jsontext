<?php

namespace PhpTek\JSONText\Dev\Fixture;

use PhpTek\JSONText\ORM\FieldType\JSONText;
use SilverStripe\Dev\TestOnly;
use Page;

/**
 * @package silverstripe-jsontext
 */
class MyAwesomeJSONPage extends Page implements TestOnly
{
    private static $db = [
        'MyJSON' => JSONText::class
    ];

    /**
     * @var string
     * @config
     */
    private static $table_name = 'MyAwesomeJSONPage';

}
