# Contributing

## Development workflow

1. install dependencies
2. run tests
3. run formatting
4. keep changes focused and backward compatible

## Quality rules

- do not edit framework or vendor files
- prefer Filament extension points over hacks
- keep the package generic
- keep host-project behavior outside the package when possible
- register CSS and JS through Filament assets

## Commands

```bash
vendor/bin/phpunit
vendor/bin/pint
```
