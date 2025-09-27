<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductTab extends Model {
    use SoftDeletes;

    protected $table = 'product_tab';

    protected $fillable = [
        'product_id',
        'tab_id',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the product that owns the pivot.
     */
    public function product() {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the tab that owns the pivot.
     */
    public function tab() {
        return $this->belongsTo(Tab::class);
    }
}
