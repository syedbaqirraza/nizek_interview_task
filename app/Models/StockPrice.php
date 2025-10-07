<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPrice extends Model
{
    protected $fillable = [
        'company_id',
        'date',
        'stock_price'
    ];

    protected $casts = [
        'date' => 'date',
        'stock_price' => 'decimal:2'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}