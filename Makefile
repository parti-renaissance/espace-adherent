TOOLS=docker-compose run --rm tools
CONSOLE=$(TOOLS) bin/console

.PHONY: help install start stop deps composer yarn db-create db-fixtures db-update db-diff db-reset clear-cache clear-all perm clean cc

all: install

help:           ## Show this help
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

install:        ## [start deps db-create, db-fixtures] Setup the project using Docker and docker-compose
install: start deps db-reset perm

start:          ## Start the Docker containers
	docker-compose up -d

stop:           ## Stop the Docker containers
	docker-compose down

deps:           ## [composer yarn assets-dev perm] Install the project PHP and JS dependencies
deps: composer yarn assets-dev perm

composer:       ## Install the project PHP dependencies
	$(TOOLS) composer install

yarn:           ## Install the project JS dependencies
	$(TOOLS) yarn install

db-create:      ## Create the database
	$(TOOLS) php -r "for(;;){if(@fsockopen('db',3306)){break;}}" # Wait for MariaDB
	$(CONSOLE) doctrine:database:drop --force --if-exists
	$(CONSOLE) doctrine:database:create --if-not-exists
	$(CONSOLE) doctrine:migrations:migrate -n

db-fixtures:    ## Reloads the data fixtures for the dev environment
	$(CONSOLE) doctrine:fixtures:load -n

db-update:      ## Update the database structure according to the last changes
	$(CONSOLE) doctrine:migration:migrate -n

db-diff:        ## Generate a migration by comparing your current database to your mapping information.
	$(CONSOLE) doctrine:migration:diff

db-reset:       ## Create the database and load the fixtures in it
db-reset: db-create db-fixtures

clear-cache:    ## Clear the application cache in development
	$(CONSOLE) cache:clear

cc:             ## Clear-cache and fix perm
cc: clear-cache perm

clear-all:      ## Deeply clean the application (remove all the cache, the logs, the sessions and the built assets)
	$(CONSOLE) cache:clear --no-warmup
	$(CONSOLE) cache:clear --no-warmup --env=prod
	$(CONSOLE) cache:clear --no-warmup --env=test
	$(TOOLS) rm -rf var/logs/*
	$(TOOLS) rm -rf var/sessions/*
	$(TOOLS) rm -rf web/built
	$(TOOLS) rm -rf supervisord.log supervisord.pid npm-debug.log .tmp

clean:          ## Removes all generated files
	- @make clear-all
	$(TOOLS) rm -rf vendor node_modules

perm:           ## Fix the application cache and logs permissions
	$(TOOLS) chmod 777 -R var

assets:         ## Watch the assets and build their development version on change
	$(TOOLS) yarn watch

assets-dev:     ## Build the development assets
	$(TOOLS) yarn build-dev

assets-prod:    ## Build the production assets
	$(TOOLS) yarn build-prod

test:           ## [test-php test-lintjs test-js] Run the PHP and the Javascript tests
test: test-php test-lintjs test-js

test-php:       ## Run the PHP tests
	$(TOOLS) vendor/bin/phpunit

test-lintjs:    ## Lint the Javascript to follow the convention
	$(TOOLS) yarn lint

test-js:        ## Run the Javascript tests
	$(TOOLS) yarn test
