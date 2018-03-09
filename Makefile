FIG=docker-compose
RUN=$(FIG) run --rm app
EXEC=$(FIG) exec app
CONSOLE=bin/console
PHPCSFIXER?=$(RUN) php -d memory_limit=1024m vendor/bin/php-cs-fixer

.DEFAULT_GOAL := help
.PHONY: help start stop reset db db-diff db-migrate db-rollback db-load watch clear clean test tu tf tj lint ls ly lt
.PHONY: lj build up perm deps cc phpcs phpcsfix tty

help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'


##
## Project setup
##---------------------------------------------------------------------------

start:            ## Install and start the project
start: build up app/config/parameters.yml db rabbitmq-fabric web/built assets-amp var/public.key perm

stop:             ## Remove docker containers
stop:
	$(FIG) kill
	$(FIG) rm -v --force

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
	$(RUN) $(CONSOLE) cache:clear --no-warmup
	$(RUN) $(CONSOLE) cache:warmup

tty:              ## Run app container in interactive mode
tty:
	$(RUN) /bin/bash

var/public.key:   ## Generate the public key
var/public.key: var/private.key
	$(RUN) openssl rsa -in var/private.key -pubout -out var/public.key

var/private.key:  ## Generate the private key
var/private.key:
	$(RUN) openssl genrsa -out var/private.key 1024

rabbitmq-fabric:
	$(RUN) $(CONSOLE) rabbitmq:setup-fabric

##
## Database
##---------------------------------------------------------------------------

db:             ## Reset the database and load fixtures
db: vendor
	$(RUN) php -r "for(;;){if(@fsockopen('db',3306)){break;}}" # Wait for MySQL
	$(RUN) $(CONSOLE) doctrine:database:drop --force --if-exists
	$(RUN) $(CONSOLE) doctrine:database:create --if-not-exists
	$(RUN) $(CONSOLE) doctrine:database:import -n -- dump/dump-2017.sql
	$(RUN) $(CONSOLE) doctrine:migrations:migrate -n
	$(RUN) $(CONSOLE) doctrine:fixtures:load -n

db-diff:        ## Generate a migration by comparing your current database to your mapping information
db-diff: vendor
	$(RUN) $(CONSOLE) doctrine:migration:diff

db-migrate:     ## Migrate database schema to the latest available version
db-migrate: vendor
	$(RUN) $(CONSOLE) doctrine:migration:migrate -n

db-rollback:    ## Rollback the latest executed migration
db-rollback: vendor
	$(RUN) $(CONSOLE) d:m:e --down $(shell $(RUN) $(CONSOLE) d:m:l) -n

db-load:        ## Reset the database fixtures
db-load: vendor
	$(RUN) $(CONSOLE) doctrine:fixtures:load -n

db-validate:    ## Check the ORM mapping
db-validate: vendor
	$(RUN) $(CONSOLE) doctrine:schema:validate


##
## Assets
##---------------------------------------------------------------------------

watch:          ## Watch the assets and build their development version on change
watch: node_modules
	$(RUN) yarn watch

assets:         ## Build the development version of the assets
assets: node_modules
	$(RUN) yarn build-dev

assets-prod:    ## Build the production version of the assets
assets-prod: node_modules
	$(RUN) yarn build-prod

assets-amp:     ## Build the production version of the AMP CSS
assets-amp: node_modules
	$(RUN) yarn build-amp


##
## Tests
##---------------------------------------------------------------------------

test:           ## Run the PHP and the Javascript tests
test: tu tf tj

tu:             ## Run the PHP unit tests
tu: vendor
	$(EXEC) vendor/bin/phpunit --exclude-group functional || true

tf:             ## Run the PHP functional tests
tf: tfp
	$(EXEC) vendor/bin/behat -vvv || true
	$(EXEC) vendor/bin/phpunit --group functional || true

tfp:            ## Prepare the PHP functional tests
tfp: vendor assets-amp assets-prod
	$(FIG) exec rabbitmq rabbitmqctl add_vhost /test || true
	$(FIG) exec rabbitmq rabbitmqctl set_permissions -p /test guest ".*" ".*" ".*"
	$(EXEC) $(CONSOLE) --env=test_mysql rabbitmq:setup-fabric
	$(EXEC) rm -rf var/cache/test var/cache/test_sqlite var/cache/test_mysql /tmp/data.db app/data/dumped_referents_users || true
	$(EXEC) $(CONSOLE) doctrine:database:create --env=test_sqlite || true
	$(EXEC) $(CONSOLE) doctrine:schema:create --env=test_sqlite || true
	$(EXEC) $(CONSOLE) doctrine:database:drop --force --if-exists --env=test_mysql || true
	$(EXEC) $(CONSOLE) doctrine:database:create --env=test_mysql || true
	$(EXEC) $(CONSOLE) doctrine:database:import --env=test_mysql -n -- dump/dump-2017.sql
	$(EXEC) $(CONSOLE) doctrine:migration:migrate -n --env=test_mysql || true

tj:             ## Run the Javascript tests
tj: node_modules
	$(EXEC) yarn test

lint:           ## Run lint on Twig, YAML, PHP and Javascript files
lint: ls ly lt lj phpcs

ls:             ## Lint Symfony (Twig and YAML) files
ls: ly lt

ly:
	$(RUN) $(CONSOLE) lint:yaml app/config

lt:
	$(RUN) $(CONSOLE) lint:twig templates

lj:             ## Lint the Javascript to follow the convention
lj: node_modules
	$(RUN) yarn lint

ljfix:          ## Lint and try to fix the Javascript to follow the convention
ljfix: node_modules
	$(RUN) yarn lint -- --fix

phpcs:          ## Lint PHP code
phpcs: vendor
	$(PHPCSFIXER) fix --diff --dry-run --no-interaction -v

phpcsfix:       ## Lint and fix PHP code to follow the convention
phpcsfix: vendor
	$(PHPCSFIXER) fix

security-check: ## Check for vulnerable dependencies
security-check: vendor
	$(RUN) vendor/bin/security-checker security:check


##
## Dependencies
##---------------------------------------------------------------------------

deps:           ## Install the project PHP and JS dependencies
deps: vendor web/built


##


# Internal rules

build:
	$(FIG) pull --parallel
	$(FIG) build --force-rm

up:
	$(FIG) up -d --remove-orphans

perm:
	-$(EXEC) chmod -R 777 var app/data/images
	-$(EXEC) chown www-data:root var/public.key var/private.key
	-$(EXEC) chmod 660 var/public.key var/private.key

# Rules from files

vendor: composer.lock
	@$(RUN) composer install

composer.lock: composer.json
	@echo compose.lock is not up to date.

app/config/parameters.yml: app/config/parameters.yml.dist vendor
	@$(RUN) composer run-script post-install-cmd

node_modules: yarn.lock
	@$(RUN) yarn install

yarn.lock: package.json
	@echo yarn.lock is not up to date.

web/built: front node_modules
	@$(RUN) yarn build-dev
