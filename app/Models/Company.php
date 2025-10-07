<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['name'];

    public function stockPrices(): HasMany
    {
        return $this->hasMany(StockPrice::class);
    }
}