<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Band\FormedIn;
use App\Domain\Band\Genre;
use App\Models\BandRecord;
use Illuminate\Database\Seeder;

final class BandSeeder extends Seeder
{
    /**
     * Dados fixos e determinísticos — a suíte de testes não depende de ordem aleatória.
     */
    public function run(): void
    {
        $bands = [
            ['name' => 'Pink Floyd', 'genre' => Genre::Rock, 'formed_in' => 1965],
            ['name' => 'Nirvana', 'genre' => Genre::Rock, 'formed_in' => 1987],
            ['name' => 'Miles Davis Quintet', 'genre' => Genre::Jazz, 'formed_in' => 1955],
            ['name' => 'Metallica', 'genre' => Genre::Metal, 'formed_in' => 1981],
            ['name' => 'Daft Punk', 'genre' => Genre::Electronic, 'formed_in' => 1993],
        ];

        foreach ($bands as $band) {
            BandRecord::query()->firstOrCreate(
                ['name' => $band['name']],
                [
                    'genre' => $band['genre']->value,
                    'formed_in' => (new FormedIn($band['formed_in']))->value,
                ],
            );
        }
    }
}
