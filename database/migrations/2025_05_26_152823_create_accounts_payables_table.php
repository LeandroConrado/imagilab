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
        Schema::create('accounts_payables', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable(); // Número da fatura
            $table->string('description'); // Descrição da conta
            $table->decimal('amount', 12, 2); // Valor
            $table->decimal('paid_amount', 12, 2)->default(0); // Valor pago
            $table->decimal('remaining_amount', 12, 2)->default(0); // Valor restante
            $table->enum('status', ['pending', 'overdue', 'partial', 'paid', 'cancelled'])->default('pending');
            $table->date('issue_date'); // Data de emissão
            $table->date('due_date'); // Data de vencimento
            $table->date('payment_date')->nullable(); // Data do pagamento
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->onDelete('set null');
            $table->string('payment_method')->nullable(); // Método de pagamento
            $table->string('reference')->nullable(); // Referência/Observação
            $table->text('notes')->nullable(); // Notas adicionais
            $table->timestamps();
            
            // Índices para performance
            $table->index(['status', 'due_date']);
            $table->index('supplier_id');
            $table->index('expense_category_id');
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts_payables');
    }
};