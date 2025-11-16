<?php

namespace Ibraheem\AramexIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MerchantAramexAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'merchant_id',
        'merchant_api_key',
        'aramex_username',
        'aramex_password',
        'account_number',
        'account_pin',
        'entity',
        'country_code',
        'city',
        'environment',
        'is_active',
        'settings',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'aramex_password',
        'account_pin',
    ];

    protected $attributes = [
        'is_active' => false,
        'environment' => 'sandbox',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->merchant_api_key)) {
                $model->merchant_api_key = Str::random(64);
            }
        });
    }

    /**
     * Get shipments for this account.
     */
    public function shipments()
    {
        return $this->hasMany(AramexShipment::class, 'merchant_aramex_account_id');
    }

    /**
     * Get billing records for this account.
     */
    public function billings()
    {
        return $this->hasMany(MerchantBilling::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Get transactions for this account.
     */
    public function transactions()
    {
        return $this->hasMany(MerchantTransaction::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include production accounts.
     */
    public function scopeProduction($query)
    {
        return $query->where('environment', 'production');
    }

    /**
     * Scope a query to only include sandbox accounts.
     */
    public function scopeSandbox($query)
    {
        return $query->where('environment', 'sandbox');
    }

    /**
     * Find by API key.
     */
    public static function findByApiKey($apiKey)
    {
        return static::where('merchant_api_key', $apiKey)
            ->where('is_active', true)
            ->first();
    }
}

