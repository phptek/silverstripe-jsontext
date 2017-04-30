<?php
/**
 * Custom situation-specific module exceptions.
 * 
 * @package silverstripe-jsontext
 * @author Russell Michell 2016 <russ@theruss.com>
 */

namespace phptek\JSONText\Exceptions;

/**
 * Generic base module exception.
 */
class JSONTextException extends \Exception
{
}

/**
 * Thrown whnever userland issues with method parameters, types and values are
 * discovered.
 */
class JSONTextInvalidArgsException extends JSONTextException
{
}

/**
 * Thrown when data arrives at a routine in an unexpected format or when raw data
 * is found to be in an unusable state.
 */
class JSONTextDataException extends JSONTextException
{
}

/**
 * Thrown when issues with regard to SIlverStripe config are discovered.
 */
class JSONTextConfigException extends JSONTextException
{
}
