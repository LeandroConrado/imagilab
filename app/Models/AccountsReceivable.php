<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AccountsReceivable extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'customer_id', 'order_id', 'amount',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(FinancialTransaction::class, 'transactionable');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receivable) {
            if (empty($receivable->invoice_number)) {
                $receivable->invoice_number = 'REC-' . strtoupper(uniqid());
            }
            $receivable->amount_remaining = $receivable->amount - $receivable->amount_paid;
        });

        static::updating(function ($receivable) {
            $receivable->amount_remaining = $receivable->amount - $receivable->amount_paid;

            if ($receivable->amount_remaining <= 0) {
                $receivable->status = 'paid';
            } elseif ($receivable->amount_paid > 0) {
                $receivable->status = 'partial';
            }
        });
    }
}
