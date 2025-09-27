<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tab_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tab_id')->constrained()->onDelete('cascade');
            $table->string('payer_name')->nullable();
            $table->decimal('payment_value', 10, 2);
            $table->string('payment_method');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_payments');
    }
};
