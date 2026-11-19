<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'npwp',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the invoices for the client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the active invoices for the client.
     */
    public function activeInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class)
            ->whereIn('status', ['draft', 'sent']);
    }

    /**
     * Scope: Search by name, email, phone, or NPWP.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('email', 'LIKE', "%{$term}%")
              ->orWhere('phone', 'LIKE', "%{$term}%")
              ->orWhere('npwp', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Scope: Only active (non-deleted) clients.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}