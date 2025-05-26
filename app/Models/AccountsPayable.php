<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AccountsPayable extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'supplier_id', 'expense_category_id', 'amount',
        'amount_paid', 'amount_remaining', 'due_date', 'issue_date',
        'status', 'payment_method', 'description', 'notes',
        'installment_number', 'total_installments'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_remaining' => 'decimal:2',
        'due_date' => 'date',
        'issue_date' => 'date'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(FinancialTransaction::class, 'transactionable');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payable) {
            if (empty($payable->invoice_number)) {
                $payable->invoice_number = 'PAY-' . strtoupper(uniqid());
            }
            $payable->amount_remaining = $payable->amount - $payable->amount_paid;
        });

        static::updating(function ($payable) {
            $payable->amount_remaining = $payable->amount - $payable->amount_paid;

            if ($payable->amount_remaining <= 0) {
                $payable->status = 'paid';
            } elseif ($payable->amount_paid > 0) {
                $payable->status = 'partial';
            }
        });
    }
}
