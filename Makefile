DOCKER_COMPOSE?=docker-compose
RUN=$(DOCKER_COMPOSE) run --rm app
EXEC?=$(DOCKER_COMPOSE) exec app
CONSOLE=bin/console
PHPCSFIXER?=$(EXEC) php -d memory_limit=1024m vendor/bin/php-cs-fixer

.DEFAULT_GOAL := help
.PHONY: help start stop reset db db-diff db-migrate db-rollback db-load watch clear clean test tu tf tj lint ls ly lt
.PHONY: lj build up perm deps cc phpcs phpcsfix tty tfp tfp-rabbitmq tfp-db test-behat test-phpunit-functional
.PHONY: wait-for-rabbitmq wait-for-db security-check

help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'


##
## Project setup
##---------------------------------------------------------------------------

start:            ## Install and start the project
start: build up app/config/parameters.yml db rabbitmq-fabric web/built assets-amp var/public.key perm

stop:             ## Remove docker containers
stop:
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) rm -v --force

reset:            ## Reset the whole project
reset: stop start

clear:            ## Remove all the cache, the logs, the sessions and the built assets
clear: perm
	-$(EXEC) rm -rf var/cache/*
	-$(EXEC) rm -rf var/sessions/*
	-$(EXEC) rm -rf supervisord.log supervisord.pid npm-debug.log .tmp
	-$(EXEC) $(CONSOLE) redis:flushall -n
	rm -rf var/logs/*
	rm -rf web/built
	rm var/.php_cs.cache

clean:            ## Clear and remove dependencies
clean: clear
	rm -rf vendor node_modules

cc:               ## Clear the cache in dev env
cc:
	$(EXEC) $(CONSOLE) cache:clear --no-warmup
	$(EXEC) $(CONSOLE) cache:warmup

tty:              ## Run app container in interactive mode
tty:
	$(RUN) /bin/bash

var/public.key:   ## Generate the public key
var/public.key: var/private.key
	$(EXEC) openssl rsa -in var/private.key -pubout -out var/public.key

var/private.key:  ## Generate the private key
var/private.key:
	$(EXEC) openssl genrsa -out var/private.key 1024

wait-for-rabbitmq:
	$(EXEC) php -r "set_time_limit(60);for(;;){if(@fsockopen('rabbitmq',5672)){break;}echo \"Waiting for RabbitMQ\n\";sleep(1);}"

rabbitmq-fabric:
rabbitmq-fabric: wait-for-rabbitmq
	$(EXEC) $(CONSOLE) rabbitmq:setup-fabric

##
## Database
##---------------------------------------------------------------------------

wait-for-db:
	$(EXEC) php -r "set_time_limit(60);for(;;){if(@fsockopen('db',3306)){break;}echo \"Waiting for MySQL\n\";sleep(1);}"

db:             ## Reset the database and load fixtures
db: vendor wait-for-db
	$(EXEC) $(CONSOLE) doctrine:database:drop --force --if-exists
	$(EXEC) $(CONSOLE) doctrine:database:create --if-not-exists
	$(EXEC) $(CONSOLE) doctrine:database:import -n -- dump/dump-2017.sql
	$(EXEC) $(CONSOLE) doctrine:migrations:migrate -n
	$(EXEC) $(CONSOLE) doctrine:fixtures:load -n

db-diff:        ## Generate a migration by comparing your current database to your mapping information
db-diff: vendor wait-for-db
	$(EXEC) $(CONSOLE) doctrine:migration:diff

db-migrate:     ## Migrate database schema to the latest available version
db-migrate: vendor wait-for-db
	$(EXEC) $(CONSOLE) doctrine:migration:migrate -n

db-rollback:    ## Rollback the latest executed migration
db-rollback: vendor wait-for-db
	$(EXEC) $(CONSOLE) doctrine:migration:migrate prev -n

db-load:        ## Reset the database fixtures
db-load: vendor wait-for-db
	$(EXEC) $(CONSOLE) doctrine:fixtures:load -n

db-validate:    ## Check the ORM mapping
db-validate: vendor wait-for-db
	$(EXEC) $(CONSOLE) doctrine:schema:validate


##
## Assets
##---------------------------------------------------------------------------

watch:          ## Watch the assets and build their development version on change
watch: node_modules
	$(EXEC) yarn watch

assets:         ## Build the development version of the assets
assets: node_modules
	$(EXEC) yarn build-dev

assets-prod:    ## Build the production version of the assets
assets-prod: node_modules
	$(EXEC) yarn build-prod

assets-amp:     ## Build the production version of the AMP CSS
assets-amp: node_modules
	$(EXEC) yarn build-amp


##
## Tests
##---------------------------------------------------------------------------

test:                    ## Run the PHP and the Javascript tests
test: tu tf tj

test-behat:              ## Run behat tests
test-behat:
	$(EXEC) vendor/bin/behat -vvv

test-phpunit-functional: ## Run phpunit fonctional tests
test-phpunit-functional:
	$(EXEC) vendor/bin/phpunit --group functional

tu:                      ## Run the PHP unit tests
tu: vendor app/config/assets_version.yml
	$(EXEC) vendor/bin/phpunit --exclude-group functional

tf:                      ## Run the PHP functional tests
tf: tfp test-behat test-phpunit-functional

tfp:                     ## Prepare the PHP functional tests
tfp: assets-amp assets-prod vendor perm tfp-rabbitmq tfp-db

tfp-rabbitmq:            ## Init RabbitMQ setup for tests
tfp-rabbitmq: wait-for-rabbitmq
	$(DOCKER_COMPOSE) exec rabbitmq rabbitmqctl add_vhost /test || true
	$(DOCKER_COMPOSE) exec rabbitmq rabbitmqctl set_permissions -p /test guest ".*" ".*" ".*"
	$(EXEC) $(CONSOLE) --env=test rabbitmq:setup-fabric

tfp-db:                  ## Init databases for tests
tfp-db: wait-for-db
	$(EXEC) rm -rf /tmp/data.db app/data/dumped_referents_users || true
	$(EXEC) $(CONSOLE) doctrine:database:create --env=test_sqlite
	$(EXEC) $(CONSOLE) doctrine:schema:create --env=test_sqlite
	$(EXEC) $(CONSOLE) doctrine:database:drop --force --if-exists --env=test_mysql
	$(EXEC) $(CONSOLE) doctrine:database:create --env=test_mysql
	$(EXEC) $(CONSOLE) doctrine:database:import --env=test_mysql -n -- dump/dump-2017.sql
	$(EXEC) $(CONSOLE) doctrine:migration:migrate -n --env=test_mysql
	$(EXEC) $(CONSOLE) doctrine:schema:validate --env=test_mysql

tj:                      ## Run the Javascript tests
tj: node_modules
	$(EXEC) yarn test

lint:                    ## Run lint on Twig, YAML, PHP and Javascript files
lint: ls ly lt lj phpcs

ls:                      ## Lint Symfony (Twig and YAML) files
ls: ly lt

ly:
	$(EXEC) $(CONSOLE) lint:yaml app/config

lt:
	$(EXEC) $(CONSOLE) lint:twig templates

lj:                      ## Lint the Javascript to follow the convention
lj: node_modules
	$(EXEC) yarn lint

ljfix:                   ## Lint and try to fix the Javascript to follow the convention
ljfix: node_modules
	$(EXEC) yarn lint -- --fix

phpcs:                   ## Lint PHP code
phpcs: vendor
	$(PHPCSFIXER) fix --diff --dry-run --no-interaction -v

phpcsfix:                ## Lint and fix PHP code to follow the convention
phpcsfix: vendor
	$(PHPCSFIXER) fix

security-check:          ## Check for vulnerable dependencies
security-check: vendor
	$(EXEC) vendor/bin/security-checker security:check


##
## Dependencies
##---------------------------------------------------------------------------

deps:           ## Install the project PHP and JS dependencies
deps: vendor web/built


##


# Internal rules

build:
	$(DOCKER_COMPOSE) pull --parallel --ignore-pull-failures
	$(DOCKER_COMPOSE) build --force-rm

up:
	$(DOCKER_COMPOSE) up -d --remove-orphans

perm:
	$(EXEC) chmod -R 777 var app/data/images
	$(EXEC) chown -R www-data:root var
	$(EXEC) chmod 660 var/public.key var/private.key

# Rules from files

vendor: composer.lock
	$(EXEC) composer install -n

composer.lock: composer.json
	@echo compose.lock is not up to date.

app/config/parameters.yml: app/config/parameters.yml.dist vendor
	$(EXEC) composer -n run-script post-install-cmd

node_modules: yarn.lock
	$(EXEC) yarn install

yarn.lock: package.json
	@echo yarn.lock is not up to date.

web/built: front node_modules
	$(EXEC) yarn build-dev

app/config/assets_version.yml:
	 $(EXEC) yarn build-prod
