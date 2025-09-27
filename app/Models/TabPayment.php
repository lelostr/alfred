<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TabPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tab_id',
        'payer_name',
        'payment_value',
        'payment_method'
    ];

    protected $casts = [
        'payment_value' => 'decimal:2',
    ];

    /**
     * Get the tab that owns the payment.
     */
    public function tab(): BelongsTo
    {
        return $this->belongsTo(Tab::class);
    }
}
