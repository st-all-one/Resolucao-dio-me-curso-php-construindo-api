# ADR-0004 — SQLite, WAL e fila de banco

- Status: Aceita
- Data: 2026-11-25

## Contexto

O desafio pede SQLite e configuração de filas. SQLite tem um escritor por vez; o worker e o FPM escrevem no mesmo arquivo.

## Decisão

- `journal_mode=wal`, `busy_timeout=5000`, `synchronous=normal` por conexão.
- Arquivo em `database/sqlite/database.sqlite`, montado em volume nomeado dedicated — nunca mascarar `database/` (onde vivem migrations e seeders).
- `db-init` cria o arquivo, ajusta proprietário para `www-data` e aplica migrate+seed antes dos demais serviços.
- Fila `database`, fila lógica `indexing`, `Queue::route` no provider.
- Worker com `--tries=3 --backoff=5,15,30 --timeout=30 --max-jobs=200 --max-time=3600`.

## Consequências

- Leituras não bloqueiam escritas; escritor único com retry via `busy_timeout`.
- O job recebe escalares + `request_id`, nunca Eloquent; não depende de container no worker.
- Worker de longa duração exige restart no deploy (já feito por `make up`).
- Para produção com múltiplos nós, migrar para Postgres/Redis e revisar este ADR.
