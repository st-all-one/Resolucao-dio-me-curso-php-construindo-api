# ADR-0002 — DDD-lite em vez de MVC clássico

- Status: Aceita
- Data: 2026-11-25

## Contexto

O projeto legado colocava regras em `BandController` e serializava Eloquent diretamente. Isso acopla domínio, HTTP e persistência, e dificulta testar regras sem banco.

## Decisão

Quatro camadas com dependência unidirecional:

```
Domain → (nada)
Application → Domain
Infrastructure → Domain + Application + Laravel
HTTP → Application + Infrastructure
```

- `Domain/Band`: enum, value objects, entidade `readonly`, `BandRepository`, eventos e erros.
- `Application/Band`: Actions com construtor injetado e comandos imutáveis.
- `Infrastructure/Band`: repositório Eloquent e mapper.
- `Infrastructure/Http`: controllers finos, Form Requests, Resources, middleware e `Problem`.
- `Providers/BandServiceProvider`: composition root; `Queue::route` centralizado.

## Consequências

- Regras de negócio testadas sem framework pesado nem banco (testes Unit).
- Nenhum Model Eloquent aparece em resposta HTTP; Resource é a única fronteira.
- Mais arquivos que um MVC simples; aceitável pelo ganho de isolamento.
- Modelos Eloquent continuam sendo usados internamente para schema/seed e nome de tabela.
