# ADR-0005 — Qualidade e observabilidade

- Status: Aceita
- Data: 2026-11-25

## Contexto

O projeto de referência `blake3-for-php` define rigor agressivo: PHPStan max sem baseline, formatação automática, testes estritos e gate único.

## Decisão

- **Pint** com `declare_strict_types`, imports ordenados, `concat_space` e `no_unused_imports`.
- **PHPStan + Larastan** `level: max`, `checkExplicitMixed`, sem baseline e sem ignores amplos.
- **PHPUnit 12** com `failOnWarning`, `failOnDeprecation`, `failOnNotice`, `failOnRisky`, `failOnPhpunitDeprecation`, `requireCoverageMetadata` e DB em `:memory:`.
- **Logs JSON no stdout**, um evento por linha, com `Context` injetado (`request_id`, `route`, `job`).
- `X-Request-Id` aceito do cliente (alfanumérico, ≤64) ou gerado; propagado via `Context` para o job.
- `Context::hidden` reservado para PII; nenhum segredo é logado.

## Consequências

- `make check` é o único caminho de validação.
- Erros de tipo aparecem antes de runtime; o custo é escrever mapeamento explícito de rows do banco.
- Cardinalidade alta fica em logs, não em métricas agregadas (não há Pulse neste escopo).
