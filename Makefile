DOCKER_COMPOSE_ARGS?=
DOCKER_COMPOSE?=docker compose $(DOCKER_COMPOSE_ARGS)
RUN_ARGS?=--rm
RUN=$(DOCKER_COMPOSE) run $(RUN_ARGS) app
RUN_NODE?=$(DOCKER_COMPOSE) run --rm node
EXEC_ARGS?=
EXEC?=$(DOCKER_COMPOSE) exec $(EXEC_ARGS) app
COMPOSER=$(EXEC) composer
CONSOLE=$(EXEC) bin/console
PHPCSFIXER?=$(EXEC) php -d memory_limit=1024m vendor/bin/php-cs-fixer
BEHAT=$(EXEC) vendor/bin/behat
BEHAT_ARGS?=-vvv
PHPUNIT=$(EXEC) vendor/bin/phpunit
PHPUNIT_ARGS?=
DOCKER_FILES=$(shell find ./docker/ -type f -name '*')
CONTAINERS?=

.DEFAULT_GOAL := help
.PHONY: help start stop reset db db-init db-diff db-diff-dump db-migrate db-rollback db-load watch clear clean test tu tf tj lint ls ly lt lintfix
.PHONY: lj build up perm deps cc phpcs phpcsfix phplint tty tfp tfp-rabbitmq tfp-db tfp-db-init test-behat test-phpunit-functional
.PHONY: wait-for-rabbitmq wait-for-db security-check rm-docker-dev.lock assets

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Project setup
##---------------------------------------------------------------------------

start: build up assets db keys perm cc ## Install and start the project

start-mac: build up web-built-mac db keys.key perm cc ## Install and start the project

stop:                                                                                                  ## Remove docker containers
	$(DOCKER_COMPOSE) kill || true
	$(DOCKER_COMPOSE) rm -v --force

reset: stop rm-docker-dev.lock start

