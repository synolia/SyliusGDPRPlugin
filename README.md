[![License](https://badgen.net/github/license/synolia/SyliusGDPRPlugin)](https://github.com/synolia/SyliusGDPRPlugin/blob/master/LICENSE)
[![CI - Analysis](https://github.com/synolia/SyliusGDPRPlugin/actions/workflows/analysis.yaml/badge.svg?branch=master)](https://github.com/synolia/SyliusGDPRPlugin/actions/workflows/analysis.yaml)
[![CI - Sylius](https://github.com/synolia/SyliusGDPRPlugin/actions/workflows/sylius.yaml/badge.svg?branch=master)](https://github.com/synolia/SyliusGDPRPlugin/actions/workflows/sylius.yaml)
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

|        | Version |
|:-------|:--------|
| PHP    | ^8.2    |
| Sylius | ^2.0    |

## Installation

1. Add the bundle and dependencies in your composer.json :

    ```shell
    composer require synolia/sylius-gdpr-plugin --no-scripts
    ```

2. Create required config in `config/packages/gdpr.yaml` file:

    ```yaml
    imports:
        - { resource: "@SynoliaSyliusGDPRPlugin/Resources/config/app/config.yaml" }
    ```

3. Create routing in `config/routes/gdpr.yaml` file:

     ```yaml
     synolia_gdpr:
         resource: "@SynoliaSyliusGDPRPlugin/Resources/config/routes.yaml"
         prefix: '/%sylius_admin.path_name%'
     ```

4. Process translations

    ```bash
    php bin/console translation:extract en SynoliaSyliusGDPRPlugin --dump-messages
    php bin/console translation:extract fr SynoliaSyliusGDPRPlugin --dump-messages
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

   `value` can be null, an array, an int, a string and an [expression language](https://symfony.com/doc/current/reference/formats/expression_language.html)

Example of configuration with dynamic value
```yaml
Sylius\Component\Core\Model\Customer:
   properties:
      firstName:
          value: '@="some-arbitrary-text..." ~ object.getId() ~ "...more-arbitrary-text"'
```
 
### Note:
   > your expression language must starts with `@=` to be evaluated properly 
   
   > variable `object` is the current entity you are dealing with (e.g. in that case `Sylius\Component\Core\Model\Customer`)

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
   
   > or use php attributes `#[AsController]` on your controller and `#[Autowire(AnonymizeCustomerNotLoggedSinceType::class)]` in your constructor for `$formsType` parameter

   - Then create your form processor by implementing Synolia\SyliusGDPRPlugin\Processor\AnonymizerProcessor\AdvancedActionsFormDataProcessorInterface

[There](src/Processor/AdvancedActions/AnonymizeCustomersNotLoggedBeforeProcessor.php) a form processor example

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
