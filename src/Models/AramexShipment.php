<?php

namespace Ibraheem\AramexIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AramexShipment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'merchant_id',
        'merchant_aramex_account_id',
        'aramex_shipment_id',
        'aramex_shipment_number',
        'tracking_number',
        'reference',
        'label_url',
        'label_path',
        'status',
        'shipment_data',
        'aramex_response',
        'tracking_data',
        'webhook_data',
        'receiver_info',
        'shipper_info',
        'weight',
        'cod_amount',
        'product_name',
        'rate_amount',
        'error_message',
        'last_tracking_update',
        'notes',
    ];

    protected $casts = [
        'shipment_data' => 'array',
        'aramex_response' => 'array',
        'tracking_data' => 'array',
        'webhook_data' => 'array',
        'receiver_info' => 'array',
        'shipper_info' => 'array',
        'weight' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'rate_amount' => 'decimal:2',
        'last_tracking_update' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the merchant account for this shipment.
     */
    public function merchantAccount()
    {
        return $this->belongsTo(MerchantAramexAccount::class, 'merchant_aramex_account_id');
    }

    /**
     * Scope a query to only include shipments for a specific merchant.
     */
    public function scopeByMerchant($query, $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if shipment is delivered.
     */
    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if shipment is in transit.
     */
    public function isInTransit()
    {
        return in_array($this->status, ['in_transit', 'out_for_delivery']);
    }

    /**
     * Check if shipment has COD.
     */
    public function hasCOD()
    {
        return $this->cod_amount > 0;
    }
}

