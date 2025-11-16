<?php

namespace Ibraheem\AramexIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MerchantTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'merchant_id',
        'billing_id',
        'shipment_id',
        'transaction_type',
        'amount',
        'currency',
        'description',
        'status',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the billing record.
     */
    public function billing()
    {
        return $this->belongsTo(MerchantBilling::class, 'billing_id');
    }

    /**
     * Get the shipment record.
     */
    public function shipment()
    {
        return $this->belongsTo(AramexShipment::class, 'shipment_id');
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope a query to filter by merchant.
     */
    public function scopeByMerchant($query, $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }
}

