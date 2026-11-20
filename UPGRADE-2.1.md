# UPGRADE FROM `v2.0.X` TO `v2.1.0`

## Requirements

* Sylius 2.0 and 2.1 are no longer supported (end of support): the minimum required version is now Sylius `2.2.13`.
* PHP 8.2 is no longer supported: the minimum required version is now PHP 8.3, as required by Sylius 2.2.

## Behaviour changes

* Union property types now resolve to the type that never casts the anonymized value, with the same rule on every
  Symfony version: `object` > `array` > `string` > `float` > `int` > `bool`.
  On Symfony 7.4+, an `int|string` property now resolves to `string` instead of `int`: the anonymized value is kept
  as is (e.g. `anonymized-4f3a9c`) instead of being cast to `0`.
* `float` and `bool` properties now receive the cast value. Previously, a property typed only through PHPDoc
  (e.g. `/** @var float */`) received the raw string (e.g. `'12.5'` instead of `12.5`), and a native `float`
  property with a non-numeric value (e.g. a prefixed one) raised an exception instead of being set to `0.0`.
