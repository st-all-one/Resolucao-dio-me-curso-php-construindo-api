# AGENTS.md — Bands API (Laravel 13 / PHP 8.5)

> Constituição do projeto. Plano de execução: `PLAN.md`. Decisões: `docs/adr/`.
> Histórico: `CHANGELOG.md`.

## 0. Regra de ouro

O agente entrega código e evidências; o humano commita. Git é somente leitura para o agente.

## 1. Identidade

- API REST para o desafio DIO "Construindo uma API do zero" (domínio: bandas).
- Repositório final: `st-all-one/Resolucao-dio-me-curso-php-construindo-api`. Mantenedor único: Stallone LS. Licença MIT.
- PHP 8.5.13, Laravel 13.x, SQLite 3 via PDO, Caddy 2, Docker Compose.
- Namespace raiz `App\`; domínio em `App\Domain`, casos de uso em `App\Application`.

## 2. Contrato inviolável

- `declare(strict_types=1)` em todo arquivo PHP.
- API pública em `/api/v1`; aliases legados redirecionam com 308.
- Painel em `GET /`: Blade + CSS puro, sem JavaScript e sem Vite/Tailwind.
- Erros seguem RFC 9457 (`application/problem+json`), inclusive erros não previstos (catch-all 500).
- Nenhum Model Eloquent ou Request do framework cruza a fronteira do domínio.
- Entrada é validada por Form Request; regras de negócio ficam em Actions.
- Toda action de rota deve ser classe ou array — nunca closure — para sobreviver a `route:cache`.
- Somente portas 24001–24010: `24001` host, `24002` FPM, `24003` Caddy, `24004` admin.
- Nenhum `env()` fora de `config/`; nenhum segredo em log.
- `APP_DEBUG=false` em produção. Respostas de erro nunca expõem stack trace.

## 3. Arquitetura (DDD-lite)

```
app/Domain/Band/          enum, value objects, entidade, contrato de repositório, erros, eventos
app/Application/Band/     Actions (casos de uso) + comandos imutáveis
app/Infrastructure/Band/  implementação Eloquent do repositório + mapper
app/Infrastructure/Http/  Controllers, Form Requests, Resources, middleware
app/Infrastructure/Queue/ Jobs e adaptador de eventos de domínio
app/Models/               Eloquent (detalhe de persistência, nunca exposto)
app/Providers/            composition root (DI + Queue::route)
resources/views/          layout e dashboard em Blade
public/css/               CSS puro do painel
```

Regras de dependência: `Domain` não importa nada de `Infrastructure`, `Http` ou Eloquent.
`Application` depende somente de `Domain`. `Infrastructure` implementa os contratos e
adapta o domínio ao framework. Controllers são finos e delegam a Actions.

## 4. Comandos canônicos

```bash
make up          # build + migrate + seed + iniciar stack
make stats       # saúde dos containers, rotas, migrations, fila e logs
make down        # parar sem apagar dados
make destroy     # down + remover volumes, imagens e redes do projeto
make check       # validate + lint + static + test (gate único)
make optimize    # php artisan optimize (config/events/routes/views)
make test        # suíte PHPUnit
make shell       # shell no container de aplicação
```

Nunca rode PHP/Composer direto no host: use `make exec cmd='...'` ou os alvos acima.
`make check` é o gate; não valide com comandos avulsos.
`php artisan optimize` deve funcionar sempre — se uma rota usar closure, o cache quebra.

## 5. Ferramentas e rigor

- **Pint** (config `pint.json`): PSR-12 + rigidez extra; `--test` no gate; `bootstrap/cache` excluído.
- **PHPStan + Larastan** nível `max`, sem baseline, incluindo `app/`, `routes/`,
  `bootstrap/`, `config/`, `database/` e `tests/`.
- **PHPUnit 12** com `failOnWarning`, `failOnDeprecation`, `failOnNotice`,
  `failOnRisky`, `failOnPhpunitDeprecation`, ordem determinística e metadados de
  cobertura obrigatórios.
- **Composer** `validate --strict`, `audit` sem advisories e `platform.php = 8.5.13`.
- Versões fixadas em `composer.lock`; imagens fixadas por digest em `compose.yaml`.

## 6. Testes

- Unit: Value Objects, enum e regras puras.
- Feature: contrato HTTP (status, corpo, headers), validação estrita, problemas RFC 9457,
  fila, persistência SQLite, dashboard e compatibilidade com `optimize`.
- Determinísticos: sem relógio, rede ou aleatoriedade não controlada.
- Fakes do framework para fila/eventos; banco em `:memory:` por teste.

## 7. Definição de pronto

- `make check` verde (30 testes / 107 asserções).
- `make optimize` verde e API/painel funcionando com caches ativos.
- `make up` + `curl` provando os quatro endpoints, um erro 422/404 e o painel em `/`.
- `make stats` mostrando workers, fila e logs sem erro.
- `make down`/`make destroy` verificados; nenhum artefato temporário versionado.
