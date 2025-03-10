
# Makefile for schollccib project

# Variables
COMPOSE = docker-compose
EXEC_APP = $(COMPOSE) exec app

# Commands
.PHONY: build
build:
	$(COMPOSE) up --build

.PHONY: install-composer
install-composer:
	$(EXEC_APP) composer install

.PHONY: update-structure
update-structure:
	$(EXEC_APP) php bin/console doctrine:migrations:diff

.PHONY: migrate
migrate:
	$(EXEC_APP) php bin/console doctrine:migrations:migrate

.PHONY: install-node-modules
install-node-modules:
	$(EXEC_APP) yarn install
	$(EXEC_APP) npm install @popperjs/core

.PHONY: dev
dev:
	$(EXEC_APP) php bin/console fos:js-routing:dump --format=json --target=public/js/fos_js_routes.json
	$(EXEC_APP) yarn run dev --watch

.PHONY: clear-cache
clear-cache:
	$(EXEC_APP) php bin/console cache:clear

.PHONY: test
test:
	$(EXEC_APP) php bin/phpunit

.PHONY: ps
ps:
	$(COMPOSE) ps

.PHONY: down
down:
	$(COMPOSE) down --rmi all

.PHONY: prune
prune:
	docker system prune -af

.PHONY: clean-node-modules
clean-node-modules:
	$(EXEC_APP) rm -rf node_modules yarn.lock

.PHONY: make-migration
make-migration:
	$(EXEC_APP) php bin/console make:migration

# Default target
.PHONY: all
all: build install-composer migrate install-node-modules dev

.PHONY: start
start:
	$(COMPOSE) up -d

.PHONY: stop
stop:
	$(COMPOSE) down
