<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    /**
     * The products that belong to the tab with pivot data.
     */
    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, 'tab_products')
            ->withPivot('quantity', 'deleted_at')
            ->withTimestamps()
            ->whereNull('tab_products.deleted_at');
    }

    /**
     * Get all product tab relationships (including soft deleted)
     */
    public function tabProducts(): HasMany {
        return $this->hasMany(TabProduct::class);
    }

    /**
     * Get active payments for the tab (excluding soft deleted)
     */
    public function payments(): HasMany {
        return $this->hasMany(TabPayment::class)->whereNull('deleted_at');
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

    /**
     * Get total amount paid
     */
    public function getTotalPaid(): float {
        return $this->payments()->sum('payment_value');
    }

    /**
     * Get remaining amount to pay
     */
    public function getRemainingAmount(): float {
        return $this->total_value - $this->getTotalPaid();
    }

    /**
     * Check if tab is fully paid
     */
    public function isFullyPaid(): bool {
        return $this->getRemainingAmount() <= 0;
    }

    /**
     * Check if tab is overpaid
     */
    public function isOverpaid(): bool {
        return $this->getTotalPaid() > $this->total_value;
    }
}
