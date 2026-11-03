# expcomponents-archive

Create, modify and extract archive files: Tar (ustar, v7, pax and GNU) and Zip.

This is a maintained fork of [zetacomponents/Archive](https://github.com/zetacomponents/Archive)
by 7x (se7en.x), kept for Exponential and anyone else who still uses the Zeta Components
Archive component on current PHP versions. All credit for the component goes to the
Zeta Components authors listed in `composer.json` and `CREDITS`; the upstream copyright
and the Apache License 2.0 (`LICENSE.txt`) are unchanged.

## What is different from upstream

- Runs on PHP 7.4 to 8.5 without deprecation notices (see `ChangeLog`).
- The package is `se7enxweb/expcomponents-archive` and replaces `zetacomponents/archive`,
  so a project that requires the upstream package gets this one in its place.
- The class names (`ezcArchive*`) and the API are unchanged.

## Installation

```bash
composer require se7enxweb/expcomponents-archive
```

## Tests

```bash
composer update
vendor/bin/phpunit
```

The suite runs on every push for PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 and 8.5, and any
deprecation fails it. Some tests call the `tar`, `gzip` and `bzip2` executables and
compare archives byte for byte with the output of GNU tar 1.34; GNU tar 1.35 leaves the
device number fields of regular files empty, so those tests fail with it.

Known limitations of the component are listed in `README`.
