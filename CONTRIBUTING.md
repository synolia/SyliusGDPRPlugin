## Installation

From the plugin root directory, run the following commands:

    ```bash
    $ composer install
    $ (cd tests/Application && yarn install)
    $ (cd tests/Application && yarn build)
    $ (cd tests/Application && bin/console assets:install public -e test)
    
    $ (cd tests/Application && bin/console doctrine:database:create -e test)
    $ (cd tests/Application && bin/console doctrine:schema:create -e test)
    
    $ (cd assets && yarn install)
    ```

To be able to setup the plugin database, remember to configure you database credentials 
in `tests/Application/.env` and `tests/Application/.env.test`.

## Usage

### Compilation of Assets

If you change assets (js, scss, ...) in directory `assets/src` you need ton compile changes by using

    ```bash
    $ (cd assets && yarn build)
    ```

In dev mode, assets are compiled in tests/Application/public/bundles/synoliasyliusakeneoplugin in real time

    ```bash
    $ (cd assets && yarn dev)
    ```

### Running code analyse and tests

  - GrumPHP (see configuration [grumphp.yml](grumphp.yml).)
  
    GrumPHP is executed by the Git pre-commit hook, but you can launch it manualy with :

    ```bash
    $ vendor/bin/grumphp run
    ```

  - PHPUnit

    ```bash
    $ vendor/bin/phpunit
    ```

### Opening Sylius with your plugin

- Using `test` environment:

    ```bash
    $ (cd tests/Application && bin/console sylius:fixtures:load -e test)
    $ (cd tests/Application && bin/console server:run -d public -e test)
    ```
    
- Using `dev` environment:

    ```bash
    $ (cd tests/Application && bin/console sylius:fixtures:load -e dev)
    $ (cd tests/Application && bin/console server:run -d public -e dev)
    ```
