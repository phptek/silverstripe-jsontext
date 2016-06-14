# Configuration

As the [README](../../README.md) suggests, you can put the module into `postgres` (The default) or `mysql` mode, but only `postgres` works at this time. You can
do this via standard SS config in your project's `mysite/_config/config.yml` file thus:

    JSONText:
      backend: postgres


Note: The module default is to use `postgres` which is also the only backend that will work at the moment.
