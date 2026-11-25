# Plano — Bands API (DDD-lite com Laravel 13)

> Documento de execução. Critérios de aceite verificáveis. Constituição: `AGENTS.md`.
> Referências: `_ref/dio-laravel-api/` (contrato legado), `_ref/blake3-for-php/` (rigor).

## 1. Objetivo

Recriar a API da live DIO com o mesmo contrato de domínio (`bands`), mas com:
PHP 8.5.13, Laravel 13.x, SQLite, Caddy, Docker Compose, DDD-lite, fila database,
logs estruturados com correlação, erros RFC 9457, painel Blade visível e quality gates desde o dia zero.

Não objetivos: autenticação, gRPC, Octane, Pulse/Telescope/Horizon, multi-tenancy,
migração de dados legados.

## 2. Contrato público (congelado)

| Método | Rota | Sucesso | Erro |
|---|---|---|---|
| GET | `/api/v1/bands` | 200, array de bandas (paginado por `per_page`) | — |
| GET | `/api/v1/bands/{id}` | 200 | 404 `band-not-found` |
| GET | `/api/v1/bands/gender/{gender}` | 200 | 404 `no-bands-for-gender` |
| POST | `/api/v1/bands` | 201 + `Location` | 422 `validation-failed` |

Aliases legados (`/bands`, `/bands/store`, `/bands/gender/{gender}`, `/bands/{id}`)
redirecionam com 308 via `LegacyBandController` — action de classe, compatível com `route:cache`.

Banda: `id`, `name`, `genre` (enum), `formed_in` (ano), `created_at`, `updated_at`.

Todo erro: `Content-Type: application/problem+json`, com `type`, `title`, `status`,
`detail`, `instance`, `code`, `request_id` e `errors` quando aplicável. Erros não
previstos também viram `internal-error` (500) sem vazar stack trace.

Painel: `GET /` — Blade + CSS puro, filtro por gênero via GET, sem JavaScript.

## 3. Estrutura final

```
app/Domain/Band/          enum, value objects, entidade, contrato de repositório, erros, eventos
app/Application/Band/     Actions + comandos imutáveis (inclui BrowseBands para o painel)
app/Infrastructure/Band/  implementação Eloquent do repositório + mapper
app/Infrastructure/Http/  controllers, requests, resources, middleware, Problem
app/Infrastructure/Queue/ jobs e adaptador de eventos de domínio
app/Providers/            composition root (DI + Queue::route)
config/                   api-only, customizado (app, cache, database, logging, queue)
database/                 migrations + seeder determinístico + sqlite/
docker/                   php-fpm.Dockerfile, www.conf, php.ini, Caddyfile
resources/views/          layout + dashboard em Blade
public/css/               CSS puro do painel
docs/adr/                 decisões arquiteturais
tests/                    Unit + Feature (inclui optimize e problem details)
Makefile                  interface única
```

## 4. Fases

### Fase 0 — Fundação
- [x] Skeleton Laravel 13.44.1 via Composer.
- [x] Larastan 3.15 / PHPStan 2.3.7 + strict/deprecation rules.
- [x] PHP 8.5.13 fixado por digest (FPM+CLI), Caddy 2.11.9 fixado por digest.
- [x] Portas 24001–24004, nenhuma porta HTTP/TLS padrão em uso.
- [x] Constituição, plano e ADRs versionados.

Aceite: `make up` sobe a stack e `make stats` reporta todos os serviços healthy.

### Fase 1 — Domínio puro
- [x] `Genre` enum com os 9 gêneros e labels.
- [x] Value Objects com validação no construtor (`BandName`, `FormedIn`, `BandId`).
- [x] Entidade `Band` imutável (`readonly`) com `withIdentity()`/`withEvents()`.
- [x] `BandRepository` e `DomainEventDispatcher` como contratos; nenhum Eloquent no domínio.

Aceite: testes Unit verdes; PHPStan max sem erros no domínio.

### Fase 2 — Casos de uso e persistência
- [x] Actions: listar, buscar por id, listar por gênero, criar e navegar (painel).
- [x] Migration SQLite com índice em `genre`, WAL e `busy_timeout`.
- [x] Repositório Eloquent + mapper tipado.
- [x] Seeder determinístico (5 bandas).

Aceite: integração com banco em memória verde; criação persistida e consultável.

### Fase 3 — HTTP, painel e contrato de erro
- [x] Rotas `/api/v1` + aliases legados 308 via controller cacheável.
- [x] Form Requests com validação estrita (`integer:strict`).
- [x] Resource como única fronteira de serialização da API.
- [x] `Problem` RFC 9457 centralizado, incluindo catch-all para erros não previstos.
- [x] Middleware `AssignRequestId` + logs estruturados em JSON.
- [x] Dashboard `GET /` em Blade, CSS puro, GET filter, semântica e acessibilidade.

Aceite: contrato provado por feature tests, `curl` e inspeção visual no navegador.

### Fase 4 — Filas e observabilidade
- [x] Evento `BandCreated` despachado via porta `DomainEventDispatcher`.
- [x] Job `IndexBand` na fila `database`, fila lógica `indexing`, `Queue::route`.
- [x] Worker com `--tries=3 --backoff=5,15,30 --max-jobs=200 --max-time=3600`.
- [x] Logs JSON no stdout; `request_id` propagado do request ao job.
- [x] `make stats` mostra saúde, fila e logs.

Aceite: POST enfileira job; worker processa com o mesmo request id.

### Fase 5 — Compatibilidade, qualidade e docs
- [x] `php artisan optimize` verde (config/events/routes/views), com teste de regressão.
- [x] `pint.json` (exclude de `bootstrap/cache`), `phpstan.neon` max sem baseline, `phpunit.xml` estrito.
- [x] 30 testes / 107 asserções, incluindo validação estrita, 500 padronizado e optimize.
- [x] `README.md`, `AGENTS.md`, `docs/adr/` e `CHANGELOG.md`.
- [x] `composer.json` com owner/licença/repo final e `platform.php = 8.5.13`.
- [x] `make check` e `make smoke` verdes.

Aceite: gate único verde; evidências registradas no README e CHANGELOG.

## 5. Matriz de verificação

| Gate | Comando | Critério |
|---|---|---|
| Estilo | `make lint` | Pint `--test` sem diffs |
| Estático | `make static` | PHPStan max, zero erros, sem baseline |
| Contrato | `make validate` | `composer validate --strict` |
| Testes | `make test` | 30 testes, zero warnings/deprecations/risky |
| Gate | `make check` | todos acima |
| Otimização | `make optimize` | config/events/routes/views cacheados |
| HTTP | `make smoke` | 200/201/404/422/308 |
| Painel | browser/curl | `GET /` 200, filtro GET sem JS |
| Stack | `make stats` | containers healthy, fila e logs sem erro |
| Limpeza | `make down` / `make destroy` | volume preservado / removido |

## 6. Riscos e mitigação

| Risco | Mitigação |
|---|---|
| SQLite `database is locked` | WAL + `busy_timeout` + escritor único; ver ADR-0004 |
| Worker não ver código novo | `--max-jobs=200` e restart no `make up` |
| Cache vazando para testes | `optimize:clear` no target `make test` |
| Erro com stack trace em produção | `APP_DEBUG=false` + `Problem` catch-all |
| Aliases legados divergentes | redirecionamento 308, uma única implementação |
| `route:cache` com closures | actions de classe em todas as rotas persistentes |
| Porta padrão herdada da imagem | FPM em 24002, Caddy em 24003/24001; ver ADR-0001 |
