TOOLS = docker-compose run --rm tools
CONSOLE = ./bin/console

all: boot install assets run

boot:
	docker-compose up -d

install:
	$(TOOLS) sh -c "composer install && yarn install"

assets:
	$(TOOLS) sh -c "npm run build-dev"

run:
	$(CONSOLE) server:run

watch:
	$(TOOLS) npm run watch

test:
	$(TOOLS) ./vendor/bin/simple-phpunit
