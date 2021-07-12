[![License](https://badgen.net/github/license/synolia/SyliusGDPRPlugin)](https://github.com/synolia/SyliusGDPRPlugin/blob/master/LICENSE)
[![CI](https://github.com/synolia/SyliusGDPRPlugin/actions/workflows/ci.yaml/badge.svg?branch=master)](https://github.com/synolia/SyliusGDPRPlugin/actions/workflows/ci.yaml)
[![Version](https://badgen.net/github/tag/synolia/SyliusGDPRPlugin?label=Version)](https://packagist.org/packages/synolia/sylius-gdpr-plugin)
[![Total Downloads](https://poser.pugx.org/synolia/sylius-gdpr-plugin/downloads)](https://packagist.org/packages/synolia/sylius-gdpr-plugin)

<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

## Features

## Requirements

| | Version |
| :--- | :--- |
| PHP  | 7.3, 7.4, 8.0 |
| Sylius | 1.9, 1.10 |

## Installation

1. Add the bundle and dependencies in your composer.json :

    ```shell
    composer require synolia/sylius-gdpr-plugin --no-scripts
    ```

2. Enable the plugin in your `config/bundles.php` file by add

    ```php
    Synolia\SyliusGDPRPlugin\SynoliaSyliusGDPRPlugin::class => ['all' => true],
    ```

3. Import required config in your `config/packages/_sylius.yaml` file:

    ```yaml
    imports:
        - { resource: "@SynoliaSyliusGDPRPlugin/Resources/config/app/config.yaml" }
    ```

4. Import routing in your `config/routes.yaml` file:

    ```yaml
    synolia_gdpr:
        resource: "@SynoliaSyliusGDPRPlugin/Resources/config/routes/admin/customer.yaml"
        prefix: '/%sylius_admin.path_name%'
    ```

5. Copy plugin migrations to your migrations directory (e.g. `src/Migrations`) and apply them to your database:

    ```shell
    cp -R vendor/synolia/sylius-gdpr-plugin/src/Migrations/* src/Migrations
    bin/console doctrine:migrations:migrate
    ```

6. Clear cache

    ```shell
    bin/console cache:clear
    ```

## Development

See [How to contribute](CONTRIBUTING.md).

## License

This library is under the [EUPL-1.2 license](LICENSE).

## Credits

Developed by [Synolia](https://synolia.com/).
