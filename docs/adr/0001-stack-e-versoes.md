# ADR-0001 — Stack e versões fixadas

- Status: Aceita
- Data: 2026-11-25

## Contexto

O desafio original usava Laravel 5.7/PHP 7.1. O objetivo do laboratório éModerno e reproduzível: mesma imagem, mesmas dependências, mesmo resultado em qualquer máquina.

## Decisão

- `php:8.5.13-fpm-alpine3.24@sha256:51414d5291758fe27f58a64c7e8a078465d32cc7dcef7bda803f5b36019802e0` como imagem principal.
- `caddy:2.11.9-alpine@sha256:003a97808e781aa9f7e75cc2f43d34d461099031faf5e20faefb9f61126836f0` como proxy reverso.
- Laravel `^13.17` (resolvido em 13.44.1), PHPUnit 12, Larastan 3 / PHPStan 2.
- `composer.lock` versionado e `platform.php = 8.5.13` para resolução determinística.
- Portas fixas (faixa 24001–24010): `24001` exposta no host, `24003` listener HTTP do Caddy no container, `24002` PHP-FPM, `24004` Admin API do Caddy em `localhost`. Nenhuma porta HTTP/TLS padrão é utilizada.
- O pool `www` do PHP-FPM é substituído por `docker/www.conf` (escuta em `24002`, sem `listen.allowed_clients`).

- `docker compose ps` pode exibir `9000/tcp`/`80/tcp`/`443/tcp` por metadados `EXPOSE` herdados das imagens base; nenhum desses sockets está aberto. Os listeners reais foram verificados: `24002` (FPM), `24003` (Caddy), `24004` (admin) e `24001` no host.
- `composer install` não aceita PHP < 8.5.13; isso é intencional e fixado em `config.platform.php`.
- Atualizações de imagem exigem novo digest e revisão deste ADR.
- O `auto_https` do Caddy é desligado: o ambiente é local HTTP. Em produção, habilitar TLS e HSTS.

## Alternativas rejeitadas

- PHP 8.4: contrato do laboratório pede 8.5.13.
- Apache/NGINX: Caddy reduz configuração e já integra com PHP-FPM via `php_fastcgi`.
