<?php

namespace App\Models\CalculatorMeta;

use Illuminate\Database\Eloquent\Model;

class PbCity extends Model
{
    public function getLabelAttribute(): string
    {
        $country = PbCountry::query()->where('id', $this->country)->first()['name'];
        return trim(sprintf(
            '%s, %s',
            $this->name,
            $country
        ));
    }
}
