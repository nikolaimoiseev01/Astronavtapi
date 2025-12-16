<?php

namespace App\Models\CalculatorMeta;

use Illuminate\Database\Eloquent\Model;

class PbCity extends Model
{
    public function getLabelAttribute(): string
    {
        return trim(sprintf(
            '%s (%s) — %s',
            $this->name,
            $this->english,
            strtoupper($this->country)
        ));
    }
}
