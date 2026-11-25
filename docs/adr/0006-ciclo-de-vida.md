# ADR-0006 — Ciclo de vida por Makefile

- Status: Aceita
- Data: 2026-11-25

## Contexto

Reproduzir o ambiente com uma sequência longa de comandos é propenso a divergência entre desenvolvedores e avaliadores.

## Decisão

Quatro comandos de ciclo de vida, com semântica explícita:

| Comando | Efeito |
|---|---|
| `make up` | gera `APP_KEY` se ausente, build das imagens pinadas, sobe stack com healthchecks, migrate+seed |
| `make stats` | status dos containers, rotas, migrations, jobs falhos e últimas linhas de log |
| `make down` | para containers, preserva volumes e dados |
| `make destroy` | `down` + remove volumes, imagens e redes do projeto |

Gate de qualidade: `make check` (composer validate + Pint + PHPStan max + PHPUnit).
`make smoke` prova os contratos HTTP via `curl`.

A porta padrão exposta é `24001` (Caddy escuta em `24003` no container, PHP-FPM em `24002` e admin do Caddy em `localhost:24004`), sobrepondo com `HTTP_PORT=24005 make up`.

## Consequências

- Avaliador executa apenas comandos `make`, sem conhecer detalhes do Compose.
- `destroy` é destrutivo por design: remove dados e imagens; `down` é seguro.
- `make up` reinicia workers, o que é necessário para código novo em processos long-lived.
