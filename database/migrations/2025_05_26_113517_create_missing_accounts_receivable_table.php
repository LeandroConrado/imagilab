<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('accounts_receivables')) {
            Schema::create('accounts_receivables', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignId('customer_id')->constrained()->onDelete('cascade');
                $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
                $table->decimal('amount', 10, 2);
                $table->decimal('amount_paid', 10, 2)->default(0);
                $table->decimal('amount_remaining', 10, 2);
                $table->date('due_date');
                $table->date('issue_date');
                $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'cancelled'])->default('pending');
                $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'pix', 'check'])->nullable();
                $table->text('description');
                $table->text('notes')->nullable();
                $table->integer('installment_number')->default(1);
                $table->integer('total_installments')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_receivables');
    }
};
