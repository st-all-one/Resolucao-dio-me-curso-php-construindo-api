@extends('layouts.app')

@section('title', 'Painel de bandas — DIO Bands API')

@section('content')
    <section class="hero" aria-labelledby="page-title">
        <p class="eyebrow">Desafio DIO · Laravel 13</p>
        <h1 id="page-title">Bandas</h1>
        <p class="lede">
            Painel server-rendered consumindo o mesmo caso de uso da API. Filtre por gênero
            sem JavaScript; os dados vêm do domínio, não de uma consulta na view.
        </p>
        <dl class="stats">
            <div class="stat">
                <dt>Bandas exibidas</dt>
                <dd>{{ $browse->bands->total }}</dd>
            </div>
            <div class="stat">
                <dt>Filtro ativo</dt>
                <dd>{{ $browse->genre?->label() ?? 'Todos' }}</dd>
            </div>
            <div class="stat">
                <dt>Página</dt>
                <dd>{{ $browse->bands->page }}</dd>
            </div>
        </dl>
    </section>

    <section class="panel" aria-labelledby="filter-title">
        <h2 id="filter-title">Filtrar por gênero</h2>
        <form class="filter-form" method="get" action="{{ route('dashboard') }}">
            <div class="field">
                <label for="genre">Gênero</label>
                <select id="genre" name="genre">
                    <option value="" @selected($browse->genre === null)>Todos os gêneros</option>
                    @foreach ($browse->genres as $option)
                        <option value="{{ $option->value }}" @selected($browse->genre === $option)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit">Filtrar</button>
            @if ($browse->genre !== null)
                <a class="clear-link" href="{{ route('dashboard') }}">Limpar filtro</a>
            @endif
        </form>
    </section>

    <section class="panel" aria-labelledby="bands-title">
        <h2 id="bands-title">Bandas cadastradas</h2>

        @if ($browse->bands->items === [])
            <p class="empty-state">
                Nenhuma banda encontrada para o filtro selecionado.
                <a href="{{ route('dashboard') }}">Ver todas as bandas</a>.
            </p>
        @else
            <div class="table-scroll">
                <table>
                    <caption>
                        @if ($browse->genre !== null)
                            Bandas do gênero {{ $browse->genre->label() }}.
                        @else
                            Todas as bandas cadastradas.
                        @endif
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">Banda</th>
                            <th scope="col">Gênero</th>
                            <th scope="col">Formação</th>
                            <th scope="col">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($browse->bands->items as $band)
                            <tr>
                                <th scope="row">{{ $band->name->value }}</th>
                                <td>{{ $band->genre->label() }}</td>
                                <td><time datetime="{{ $band->formedIn->value }}">{{ $band->formedIn->value }}</time></td>
                                <td><code>{{ $band->id?->value }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
