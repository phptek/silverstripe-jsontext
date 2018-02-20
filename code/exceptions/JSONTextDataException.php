<?php
/**
 * Custom situation-specific module exceptions.
 * 
 * @package silverstripe-jsontext
 * @author Russell Michell 2016 <russ@theruss.com>
 */

namespace PhpTek\JSONText\Exception;

/**
 * Thrown when data arrives at a routine in an unexpected format or when raw data
 * is found to be in an unusable state.
 */
class JSONTextDataException extends JSONTextException
{
}
