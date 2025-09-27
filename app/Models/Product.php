<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model {

    protected $fillable = ['name', 'category', 'price', 'description', 'image'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * The tabs that belong to the product.
     */
    public function tabs(): BelongsToMany {
        return $this->belongsToMany(Tab::class, 'product_tab');
    }
}
