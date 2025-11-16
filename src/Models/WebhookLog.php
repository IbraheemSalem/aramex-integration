<?php

namespace Ibraheem\AramexIntegration\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'merchant_id',
        'shipment_id',
        'event_type',
        'payload',
        'headers',
        'ip_address',
        'processed',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the shipment record.
     */
    public function shipment()
    {
        return $this->belongsTo(AramexShipment::class, 'shipment_id');
    }

    /**
     * Scope a query to filter by processed status.
     */
    public function scopeProcessed($query)
    {
        return $query->where('processed', true);
    }

    /**
     * Scope a query to filter by unprocessed status.
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }
}

