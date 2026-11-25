# ADR-0003 — Contrato HTTP e erros

- Status: Aceita
- Data: 2026-11-25

## Contexto

O legado expunha `/bands`, `/bands/store`, `/bands/gender/{gender}` e `/bands/{id}` com respostas inconsistentes. Não havia contrato de erro.

## Decisão

- Rotas canônicas sob `/api/v1`, com paginação em `meta` e `X-Request-Id` em toda resposta.
- Aliases legados redirecionam com **308** (preserva método e corpo), sem middleware de sessão.
- Erros usam RFC 9457 (`application/problem+json`) com `type`, `title`, `status`, `detail`, `instance`, `code`, `request_id` e `errors` opcional.
- Validação: Form Request → 422 com `errors` por campo.
- Domínio: `BandNotFound` → 404; `InvalidBandData` → 422.
- `APP_DEBUG=false` e renderização centralizada em `bootstrap/app.php`; nenhum stack trace vaza.

## Consequências

- Clientes antigos continuam funcionando via redirecionamento permanente.
- Erros são legíveis por máquina e correlacionáveis com logs.
- Testes de feature fixam status, `Content-Type` e corpo — o contrato é executável.
