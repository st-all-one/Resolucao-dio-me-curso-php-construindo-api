# Changelog

Todas as mudanças notáveis deste projeto são documentadas aqui.
Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/)
e [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [Unreleased]

### Added
- Dashboard server-rendered em `GET /`: Blade + CSS puro, sem JavaScript, com filtro por gênero via GET.
- `BrowseBands` e `BandBrowse` como caso de uso e DTO do painel.
- Compatibilidade comprovada com `php artisan optimize` (config/events/routes/views), incluindo teste de regressão.
- `LegacyBandController`: aliases legados como actions de classe, compatíveis com `route:cache`.
- Renderizador catch-all de erros não previstos em rotas API, sempre em RFC 9457 sem stack trace.
- Validação estrita de `formed_in` (`integer:strict`), evitando 500 em payload com ano como string.
- `make optimize` e portas dedicadas 24001–24004.
- Metadados de repositório, autor e licença no `composer.json`.

### Changed
- Imagem principal fixada em `php:8.5.13-fpm-alpine3.24@sha256:51414d52…`; Caddy fixado em `2.11.9-alpine@sha256:003a9780…`.
- PHP-FPM passou a escutar em `24002` com pool customizado `docker/www.conf`.
- Caddy passou a escutar em `24003` no container, exposto como `24001` no host; admin em `localhost:24004`.
- `platform.php` fixado em `8.5.13`; `composer.lock` regenerado.
- `make test` usa `optimize:clear` e DB SQLite em memória; `make check` passou a incluir 30 testes.
- Pint passou a excluir `bootstrap/cache`, `vendor` e `_ref`.

### Fixed
- `make check` voltou a funcionar após `make optimize`: Pint usa lista explícita de arquivos e não entra mais em `bootstrap/cache`.
- Dashboard deixou de depender de sessão: registrado com middleware `api` via `then()`.
- Pool FPM rejeitava conexões por `listen.allowed_clients` vazio; diretiva removida.
- `db-init` agora ajusta proprietário do SQLite para `www-data` antes de migrar e semear.
- `.env` sincronizado com portas e caminho de banco do container.
- `LOG_STACK=null` aplicado nos testes para evitar ruído de exceções esperadas.

### Verified
- `make check`: 30 testes / 107 asserções, Pint limpo, PHPStan max sem erros.
- `make optimize`: cache de config, events, routes e views gerado com sucesso.
- `make smoke`: health 200, listagem 200, 404, 422 e redirect 308.
- POST real com job processado e `request_id` propagado.
- `make up`, `make stats`, `make down` e `make destroy` executados com sucesso.

## [1.0.0] — 2026-11-25

### Added
- API `/api/v1/bands` com listagem, busca por id, filtro por gênero e criação.
- Aliases legados da live DIO com redirecionamento 308.
- DDD-lite: domínio, aplicação, infraestrutura e composition root.
- SQLite WAL, fila `database` com worker dedicado e logs estruturados com `X-Request-Id`.
- Erros RFC 9457, quality gates com Pint, PHPStan/Larastan max e PHPUnit 12.
- Docker Compose + Caddy, Makefile com `up`, `stats`, `down` e `destroy`.
- ADRs 0001–0006.

[Unreleased]: https://github.com/st-all-one/Resolucao-dio-me-curso-php-construindo-api/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/st-all-one/Resolucao-dio-me-curso-php-construindo-api/releases/tag/v1.0.0