clear: perm rm-docker-dev.lock                                                                                             ## Remove all the cache, the logs, the sessions and the built assets
	-$(EXEC) rm -rf var/cache/*
	-$(EXEC) rm -rf var/sessions/*
	-$(EXEC) rm -rf supervisord.log supervisord.pid npm-debug.log .tmp
	-$(CONSOLE) redis:flushall -n
	rm -rf var/logs/*
	rm -rf public/built
	rm -rf var/.php_cs

clean: clear                                                                                           ## Clear and remove dependencies
	rm -rf vendor node_modules

cc:                                                                                                    ## Clear the cache in dev env
	$(CONSOLE) cache:clear --no-warmup
	$(CONSOLE) cache:warmup

tfp-cc:                                                                                                ## Clear the cache in test env
	$(CONSOLE) cache:clear --env=test --no-warmup
	$(CONSOLE) cache:warmup --env=test

tty:                                                                                                   ## Run app container in interactive mode
	$(RUN) /bin/bash

keys:                                                                                                  ## Generate the public and private keys
	$(EXEC) openssl genrsa -out var/private.key 2048
	$(EXEC) openssl rsa -in var/private.key -pubout -out var/public.key

wait-for-rabbitmq:
	$(EXEC) php -r "set_time_limit(60);for(;;){if(@fsockopen('rabbitmq',5672)){break;}echo \"Waiting for RabbitMQ\n\";sleep(1);}"

##
## Database
##---------------------------------------------------------------------------


wait-for-db:
	$(EXEC) php -r "set_time_limit(60);for(;;){if(@fsockopen('db',3306)){break;}echo \"Waiting for MySQL\n\";sleep(1);}"

db: db-init                                                                                 ## Reset the database and load fixtures
	$(CONSOLE) doctrine:fixtures:load -n --no-debug

db-init: vendor wait-for-db                                                                            ## Init the database
	$(CONSOLE) doctrine:database:drop --force --if-exists --no-debug
	$(CONSOLE) doctrine:database:create --if-not-exists --no-debug
	$(DOCKER_COMPOSE) exec -T db mysql -uroot -proot --quick enmarche < ./dump/dump-2024.sql
	$(CONSOLE) doctrine:migrations:migrate -n --no-debug

db-diff: vendor wait-for-db                                                                            ## Generate a migration by comparing your current database to your mapping information
	$(CONSOLE) doctrine:migration:diff --formatted --no-debug

db-diff-dump: vendor wait-for-db                                                                       ## Generate a migration by comparing your current database to your mapping information and display it in console
	$(CONSOLE) doctrine:schema:update --dump-sql

db-migrate: vendor wait-for-db                                                                         ## Migrate database schema to the latest available version
	$(CONSOLE) doctrine:migration:migrate -n --no-debug

db-rollback: vendor wait-for-db                                                                        ## Rollback the latest executed migration
	$(CONSOLE) doctrine:migration:migrate prev -n --no-debug

db-load: vendor wait-for-db                                                                            ## Reset the database fixtures
	$(CONSOLE) doctrine:fixtures:load -n --no-debug

db-validate: vendor wait-for-db                                                                        ## Check the ORM mapping
	$(CONSOLE) doctrine:schema:validate --no-debug

##
## Assets
##---------------------------------------------------------------------------

watch: node_modules                                                                                    ## Watch the assets and build their development version on change
	$(RUN_NODE) yarn watch

watch-mac:
	yarn watch

assets: node_modules                                                                                   ## Build the development version of the assets
	$(RUN_NODE) yarn build-dev

assets-prod: node_modules                                                                              ## Build the production version of the assets
	$(RUN_NODE) yarn build-prod

##
## Tests
##---------------------------------------------------------------------------

test: tu tf tj                                                                                         ## Run the PHP and the Javascript tests

test-behat:                                                                                            ## Run behat tests
	$(BEHAT) $(BEHAT_ARGS)

test-phpunit:                                                                                          ## Run phpunit tests
	$(PHPUNIT) $(PHPUNIT_ARGS)

test-debug:                                                                                            ## Run tests with debug group/tags
	$(PHPUNIT) --group debug
	$(BEHAT) -vvv --tags debug

test-phpunit-functional:                                                                               ## Run phpunit fonctional tests
	$(PHPUNIT) --group functional

tu: vendor                                                                                             ## Run the PHP unit tests
	$(PHPUNIT) --exclude-group functional

tf: tfp test-behat test-phpunit-functional                                                             ## Run the PHP functional tests

tfp: assets-prod vendor perm tfp-rabbitmq tfp-db                                            ## Prepare the PHP functional tests

tfp-rabbitmq: wait-for-rabbitmq                                                                        ## Init RabbitMQ setup for tests
	$(DOCKER_COMPOSE) exec $(EXEC_ARGS) rabbitmq rabbitmqctl add_vhost /test || true
	$(DOCKER_COMPOSE) exec $(EXEC_ARGS) rabbitmq rabbitmqctl set_permissions -p /test guest ".*" ".*" ".*"

tfp-db-init: wait-for-db                                                                                    ## Init databases for tests
	$(CONSOLE) doctrine:database:drop --force --if-exists --env=test --no-debug
	$(CONSOLE) doctrine:database:create --env=test --no-debug
	$(DOCKER_COMPOSE) exec -T db mysql -uroot -proot --quick enmarche_test < ./dump/dump-2024.sql
	$(CONSOLE) doctrine:migration:migrate -n --no-debug --env=test
	$(CONSOLE) doctrine:schema:validate --no-debug --env=test

tfp-db: tfp-db-init
	$(CONSOLE) doctrine:fixtures:load --no-debug --env=test -n

tj: node_modules                                                                                       ## Run the Javascript tests
	$(RUN_NODE) yarn test

lint: ls lj phpcs                                                                                ## Run lint on Twig, YAML, PHP and Javascript files

ls: ly lt lc phpstan                                                                                             ## Lint Symfony (Twig and YAML) files

ly:
	$(CONSOLE) lint:yaml config --parse-tags

lt:
	$(CONSOLE) lint:twig templates
	$(EXEC) vendor/bin/twig-cs-fixer

ltfix:
	$(EXEC) vendor/bin/twig-cs-fixer --fix

lc:
	$(CONSOLE) lint:container

lj: node_modules                                                                                       ## Lint the Javascript to follow the convention
	$(RUN_NODE) yarn lint

ljfix: node_modules                                                                                    ## Lint and try to fix the Javascript to follow the convention
	$(RUN_NODE) yarn lint:fix

lp: node_modules                                                                                    ## Lint and try to fix the Javascript to follow the convention
	$(RUN_NODE) yarn prettier

lpfix: node_modules                                                                                    ## Lint and try to fix the Javascript to follow the convention
	$(RUN_NODE) yarn prettier:fix

lintfix: phpcsfix ljfix lpfix ltfix

phpcs: vendor                                                                                          ## Lint PHP code
	$(PHPCSFIXER) fix --diff --dry-run --no-interaction -v

phpcsfix: vendor                                                                                       ## Lint and fix PHP code to follow the convention
	$(PHPCSFIXER) fix

phpstan: vendor
	$(EXEC) vendor/bin/phpstan analyse

phplint: phpcsfix phpstan

security-check: vendor                                                                                 ## Check for vulnerable dependencies
	$(EXEC) local-php-security-checker --path=/srv/app


##
## Dependencies
##---------------------------------------------------------------------------

deps: vendor assets                                                                                ## Install the project PHP and JS dependencies

# Internal rules

build: docker-dev.lock

docker-dev.lock: $(DOCKER_FILES)
	$(DOCKER_COMPOSE) pull --ignore-pull-failures
	$(DOCKER_COMPOSE) build --force-rm --pull
	touch docker-dev.lock

rm-docker-dev.lock:
	rm -f docker-dev.lock

perm:
	$(EXEC) chmod -R 777 app/data/images app/data/files
	$(EXEC) chmod 664 var/public.key var/private.key
	$(EXEC) chown -R www-data:www-data var/cache var/log

# Rules from files

vendor: vendor/composer/installed.php

vendor/composer/installed.php: composer.lock
	$(COMPOSER) install -n

composer.lock: composer.json
	@echo composer.lock is not up to date.

node_modules: yarn.lock
	$(RUN_NODE) yarn install

yarn.lock: package.json
	@echo yarn.lock is not up to date.

web-built-mac:
	yarn install
	yarn build-dev

##
## Containers
##---------------------------------------------------------------------------

build-app:
	$(DOCKER_COMPOSE) build app

up:
	$(DOCKER_COMPOSE) up -d --remove-orphans $(CONTAINERS)

up-no-deps:
	$(DOCKER_COMPOSE) up -d --remove-orphans --no-deps $(CONTAINERS)
