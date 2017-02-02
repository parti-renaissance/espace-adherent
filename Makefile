APP=docker-compose exec -T app
TOOLS=docker-compose run --rm tools
CONSOLE=$(APP) bin/console

.PHONY: help install start stop deps composer yarn db-create db-fixtures db-update clear-cache clear-all perm clean

help:           ## Show this help
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

install:        ## [start deps db-create, db-fixtures] Setup the project using Docker and docker-compose
install: start deps db-create db-fixtures perm

start:          ## Start the Docker containers
	docker-compose up -d

stop:           ## Stop the Docker containers
	docker-compose down

deps:           ## [composer yarn assets-dev perm] Install the project PHP and JS dependencies
deps: composer yarn assets-dev perm

composer:       ## Install the project PHP dependencies
	$(APP) composer install

yarn:           ## Install the project JS dependencies
	$(TOOLS) yarn install

db-create:      ## Create the database and load the fixtures in it
	$(APP) php -r "for(;;){if(@fsockopen('db',3306)){break;}}" # Wait for MariaDB
	$(CONSOLE) doctrine:database:drop --force --if-exists
	$(CONSOLE) doctrine:database:create --if-not-exists
	$(CONSOLE) doctrine:schema:create

db-fixtures:    ## Reloads the data fixtures for the dev environment
	$(CONSOLE) doctrine:fixtures:load -n

db-update:      ## Update the database structure according to the last changes
	$(CONSOLE) doctrine:schema:update --force

clear-cache:    ## Clear the application cache in development
	$(CONSOLE) cache:clear

clear-all:      ## Deeply clean the application (remove all the cache, the logs, the sessions and the built assets)
	$(CONSOLE) cache:clear --no-warmup
	$(CONSOLE) cache:clear --no-warmup --env=prod
	$(CONSOLE) cache:clear --no-warmup --env=test
	$(APP) rm -rf var/logs/*
	$(APP) rm -rf var/sessions/*
	$(APP) rm -rf web/built
	$(APP) rm -rf supervisord.log supervisord.pid npm-debug.log .tmp

clean:         ## Removes all generated files
	- @make clear-all
	$(APP) rm -rf vendor node_modules

perm:           ## Fix the application cache and logs permissions
	$(APP) chmod 777 -R var

assets:         ## Watch the assets and build their development version on change
	$(TOOLS) yarn watch

assets-dev:     ## Build the development assets
	$(TOOLS) yarn build-dev

assets-prod:    ## Build the production assets
	$(TOOLS) yarn build-prod

test:           ## [test-php test-lintjs test-js] Run the PHP and the Javascript tests
test: test-php test-lintjs test-js

test-php:       ## Run the PHP tests
	$(CONSOLE) --env=test_mysql doctrine:database:drop --force --if-exists
	$(CONSOLE) --env=test_mysql doctrine:database:create --if-not-exists
	$(CONSOLE) --env=test_mysql doctrine:schema:create
	$(CONSOLE) --env=test_sqlite doctrine:database:drop --force --if-exists
	$(CONSOLE) --env=test_sqlite doctrine:database:create --if-not-exists
	$(CONSOLE) --env=test_sqlite doctrine:schema:create
	$(APP) vendor/bin/phpunit

test-lintjs:    ## Lint the Javascript to follow the convention
	$(TOOLS) yarn lint

test-js:        ## Run the Javascript tests
	$(TOOLS) yarn test
