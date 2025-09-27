<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Tab extends Model {

    protected $fillable = [
        'client_name',
        'total_items',
        'total_value',
        'closed_at'
    ];

    protected $casts = [
        'total_value' => 'decimal:2',
        'closed_at' => 'datetime'
    ];

    /**
     * Get active product tab relationships (excluding soft deleted)
     */
    public function activeProductTabs(): HasMany {
        return $this->hasMany(TabProduct::class)->whereNull('deleted_at');
    }

    public function products(): HasManyThrough {
        return $this->hasManyThrough(Product::class, TabProduct::class, secondKey: 'id', secondLocalKey: 'product_id');
    }

    /**
     * Check if tab is closed
     */
    public function isClosed(): bool {
        return !is_null($this->closed_at);
    }

    /**
     * Close the tab
     */
    public function close(): void {
        $this->update(['closed_at' => now()]);
    }

    /**
     * Recalculate totals based on active products only
     */
    public function recalculateTotals(): void {
        $totalItems = 0;
        $totalValue = 0;

        // Use activeProductTabs to exclude soft deleted items
        $activeProductTabs = $this->activeProductTabs()->with('product')->get();

        foreach ($activeProductTabs as $productTab) {
            $quantity = $productTab->quantity;
            $totalItems += $quantity;
            $totalValue += $productTab->product->price * $quantity;
        }

        $this->update([
            'total_items' => $totalItems,
            'total_value' => $totalValue
        ]);
    }
}
