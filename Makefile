DOCKER:=$(shell docker-compose -v dot 2> /dev/null)

ifdef DOCKER
    APP=docker-compose exec -T app
    TOOLS=docker-compose run --rm tools
endif

CONSOLE=$(APP) bin/console

.PHONY: clean

all: start dependencies db perm

dependencies: composer yarn assets perm

stop:
ifdef DOCKER
	docker-compose down
endif

start:
ifdef DOCKER
	docker-compose up -d
else
	@bash -c "if [ -a vendor/autoload.php ]; then $(CONSOLE) server:run; fi;"
endif

composer:
	$(APP) composer install

yarn:
	$(TOOLS) yarn install

db:
	$(APP) php -r "for(;;){if(@fsockopen('db',3306)){break;}}" # Wait for MariaDB
	$(CONSOLE) doctrine:database:drop --force --if-exists
	$(CONSOLE) doctrine:database:create --if-not-exists
	$(CONSOLE) doctrine:schema:create
	$(CONSOLE) doctrine:fixtures:load -n
	$(CONSOLE) app:content:prepare

clean:
	$(CONSOLE) cache:clear --no-warmup
	$(CONSOLE) cache:clear --no-warmup --env=prod
	$(CONSOLE) cache:clear --no-warmup --env=test
	$(APP) rm -rf var/logs/*
	$(APP) rm -rf var/sessions/*
	$(APP) rm -rf web/built/*

perm:
	$(APP) chmod 777 -R var

assets:
	$(TOOLS) npm run build-dev

watch:
	$(TOOLS) npm run watch

test:
	$(APP) vendor/bin/simple-phpunit
