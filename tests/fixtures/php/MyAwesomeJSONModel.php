<?php

namespace PhpTek\JSONText\Dev\Fixture;

use PhpTek\JSONText\Field\JSONText;
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
}
