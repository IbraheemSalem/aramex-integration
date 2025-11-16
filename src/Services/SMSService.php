<?php

namespace Ibraheem\AramexIntegration\Services;

use Ibraheem\AramexIntegration\Models\AramexShipment;
use Illuminate\Support\Facades\Log;

class SMSService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('aramex.sms');
    }

    /**
     * Send SMS notification for shipment creation.
     */
    public function sendShipmentCreatedSMS($shipment)
    {
        if (!$this->config['enabled']) {
            return false;
        }

        try {
            $phone = $shipment->receiver_info['phone'] ?? null;
            if (!$phone) {
                Log::warning('No phone number for SMS', [
                    'shipment_id' => $shipment->id,
                ]);
                return false;
            }

            $message = $this->buildMessage($shipment);
            $provider = $this->config['provider'];

            switch ($provider) {
                case 'twilio':
                    return $this->sendViaTwilio($phone, $message);
                case 'nexmo':
                    return $this->sendViaNexmo($phone, $message);
                default:
                    Log::warning('Unknown SMS provider', [
                        'provider' => $provider,
                    ]);
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Build SMS message.
     */
    protected function buildMessage($shipment)
    {
        $template = $this->config['template'];
        
        return str_replace([
            '{tracking_number}',
            '{tracking_url}',
            '{status}',
        ], [
            $shipment->tracking_number,
            $this->buildTrackingUrl($shipment->tracking_number),
            $shipment->status,
        ], $template);
    }

    /**
     * Send via Twilio.
     */
    protected function sendViaTwilio($phone, $message)
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $from = config('services.twilio.from') ?? $this->config['from'];

        if (!$accountSid || !$authToken) {
            Log::warning('Twilio credentials not configured');
            return false;
        }

        $client = new \GuzzleHttp\Client();
        
        try {
            $response = $client->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'auth' => [$accountSid, $authToken],
                'form_params' => [
                    'From' => $from,
                    'To' => $phone,
                    'Body' => $message,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            Log::info('SMS sent via Twilio', [
                'phone' => $phone,
                'message_sid' => $result['sid'] ?? null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Twilio SMS failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send via Nexmo.
     */
    protected function sendViaNexmo($phone, $message)
    {
        $apiKey = config('services.nexmo.key');
        $apiSecret = config('services.nexmo.secret');
        $from = config('services.nexmo.from') ?? $this->config['from'];

        if (!$apiKey || !$apiSecret) {
            Log::warning('Nexmo credentials not configured');
            return false;
        }

        $client = new \GuzzleHttp\Client();
        
        try {
            $response = $client->post('https://rest.nexmo.com/sms/json', [
                'form_params' => [
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret,
                    'from' => $from,
                    'to' => $phone,
                    'text' => $message,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            Log::info('SMS sent via Nexmo', [
                'phone' => $phone,
                'message_id' => $result['messages'][0]['message-id'] ?? null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Nexmo SMS failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Build tracking URL.
     */
    protected function buildTrackingUrl($trackingNumber)
    {
        return "https://www.aramex.com/track/" . $trackingNumber;
    }
}

