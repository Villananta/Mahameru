<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'client_id',
        'issue_date',
        'due_date',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date'      => 'date',
        'due_date'        => 'date',
        'subtotal'        => 'decimal:2',
        'discount_value'  => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'grand_total'     => 'decimal:2',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * Get the client that owns the invoice.
     * Uses withTrashed() so deleted clients still show on historical invoices.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    /**
     * Get the items for the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Check if invoice is overdue (status sent and due_date < today).
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'sent'
                && $this->due_date
                && $this->due_date->isPast(),
        );
    }

    /**
     * Get human-readable status label.
     */
    protected function displayStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => config('invoice.statuses')[$this->status] ?? ucfirst($this->status),
        );
    }

    /**
     * Check if invoice is locked (paid or cancelled).
     */
    protected function isLocked(): Attribute
    {
        return Attribute::make(
            get: fn () => in_array($this->status, ['paid', 'cancelled'], true),
        );
    }

    /**
     * Scope: Filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        if ($status === 'overdue') {
            return $query->where('status', 'sent')
                ->where('due_date', '<', now()->toDateString());
        }
        return $query->where('status', $status);
    }

    /**
     * Scope: Search by invoice number or client name.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('invoice_number', 'LIKE', "%{$term}%")
              ->orWhereHas('client', fn ($cq) => $cq->where('name', 'LIKE', "%{$term}%"));
        });
    }

    /**
     * Scope: Latest first.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * Scope: With KPI aggregates.
     */
    public function scopeWithKpis($query)
    {
        return $query->selectRaw('
            COUNT(*) as total_count,
            SUM(CASE WHEN status IN (?, ?) THEN grand_total ELSE 0 END) as total_outstanding,
            SUM(CASE WHEN status = ? THEN grand_total ELSE 0 END) as total_paid,
            COUNT(CASE WHEN status = ? THEN 1 END) as draft_count
        ', ['sent', 'overdue', 'paid', 'draft']);
    }
}