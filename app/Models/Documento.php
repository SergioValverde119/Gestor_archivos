<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    // ...

    /**
     * El oficio al que pertenece este documento.
     */
    public function oficio(): BelongsTo
    {
        return $this->belongsTo(Oficio::class);
    }
}