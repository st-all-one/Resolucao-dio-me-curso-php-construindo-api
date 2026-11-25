<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Detalhe de persistência. Nunca exposto diretamente na camada HTTP.
 */
final class BandRecord extends Model
{
    protected $table = 'bands';

    protected $guarded = [];

    protected $casts = [
        'formed_in' => 'integer',
    ];
}
