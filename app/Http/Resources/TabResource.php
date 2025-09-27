<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TabResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'total_items' => $this->total_items,
            'total_value' => $this->total_value,
            'closed_at' => $this->closed_at,
            'is_closed' => $this->isClosed(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'category' => $product->category,
                        'price' => $product->price,
                        'description' => $product->description,
                        'image' => $product->image,
                        'quantity' => $product->pivot->quantity,
                        'subtotal' => $product->price * $product->pivot->quantity,
                    ];
                });
            }),
        ];
    }
}
