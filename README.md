# Doctrine Specification Bundle #

## About ##

The DoctrineSpecification Bundle is Symfony bundle for Doctrine Specification library

## Installation ##

Require the bundle and its dependencies with composer:

```bash
$ composer require igdr/doctrine-specification-bundle
```

Register the bundle:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        ...
        new Igdr\DoctrineSpecificationBundle\IgdrDoctrineSpecificationBundle(),
    );
}
```