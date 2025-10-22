
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

# ------------------------------------------------------------
# 📊 Coverage helpers
# ------------------------------------------------------------

.PHONY: coverage
coverage:
	$(EXEC_APP) sh -lc 'XDEBUG_MODE=coverage php -d xdebug.start_with_request=0 bin/phpunit --coverage-html var/coverage/html --coverage-clover var/coverage/clover.xml'
	@echo "\n✅ Rapport généré : var/coverage/html/index.html"

.PHONY: open-coverage
open-coverage:
	@echo "🔍 Ouverture du rapport de couverture..."
	@if [ "$$(uname)" = "Darwin" ]; then \
		open var/coverage/html/index.html; \
	elif [ "$$(uname)" = "Linux" ]; then \
		xdg-open var/coverage/html/index.html >/dev/null 2>&1 || sensible-browser var/coverage/html/index.html; \
	else \
		start var/coverage/html/index.html; \
	fi

# Résumé texte (si défini dans phpunit.xml.dist)
.PHONY: coverage-text
coverage-text:
	$(EXEC_APP) sh -lc 'XDEBUG_MODE=coverage php -d xdebug.start_with_request=0 bin/phpunit'
	@echo "\nOuvre: var/coverage/html/index.html"

# Allumer/éteindre Xdebug par défaut dans le conteneur (utile pour le debug IDE)
.PHONY: xdebug-on
xdebug-on:
	$(EXEC_APP) bash -lc 'echo "XDEBUG_MODE=debug,develop" > /tmp/xdebug_mode && printenv | grep -v ^XDEBUG_MODE= >> /tmp/env && echo "XDEBUG_MODE=debug,develop" >> /tmp/env && cat /tmp/env > /proc/1/environ || true'
	@echo "Xdebug debug activé (pour cette session). Lance ton IDE en écoute sur 9003."

.PHONY: xdebug-off
xdebug-off:
	@echo "Xdebug sera off par défaut; de toute façon nos cibles coverage l’activent à la volée."

.PHONY: bash
bash:
	$(COMPOSE) exec app bash
