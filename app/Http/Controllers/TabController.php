<?php

namespace App\Http\Controllers;

use App\Http\Resources\TabResource;
use App\Models\Tab;
use App\Models\Product;
use App\Models\TabProduct;
use App\Models\TabPayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TabController extends BaseController {

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse {
        $tabs = Tab::with('products')->get();
        return $this->successResponse('Comandas listadas com sucesso', TabResource::collection($tabs));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse {
        $request->validate([
            'client_name' => 'required|string|max:255',
        ]);

        $tab = Tab::create([
            'client_name' => $request->client_name,
            'total_items' => 0,
            'total_value' => 0.00,
        ]);

        return $this->successResponse('Comanda criada com sucesso', new TabResource($tab));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse {
        $tab = Tab::with('products')->findOrFail($id);
        return $this->successResponse('Comanda encontrada com sucesso', new TabResource($tab));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse {
        $request->validate([
            'client_name' => 'sometimes|string|max:255',
        ]);

        $tab = Tab::findOrFail($id);
        $tab->update($request->only(['client_name']));

        return $this->successResponse('Comanda atualizada com sucesso', new TabResource($tab));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse {
        $tab = Tab::findOrFail($id);
        $tab->delete();

        return $this->successResponse('Comanda deletada com sucesso');
    }

    /**
     * Add a product to the tab
     */
    public function addProduct(Request $request, string $id): JsonResponse {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
        ]);

        $tab = Tab::findOrFail($id);

        if ($tab->isClosed()) {
            return $this->errorResponse('Não é possível adicionar produtos a uma comanda fechada', []);
        }

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;

        // Always create a new TabProduct entry (no unique constraint)
        TabProduct::create([
            'product_id' => $product->id,
            'tab_id' => $tab->id,
            'quantity' => $quantity
        ]);

        // Recalculate totals
        $tab->recalculateTotals();
        $tab->load('products');

        return $this->successResponse('Produto adicionado com sucesso', new TabResource($tab));
    }

    /**
     * Remove a product from the tab
     */
    public function removeProduct(Request $request, string $id): JsonResponse {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
            'remove_all' => 'boolean'
        ]);

        $tab = Tab::findOrFail($id);

        if ($tab->isClosed()) {
            return $this->errorResponse('Não é possível remover produtos de uma comanda fechada', []);
        }

        $product = Product::findOrFail($request->product_id);
        $quantityToRemove = $request->quantity ?? 1;
        $removeAll = $request->boolean('remove_all', false);

        // Get active product tabs for this product
        $activeProductTabs = $tab->activeProductTabs()
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($activeProductTabs->isEmpty()) {
            return $this->errorResponse('Produto não encontrado na comanda', []);
        }

        if ($removeAll) {
            // Soft delete all instances of this product
            foreach ($activeProductTabs as $productTab) {
                $productTab->delete();
            }
        } else {
            // Remove specific quantity (FIFO - First In, First Out)
            $remainingToRemove = $quantityToRemove;

            foreach ($activeProductTabs as $productTab) {
                if ($remainingToRemove <= 0) break;

                $currentQuantity = $productTab->quantity;

                if ($remainingToRemove >= $currentQuantity) {
                    // Remove this entire entry
                    $productTab->delete();
                    $remainingToRemove -= $currentQuantity;
                } else {
                    // Reduce quantity in this entry
                    $productTab->update(['quantity' => $currentQuantity - $remainingToRemove]);
                    $remainingToRemove = 0;
                }
            }
        }

        // Recalculate totals
        $tab->recalculateTotals();
        $tab->load('products');

        return $this->successResponse('Produto removido com sucesso', new TabResource($tab));
    }

    /**
     * Close the tab
     */
    public function close(string $id): JsonResponse {
        $tab = Tab::findOrFail($id);

        if ($tab->isClosed()) {
            return $this->errorResponse('Comanda já está fechada', []);
        }

        $tab->close();
        $tab->load('products');

        return $this->successResponse('Comanda fechada com sucesso', new TabResource($tab));
    }

    /**
     * Add a payment to the tab
     */
    public function addPayment(Request $request, string $id): JsonResponse {
        $request->validate([
            'payer_name' => 'nullable|string|max:255',
            'payment_value' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
        ]);

        $tab = Tab::findOrFail($id);

        if ($tab->isClosed()) {
            return $this->errorResponse('Não é possível adicionar pagamentos a uma comanda fechada', []);
        }

        // Check if payment would exceed the remaining amount
        $remainingAmount = $tab->getRemainingAmount();
        if ($request->payment_value > $remainingAmount) {
            return $this->errorResponse('Valor do pagamento excede o valor restante da comanda', [
                'remaining_amount' => $remainingAmount,
                'payment_value' => $request->payment_value
            ]);
        }

        $payment = TabPayment::create([
            'tab_id' => $tab->id,
            'payer_name' => $request->payer_name,
            'payment_value' => $request->payment_value,
            'payment_method' => $request->payment_method,
        ]);

        $tab->load('products', 'payments');

        return $this->successResponse('Pagamento adicionado com sucesso', new TabResource($tab));
    }

    /**
     * Remove a payment from the tab
     */
    public function removePayment(Request $request, string $id): JsonResponse {
        $request->validate([
            'payment_id' => 'required|exists:tab_payments,id',
        ]);

        $tab = Tab::findOrFail($id);

        if ($tab->isClosed()) {
            return $this->errorResponse('Não é possível remover pagamentos de uma comanda fechada', []);
        }

        $payment = TabPayment::where('tab_id', $tab->id)
            ->where('id', $request->payment_id)
            ->first();

        if (!$payment) {
            return $this->errorResponse('Pagamento não encontrado nesta comanda', []);
        }

        $payment->delete(); // Soft delete
        $tab->load('products', 'payments');

        return $this->successResponse('Pagamento removido com sucesso', new TabResource($tab));
    }
}
