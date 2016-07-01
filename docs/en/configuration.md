# Configuration

As the [README](../../README.md) suggests you can put the module into `postgres` (The default) or `mysql` mode, but only `postgres` works at this time. You can
do this via standard SS config in your project's `mysite/_config/config.yml` file:

    JSONText:
      backend: postgres


Notes: 

The module uses PSR-1 namespacing, so take this into account when calling any part of the module's public API in your own logic.
The exception to the rule is that you can still use "JSONText" without the full namespace when declaring your model's `$db` static
via the built-in magic of `Injector`.
