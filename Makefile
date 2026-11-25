SHELL := /bin/bash
.DEFAULT_GOAL := help

COMPOSE := docker compose -f compose.yaml
PINT_PATHS := app config database public routes tests bootstrap/app.php bootstrap/providers.php
HTTP_PORT ?= 24001
BASE_URL ?= http://localhost:$(HTTP_PORT)
PHP_RUN := $(COMPOSE) run --rm --no-deps -T php


.PHONY: help up stats down destroy check lint fmt static validate test smoke shell exec logs

help: ## Mostra os alvos disponíveis
	@grep -hE '^[a-zA-Z0-9_-]+:.*?## ' $(MAKEFILE_LIST) \
		| awk -F '## ' '{printf "  %-12s %s\n", $$1, $$2}'

up: ## Sobe a stack (gera APP_KEY, build, migrate, seed, health check)
	@test -f .env || cp .env.example .env
	@grep -q '^APP_KEY=base64:' .env || sed -i 's|^APP_KEY=.*|APP_KEY=base64:'"$$(openssl rand -base64 32)"'|' .env
	$(COMPOSE) up -d --build --wait
	@echo "API pronta em $(BASE_URL)/api/v1/bands"

stats: ## Saúde dos containers, migrations, fila e logs recentes
	$(COMPOSE) ps
	@echo "--- rotas ---"
	$(PHP_RUN) php artisan route:list --path=bands
	@echo "--- migrações ---"
	$(PHP_RUN) php artisan migrate:status
	@echo "--- fila ---"
	$(PHP_RUN) php artisan queue:failed
	@echo "--- logs (últimas 20 linhas) ---"
	$(COMPOSE) logs --tail=20 php worker

down: ## Para os serviços preservando volumes e dados
	$(COMPOSE) down

destroy: down ## Para tudo e remove volumes, imagens e redes do projeto
	$(COMPOSE) down --volumes --remove-orphans --rmi all
	docker network prune -f --filter label=com.docker.compose.project=$(PROJECT) >/dev/null || true

check: ## Gate único: composer + estilo + estático + testes
	$(MAKE) validate
	$(MAKE) lint
	$(MAKE) static
	$(MAKE) test

lint: ## Verifica estilo com Pint
	$(PHP_RUN) vendor/bin/pint --test $(PINT_PATHS) --no-ansi

fmt: ## Aplica estilo com Pint
	$(PHP_RUN) vendor/bin/pint $(PINT_PATHS) --no-ansi

static: ## PHPStan/Larastan nível máximo
	$(PHP_RUN) vendor/bin/phpstan analyse --no-progress --memory-limit=512M

optimize: ## Cacheia config/events/routes/views (contracto obrigatório)
	$(PHP_RUN) php artisan optimize

validate: ## Valida composer.json
	$(COMPOSE) run --rm --no-deps -T php composer validate --strict --no-check-publish


test: ## Suíte PHPUnit
	$(COMPOSE) run --rm --no-deps -T \
		-e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: \
		-e DB_JOURNAL_MODE=memory -e QUEUE_CONNECTION=sync -e CACHE_STORE=array -e LOG_STACK=null \
		php sh -c "php artisan optimize:clear --quiet && php artisan test --no-ansi"

smoke: ## Prova os contratos 200/201/404/422 via HTTP
	@curl -fsS $(BASE_URL)/up >/dev/null && echo "health: 200"
	@curl -sS -o /dev/null -w 'index: %{http_code}\n' $(BASE_URL)/api/v1/bands
	@curl -sS -o /dev/null -w 'missing band: %{http_code}\n' $(BASE_URL)/api/v1/bands/999999
	@curl -sS -o /dev/null -w 'invalid create: %{http_code}\n' -H 'Content-Type: application/json' \
		-d '{"name":"","genre":"unknown","formed_in":42}' $(BASE_URL)/api/v1/bands
	@curl -sS -o /dev/null -w 'legacy redirect: %{http_code}\n' $(BASE_URL)/bands

shell: ## Abre shell no container de aplicação
	$(COMPOSE) exec php sh

exec: ## Executa um comando (cmd='php -v')
	@test -n "$(cmd)" || { echo "uso: make exec cmd='php -v'"; exit 2; }
	$(PHP_RUN) sh -c "$(cmd)"

logs: ## Acompanha logs estruturados
	$(COMPOSE) logs -f php worker
