<?php

namespace Ibraheem\AramexIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MerchantBilling extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'merchant_id',
        'billing_period',
        'month',
        'year',
        'monthly_subscription_fee',
        'total_shipments',
        'free_shipments_used',
        'paid_shipments',
        'per_shipment_fee',
        'total_amount',
        'currency',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'monthly_subscription_fee' => 'decimal:2',
        'total_shipments' => 'integer',
        'free_shipments_used' => 'integer',
        'paid_shipments' => 'integer',
        'per_shipment_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get transactions for this billing.
     */
    public function transactions()
    {
        return $this->hasMany(MerchantTransaction::class, 'billing_id');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by merchant.
     */
    public function scopeByMerchant($query, $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * Check if billing is paid.
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }
}

