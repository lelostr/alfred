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
            'total_paid' => $this->getTotalPaid(),
            'remaining_amount' => $this->getRemainingAmount(),
            'is_fully_paid' => $this->isFullyPaid(),
            'is_overpaid' => $this->isOverpaid(),
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
            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'payer_name' => $payment->payer_name,
                        'payment_value' => $payment->payment_value,
                        'payment_method' => $payment->payment_method,
                        'created_at' => $payment->created_at,
                    ];
                });
            }),
        ];
    }
}
