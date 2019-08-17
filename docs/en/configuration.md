# Configuration

As the [README](../../README.md) suggests you can put the module into `postgres` (The default) or `mysql` mode, but only `postgres` works at this time. You can
do this via standard YML config in your project's `_config` folder:

    PhpTek\JSONText\ORM\FieldType\JSONText:
      backend: postgres

To extend your project's `DataObject` subclasses to allow multiple form fields to write to a single database field, simply add the extension
in the normal way, for example:

    SilverStripe\CMS\Model\SiteTree:
      extensions:
        - PhpTek\JSONText\Extension\JSONTextExtension


Notes: 

The module uses PSR-1 namespacing, so take this into account when calling any part of the module's public API in your own logic.
The exception to the rule is that you can still use "JSONText" without the full namespace when declaring your model's `$db` static
via the built-in magic of `Injector`.
