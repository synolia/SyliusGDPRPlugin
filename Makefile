.DEFAULT_GOAL := help
SHELL=/bin/bash
COMPOSER_ROOT=composer
TEST_DIRECTORY=tests/Application
INSTALL_DIRECTORY=install/Application
CONSOLE=cd ${TEST_DIRECTORY} && php bin/console -e test
COMPOSER=cd ${TEST_DIRECTORY} && composer
YARN=cd ${TEST_DIRECTORY} && yarn

SYLIUS_VERSION=1.14.0
SYMFONY_VERSION=6.4
PHP_VERSION=8.2
PLUGIN_NAME=synolia/sylius-gdpr-plugin

###
### DEVELOPMENT
### ¯¯¯¯¯¯¯¯¯¯¯

install: sylius ## Install Plugin on Sylius [SYLIUS_VERSION=1.12.0] [SYMFONY_VERSION=6.1] [PHP_VERSION=8.1]
.PHONY: install

reset: ## Remove dependencies
ifneq ("$(wildcard ${TEST_DIRECTORY}/bin/console)","")
	${CONSOLE} doctrine:database:drop --force --if-exists || true
endif
	rm -rf ${TEST_DIRECTORY}
.PHONY: reset

phpunit: phpunit-configure phpunit-run ## Run PHPUnit
.PHONY: phpunit

###
### OTHER
### ¯¯¯¯¯¯

sylius: sylius-standard update-dependencies install-plugin install-sylius
.PHONY: sylius

sylius-standard:
	${COMPOSER_ROOT} create-project sylius/sylius-standard ${TEST_DIRECTORY} "~${SYLIUS_VERSION}" --no-install --no-scripts
	${COMPOSER} config allow-plugins true
	${COMPOSER} require sylius/sylius:"~${SYLIUS_VERSION}"

update-dependencies:
	${COMPOSER} config extra.symfony.require "~${SYMFONY_VERSION}"
	${COMPOSER} require symfony/asset:~${SYMFONY_VERSION} --no-scripts --no-update
ifeq ($(SYLIUS_VERSION)$(SYMFONY_VERSION), 2.0.06.4)
	${COMPOSER} update --no-progress -n --no-scripts
	rm ${TEST_DIRECTORY}/config/packages/csrf.yaml
endif
	${COMPOSER} update --no-progress -n

install-plugin:
	${COMPOSER} config repositories.plugin '{"type": "path", "url": "../../"}'
	${COMPOSER} config extra.symfony.allow-contrib true
	${COMPOSER} config minimum-stability "dev"
	${COMPOSER} config prefer-stable true
	${COMPOSER} req ${PLUGIN_NAME}:* --prefer-source --no-scripts
	cp -r ${INSTALL_DIRECTORY} tests
	cp -r tests/data/* ${TEST_DIRECTORY}/

install-sylius:
	${CONSOLE} sylius:install -n -s default
	${YARN} install
	${YARN} build
	${CONSOLE} cache:clear

phpunit-configure:
	cp phpunit.xml.dist ${TEST_DIRECTORY}/phpunit.xml

phpunit-run:
	cd ${TEST_DIRECTORY} && ./vendor/bin/phpunit

grumphp: ## Run GrumPHP
	vendor/bin/grumphp run

help: SHELL=/bin/bash
help: ## Dislay this help
	@IFS=$$'\n'; for line in `grep -h -E '^[a-zA-Z_#-]+:?.*?##.*$$' $(MAKEFILE_LIST)`; do if [ "$${line:0:2}" = "##" ]; then \
	echo $$line | awk 'BEGIN {FS = "## "}; {printf "\033[33m    %s\033[0m\n", $$2}'; else \
	echo $$line | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m%s\n", $$1, $$2}'; fi; \
	done; unset IFS;
.PHONY: help
