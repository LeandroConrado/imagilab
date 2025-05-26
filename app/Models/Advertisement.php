<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'content', 'image', 'link_url',
        'type', 'target_audience', 'sort_order',
        'is_active', 'start_date', 'end_date',
        'click_count', 'view_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'click_count' => 'integer',
        'view_count' => 'integer'
    ];

    // Scope para anúncios ativos no período
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    // Calcular CTR (Click Through Rate)
    public function getCtrAttribute()
    {
        return $this->view_count > 0
            ? round(($this->click_count / $this->view_count) * 100, 2)
            : 0;
    }
}
