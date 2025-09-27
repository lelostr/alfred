<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseController {

    public function index() {
        $products = Product::all();
        return $this->successResponse('Produtos listados com sucesso', $products);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);
        $product = Product::create($request->all());
        return $this->successResponse('Produto criado com sucesso', $product);
    }

    public function show($id) {
        $product = Product::find($id);
        return $this->successResponse('Produto encontrado com sucesso', $product);
    }

    public function update(Request $request, $id) {
        $product = Product::find($id);
        $product->update($request->all());
        return $this->successResponse('Produto atualizado com sucesso', $product);
    }

    public function destroy($id) {
        $product = Product::find($id);
        $product->delete();
        return $this->successResponse('Produto deletado com sucesso', $product);
    }
}
