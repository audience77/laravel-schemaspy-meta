# laravel-schemaspy-meta

`laravel-schemaspy-meta` is a tool to generate XML metadata files for [SchemaSpy](https://schemaspy.org/) based on Laravel Eloquent models. 

## Installation

```
composer require --dev audience77/laravel-schemaspy-meta
```

## Generate XML

```
php artisan generate:schemaspy-meta
```

### options

`--modelRootDir`: `string` (default: app/Models)  
Specifies the root directory of your Eloquent model files.  
Subdirectories will be processed recursively.

`--xmlFile`: `string` (default: schemaspy/schemaspy-meta.xml)  
Specifies the output file path for the generated XML file.

`--excludeModelFiles`: `array` (optional)  
Specifies files to be excluded from processing (array format).
