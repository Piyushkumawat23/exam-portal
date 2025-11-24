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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('submission_id')->constrained()->cascadeOnDelete(); // Link to submission [cite: 13]
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->decimal('amount', 10, 2);
        $table->string('payment_id');
        $table->string('method')->default('stripe'); // Stripe/Razorpay [cite: 6]
        $table->enum('status', ['success', 'failed']);
        $table->string('pdf_path')->nullable();// Store PDF Receipt path [cite: 7]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
