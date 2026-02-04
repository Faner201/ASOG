#!/usr/bin/make

ifeq ($(OS), Windows_NT)
	PLATFORM = windows
	SHELL = cmd.exe
	DEP = dep
	PHPQA = phpqa
	HELP_SUPPORTED = $(shell where printf 2>&1 >nul && where awk 2>&1 >nul && echo yes)
else
	PLATFORM = unix
	SHELL = /bin/bash
	DEP = ./dep
	PHPQA = ./phpqa
	HELP_SUPPORTED = yes
endif

export DOCKER_SCAN_SUGGEST = false

# https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help
help: ## Show this help
ifeq ($(HELP_SUPPORTED), yes)
	@printf "\033[33m%s:\033[0m\n" 'Available commands'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
else
	@echo Add "printf" and "awk" to PATH to display help
endif

.PHONY: create
create: ## Create containers
	docker compose build

.PHONY: destroy
destroy: ## Destroy containers
	docker compose down --rmi all --volumes --remove-orphans

.PHONY: start
start: ## Start containers
	docker compose up --detach --remove-orphans

.PHONY: stop
stop: ## Stop containers
	docker compose stop

.PHONY: install
install: ## Install all application dependencies
	docker compose exec php composer install --ansi

.PHONY: cs-fix
cs-fix: ## Run php-cs-fixer inside Docker
	docker compose run --rm php composer cs-fix

.PHONY: test
test: ## Run PHPUnit tests inside Docker
	docker compose run --rm php ./vendor/bin/phpunit --colors=never

.PHONY: bash
bash: ## Alias for opening bash in the php container
	docker compose exec php bash

.PHONY: console
console: ## Run Symfony console inside Docker (append args to CMD)
	docker compose exec php php bin/console