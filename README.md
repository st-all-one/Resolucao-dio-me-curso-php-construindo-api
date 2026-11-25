# DIO Bands API — Laravel 13 / PHP 8.5.13

Reimplementação do desafio da live **"Laravel: construindo uma API do zero"** (DIO) com stack atual, arquitetura DDD-lite e qualidade desde o dia zero.

Repositório: [st-all-one/Resolucao-dio-me-curso-php-construindo-api](https://github.com/st-all-one/Resolucao-dio-me-curso-php-construindo-api) · Mantenedor: Stallone LS (`st-all-one`) · Licença: MIT.

## Painel web

Acesse **`http://localhost:24001/`** para o painel server-rendered em Blade: lista bandas do domínio, filtra por gênero via `GET` (sem JavaScript) e usa CSS puro (`public/css/dashboard.css`). A tela é a demonstração visível da execução do projeto.

## Contrato da API

| Método | Rota | Sucesso |
|---|---|---|
| GET | `/api/v1/bands` | 200 — lista paginada em `data` + `meta` |
| GET | `/api/v1/bands/{id}` | 200 — banda |
| GET | `/api/v1/bands/gender/{gender}` | 200 — lista filtrada |
| POST | `/api/v1/bands` | 201 — banda criada + `Location` |

Aliases da live (`/bands`, `/bands/store`, `/bands/gender/{gender}`, `/bands/{id}`) redirecionam com **308**.

Erros usam `application/problem+json` (RFC 9457) com `code`, `request_id` e `errors` quando aplicável. Exemplo:

```json
{
  "type": "https://httpstatuses.io/404",
  "title": "Not Found",
  "status": 404,
  "detail": "Band 999 was not found.",
  "instance": "/api/v1/bands/999",
  "code": "band-not-found",
  "request_id": "smoke-123"
}
```

Gêneros aceitos: `rock`, `pop`, `jazz`, `metal`, `indie`, `electronic`, `hip_hop`, `classical`, `other`.

## Subir

Pré-requisito: Docker com Compose v2.

```bash
make up            # build + APP_KEY + migrate + seed + healthchecks
make smoke         # prova 200/201/404/422/308 via curl
make stats         # containers, rotas, migrations, fila e logs
make check         # composer validate + Pint + PHPStan max + PHPUnit
make optimize      # php artisan optimize (config/events/routes/views)
make down          # para sem apagar dados
make destroy       # remove volumes, imagens e redes

```

Portas (faixa 24001–24010, nenhuma porta padrão):

| Porta | Uso |
|---|---|
| `24001` | Caddy no host (HTTP) |
| `24002` | PHP-FPM na rede do Compose |
| `24003` | Caddy dentro do container |
| `24004` | Admin API do Caddy (loopback) |

Sobrescreva apenas a porta do host com `HTTP_PORT=24005 make up`.

## Exemplo

```bash
curl http://localhost:24001/api/v1/bands
curl http://localhost:24001/api/v1/bands/gender/rock
curl -X POST http://localhost:24001/api/v1/bands \
  -H 'Content-Type: application/json' \
  -d '{"name":"Queen","genre":"rock","formed_in":1970}'
```

A criação publica `BandCreated` no domínio. Um adaptador na infraestrutura despacha `IndexBand` para a fila `database`; o worker processa com `--tries=3`, backoff e logs correlacionados pelo mesmo `X-Request-Id` da requisição.

## Arquitetura

```
app/Domain/Band/       enum, value objects, entidade, repositório (contrato), erros, eventos
app/Application/Band/  Actions + comandos imutáveis (inclui BrowseBands para o painel)
app/Infrastructure/    Eloquent, HTTP (controllers/requests/resources/middleware), Queue
app/Providers/         composition root (DI + Queue::route)
database/              migrations, seeder determinístico, sqlite/
docker/                php-fpm.Dockerfile, www.conf, php.ini, Caddyfile
resources/views/       layout + dashboard em Blade
public/css/            CSS puro do painel
docs/adr/              decisões arquiteturais
```

Regras e comandos canônicos em [`AGENTS.md`](AGENTS.md). Plano com critérios de aceite em [`PLAN.md`](PLAN.md).

## Quality gates

| Gate | Ferramenta | Critério |
|---|---|---|
| Estilo | Pint | zero diffs |
| Estático | PHPStan + Larastan | `level: max`, zero erros, sem baseline |
| Testes | PHPUnit 12 | 30 testes / 107 asserções, zero warnings/deprecations/risky |
| HTTP | `make smoke` | 200, 201, 404, 422, 308 |
| Otimização | `make optimize` | config/events/routes/views cacheados |
| Painel | `GET /` | Blade 200, filtro GET sem JS |
| Stack | `make stats` | todos os serviços healthy |

Histórico de mudanças: [`CHANGELOG.md`](CHANGELOG.md).

## Stack e referências

- PHP `8.5.13-fpm-alpine3.24` fixado por digest; Caddy `2.11.9-alpine` fixado por digest.
- Laravel 13.44.1, SQLite WAL, fila `database`, logs JSON estruturados.
- Contrato legado: `_ref/dio-laravel-api/`.
- Rigor de lint/testes inspirado em `_ref/blake3-for-php/`.
- Decisões: [`docs/adr/`](docs/adr/README.md).

## Licença

MIT — veja `LICENSE`.
