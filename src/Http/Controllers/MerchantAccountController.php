<?php

namespace Ibraheem\AramexIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Ibraheem\AramexIntegration\Http\Controllers\Controller;
use Ibraheem\AramexIntegration\Models\MerchantAramexAccount;
use Ibraheem\AramexIntegration\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MerchantAccountController extends Controller
{
    use ApiResponseTrait;

    /**
     * Connect or create merchant Aramex account.
     */
    public function connect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|string',
            'aramex_username' => 'required|string',
            'aramex_password' => 'required|string',
            'account_number' => 'required|string',
            'account_pin' => 'required|string',
            'entity' => 'required|string',
            'country_code' => 'required|string|size:2',
            'city' => 'required|string',
            'environment' => 'required|in:sandbox,production',
            'is_active' => 'nullable|boolean',
            'settings' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 'ARAMEX_VALIDATION_01', 422, $validator->errors());
        }

        try {
            $account = MerchantAramexAccount::updateOrCreate(
                ['merchant_id' => $request->merchant_id],
                $request->only([
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
                ])
            );

            $account->makeHidden(['aramex_password', 'account_pin']);

            return $this->success($account, 'Aramex account connected successfully');
        } catch (\Exception $e) {
            Log::error('Error connecting Aramex account', [
                'merchant_id' => $request->merchant_id,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to connect account: ' . $e->getMessage(), 'ARAMEX_CONNECT_ERROR', 500);
        }
    }

    /**
     * Get merchant account.
     */
    public function getAccount(Request $request)
    {
        $merchantId = $request->input('merchant_id');

        if (!$merchantId) {
            return $this->error('Merchant ID is required', 'ARAMEX_MERCHANT_ID_REQUIRED', 400);
        }

        $account = MerchantAramexAccount::where('merchant_id', $merchantId)->first();

        if (!$account) {
            return $this->error('Account not found', 'ARAMEX_ACCOUNT_NOT_FOUND', 404);
        }

        $account->makeHidden(['aramex_password', 'account_pin']);

        return $this->success($account, 'Account retrieved successfully');
    }

    /**
     * Update merchant account.
     */
    public function updateAccount(Request $request)
    {
        $merchantId = $request->input('merchant_id');

        if (!$merchantId) {
            return $this->error('Merchant ID is required', 'ARAMEX_MERCHANT_ID_REQUIRED', 400);
        }

        $validator = Validator::make($request->all(), [
            'aramex_username' => 'nullable|string',
            'aramex_password' => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_pin' => 'nullable|string',
            'entity' => 'nullable|string',
            'country_code' => 'nullable|string|size:2',
            'city' => 'nullable|string',
            'environment' => 'nullable|in:sandbox,production',
            'is_active' => 'nullable|boolean',
            'settings' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 'ARAMEX_VALIDATION_01', 422, $validator->errors());
        }

        try {
            $account = MerchantAramexAccount::where('merchant_id', $merchantId)->first();

            if (!$account) {
                return $this->error('Account not found', 'ARAMEX_ACCOUNT_NOT_FOUND', 404);
            }

            $account->update($request->only([
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
            ]));

            $account->makeHidden(['aramex_password', 'account_pin']);

            return $this->success($account, 'Account updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating Aramex account', [
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to update account: ' . $e->getMessage(), 'ARAMEX_UPDATE_ERROR', 500);
        }
    }

    /**
     * Delete merchant account.
     */
    public function deleteAccount(Request $request)
    {
        $merchantId = $request->input('merchant_id');

        if (!$merchantId) {
            return $this->error('Merchant ID is required', 'ARAMEX_MERCHANT_ID_REQUIRED', 400);
        }

        try {
            $account = MerchantAramexAccount::where('merchant_id', $merchantId)->first();

            if (!$account) {
                return $this->error('Account not found', 'ARAMEX_ACCOUNT_NOT_FOUND', 404);
            }

            $account->delete();

            return $this->success(null, 'Account deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting Aramex account', [
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Failed to delete account: ' . $e->getMessage(), 'ARAMEX_DELETE_ERROR', 500);
        }
    }
}

