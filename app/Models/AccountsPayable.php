<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AccountsPayable extends Model
{
    use HasFactory;

    protected $table = 'accounts_payables';

    protected $fillable = [
        'invoice_number',
        'description',
        'amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'issue_date',
        'due_date',
        'payment_date',
        'supplier_id',
        'expense_category_id',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Calcular valor restante automaticamente
            $model->remaining_amount = $model->amount - $model->paid_amount;
            
            // Atualizar status baseado no pagamento
            if ($model->paid_amount == 0) {
                $model->status = $model->due_date < now() ? 'overdue' : 'pending';
            } elseif ($model->paid_amount < $model->amount) {
                $model->status = 'partial';
            } elseif ($model->paid_amount >= $model->amount) {
                $model->status = 'paid';
                $model->payment_date = $model->payment_date ?? now();
            }
        });
    }

    // Relacionamentos
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeDueInDays($query, $days = 7)
    {
        return $query->whereBetween('due_date', [now(), now()->addDays($days)])
                    ->whereIn('status', ['pending', 'partial']);
    }

    // Métodos úteis
    public function registerPayment($amount, $paymentDate = null, $paymentMethod = null)
    {
        $this->paid_amount += $amount;
        $this->payment_date = $paymentDate ?? now();
        $this->payment_method = $paymentMethod ?? $this->payment_method;
        $this->save();

        return $this;
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && in_array($this->status, ['pending', 'partial']);
    }

    public function getDaysUntilDueAttribute()
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function getPaymentPercentageAttribute()
    {
        return $this->amount > 0 ? ($this->paid_amount / $this->amount) * 100 : 0;
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'overdue' => 'Em Atraso',
            'partial' => 'Parcial',
            'paid' => 'Pago',
            'cancelled' => 'Cancelado',
            default => 'Desconhecido'
        };
    }
}