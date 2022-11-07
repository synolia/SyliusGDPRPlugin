[![License](https://badgen.net/github/license/synolia/SyliusGDPRPlugin)](https://github.com/synolia/SyliusGDPRPlugin/blob/master/LICENSE)
[![CI](https://github.com/synolia/SyliusGDPRPlugin/actions/workflows/ci.yaml/badge.svg?branch=master)](https://github.com/synolia/SyliusGDPRPlugin/actions/workflows/ci.yaml)
[![Version](https://badgen.net/github/tag/synolia/SyliusGDPRPlugin?label=Version)](https://packagist.org/packages/synolia/sylius-gdpr-plugin)
[![Total Downloads](https://poser.pugx.org/synolia/sylius-gdpr-plugin/downloads)](https://packagist.org/packages/synolia/sylius-gdpr-plugin)

<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>
<h1 align="center">Sylius GDPR Plugin</h1>

![Capture](/etc/capture.png "Capture")

## Features

   - Anonymize customer with the GDPR section in the admin customer show.
   - Export customer data with the GDPR section in the admin customer show.

   [Click to see the anonymization configuration](ANONYMIZE_CONFIGURATION.md).
   
   [Click to see the export data configuration](EXPORT_CONFIGURATION.md).

   - Anonymize any entity with command for example :

   ```shell
   php bin/console synolia:gdpr:anonymize --entity='Sylius\Component\Core\Model\Customer' --id=1 
   ```
   Use --help to get more informations

## Requirements

| | Version         |
| :--- |:----------------|
| PHP  | ^7.4, ^8.0 |
| Sylius | ^1.9            |

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

      And

     ```yaml
     synolia_gdpr_controller:
         resource: "@SynoliaSyliusGDPRPlugin/Resources/config/routes/admin/gdpr.yaml"
         prefix: '%sylius_admin.path_name%/gdpr/'
         name_prefix: 'sylius_gdpr_'
     ```

      Or you can add this conf file, which will import the entire route configuration

     ```yaml
     synolia_gdpr:
         resource: "@SynoliaSyliusGDPRPlugin/Resources/config/routes.yaml"
         prefix: '/%sylius_admin.path_name%'
     ```

5. Clear cache

    ```shell
    php bin/console cache:clear
    ```

## Add anonymization configuration

 ```yaml
synolia_sylius_gdpr:
    disable_default_mappings: false # False by default
    anonymization:
        mappings:
            paths:
                - # Your\Paths\To\Mappings\Directory
 ```

   Example of configuration
 ```yaml
Sylius\Component\Core\Model\Address: # Your class path
   properties:
      firstName:
         faker: text # let's see => https://fakerphp.github.io/formatters/
         args: [20] # The associated faker arguments
         prefix: 'anonymized-'
      lastName:
         value: 'Fake lastName'
         prefix: 'anonymized-'
 ```

   Value can be null, an array, an int and a string

## Add form in advanced actions page

There's two steps to add your custom form into the page:

   - Override the controller service by setting the link of your FormType in the $formsType variable

 ```yaml
    Synolia\SyliusGDPRPlugin\Controller\AdvancedActionsController:
        arguments:
            $formsType:
                - 'Synolia\SyliusGDPRPlugin\Form\Type\Actions\AnonymizeCustomerNotLoggedSinceType'
        tags: ['controller.service_arguments']
 ```

   - Then create your form processor by implementing Synolia\SyliusGDPRPlugin\Processor\AnonymizerProcessor\AdvancedActionsFormDataProcessorInterface

[There](src/Processor/AdvancedActions/AnonymizeCustomerNotLoggedBeforeProcessor.php) a form processor example

### Events

- Synolia\SyliusGDPRPlugin\Event\BeforeAnonymize
- Synolia\SyliusGDPRPlugin\Event\AfterAnonymize
- Synolia\SyliusGDPRPlugin\Event\BeforeCustomerAnonymize
- Synolia\SyliusGDPRPlugin\Event\AfterCustomerAnonymize
- Synolia\SyliusGDPRPlugin\Event\BeforeExportCustomerData

## Development

See [How to contribute](CONTRIBUTING.md).

## License

This library is under the [EUPL-1.2 license](LICENSE).

## Credits

Developed by [Synolia](https://synolia.com/).
