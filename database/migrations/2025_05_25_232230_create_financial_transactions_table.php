<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['receivable', 'payable']);
            $table->string('transactionable_type');
            $table->unsignedBigInteger('transactionable_id');
            $table->index(['transactionable_type', 'transactionable_id'], 'fin_trans_morph_index');
            $table->decimal('amount', 10, 2);
            $table->date('transaction_date');
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'pix', 'check']);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
