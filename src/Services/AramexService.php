<?php

namespace Ibraheem\AramexIntegration\Services;

use Ibraheem\AramexIntegration\Models\MerchantAramexAccount;
use Ibraheem\AramexIntegration\Models\AramexShipment;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AramexService
{
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => config('aramex.api.timeout', 60),
            'verify' => true,
        ]);
        $this->config = config('aramex');
    }

    /**
     * Get merchant Aramex account.
     */
    public function getMerchantAccount($merchantId)
    {
        return MerchantAramexAccount::where('merchant_id', $merchantId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Validate merchant has active Aramex account.
     */
    public function validateMerchantAccount($merchantId)
    {
        $account = $this->getMerchantAccount($merchantId);
        
        if (!$account) {
            throw new \Exception('Aramex account not found or inactive for this merchant', 404);
        }

        return $account;
    }

    /**
     * Get API base URL.
     */
    protected function getBaseUrl($environment)
    {
        return $environment === 'production' 
            ? $this->config['api']['production_url']
            : $this->config['api']['sandbox_url'];
    }

    /**
     * Build authentication payload.
     */
    protected function buildAuthPayload($account)
    {
        return [
            'ClientInfo' => [
                'UserName' => $account->aramex_username,
                'Password' => $account->aramex_password,
                'Version' => 'v1.0',
                'AccountNumber' => $account->account_number,
                'AccountPin' => $account->account_pin,
                'AccountEntity' => $account->entity,
                'AccountCountryCode' => $account->country_code,
            ]
        ];
    }

    /**
     * Create shipment.
     */
    public function createShipment($merchantId, array $data)
    {
        $account = $this->validateMerchantAccount($merchantId);
        $url = $this->getBaseUrl($account->environment) . '/CreateShipments';

        $payload = array_merge(
            $this->buildAuthPayload($account),
            [
                'Shipments' => [
                    [
                        'Reference1' => $data['reference'] ?? Str::uuid(),
                        'Reference2' => $data['reference2'] ?? null,
                        'Reference3' => $data['reference3'] ?? null,
                        'Shipper' => [
                            'Reference1' => $data['shipper_reference'] ?? null,
                            'Reference2' => $data['shipper_reference2'] ?? null,
                            'AccountNumber' => $account->account_number,
                            'PartyAddress' => [
                                'Line1' => $data['shipper_address']['line1'] ?? '',
                                'Line2' => $data['shipper_address']['line2'] ?? null,
                                'Line3' => $data['shipper_address']['line3'] ?? null,
                                'City' => $data['shipper_address']['city'] ?? $account->city,
                                'StateOrProvinceCode' => $data['shipper_address']['state'] ?? null,
                                'PostCode' => $data['shipper_address']['postal_code'] ?? null,
                                'CountryCode' => $data['shipper_address']['country_code'] ?? $account->country_code,
                            ],
                            'Contact' => [
                                'Department' => $data['shipper_contact']['department'] ?? null,
                                'PersonName' => $data['shipper_contact']['name'] ?? '',
                                'Title' => $data['shipper_contact']['title'] ?? null,
                                'CompanyName' => $data['shipper_contact']['company'] ?? null,
                                'PhoneNumber1' => $data['shipper_contact']['phone'] ?? '',
                                'PhoneNumber1Ext' => $data['shipper_contact']['phone_ext'] ?? null,
                                'PhoneNumber2' => $data['shipper_contact']['phone2'] ?? null,
                                'FaxNumber' => $data['shipper_contact']['fax'] ?? null,
                                'CellPhone' => $data['shipper_contact']['mobile'] ?? null,
                                'EmailAddress' => $data['shipper_contact']['email'] ?? null,
                                'Type' => $data['shipper_contact']['type'] ?? 'Business',
                            ]
                        ],
                        'Consignee' => [
                            'Reference1' => $data['receiver']['reference'] ?? null,
                            'Reference2' => $data['receiver']['reference2'] ?? null,
                            'AccountNumber' => $data['receiver']['account_number'] ?? null,
                            'PartyAddress' => [
                                'Line1' => $data['receiver']['address']['line1'] ?? '',
                                'Line2' => $data['receiver']['address']['line2'] ?? null,
                                'Line3' => $data['receiver']['address']['line3'] ?? null,
                                'City' => $data['receiver']['address']['city'] ?? '',
                                'StateOrProvinceCode' => $data['receiver']['address']['state'] ?? null,
                                'PostCode' => $data['receiver']['address']['postal_code'] ?? '',
                                'CountryCode' => $data['receiver']['address']['country_code'] ?? '',
                            ],
                            'Contact' => [
                                'Department' => $data['receiver']['contact']['department'] ?? null,
                                'PersonName' => $data['receiver']['name'] ?? '',
                                'Title' => $data['receiver']['contact']['title'] ?? null,
                                'CompanyName' => $data['receiver']['contact']['company'] ?? null,
                                'PhoneNumber1' => $data['receiver']['phone'] ?? '',
                                'PhoneNumber1Ext' => $data['receiver']['contact']['phone_ext'] ?? null,
                                'PhoneNumber2' => $data['receiver']['contact']['phone2'] ?? null,
                                'FaxNumber' => $data['receiver']['contact']['fax'] ?? null,
                                'CellPhone' => $data['receiver']['phone'] ?? null,
                                'EmailAddress' => $data['receiver']['email'] ?? '',
                                'Type' => $data['receiver']['contact']['type'] ?? 'Business',
                            ]
                        ],
                        'ShippingDateTime' => $data['shipping_date_time'] ?? date('c'),
                        'DueDate' => $data['due_date'] ?? null,
                        'Details' => [
                            'Dimensions' => [
                                'Length' => $data['dimensions']['length'] ?? null,
                                'Width' => $data['dimensions']['width'] ?? null,
                                'Height' => $data['dimensions']['height'] ?? null,
                                'Unit' => $data['dimensions']['unit'] ?? 'CM',
                            ],
                            'ActualWeight' => [
                                'Value' => $data['weight'] ?? 1,
                                'Unit' => $data['weight_unit'] ?? 'KG',
                            ],
                            'ProductGroup' => $data['product_group'] ?? 'DOM',
                            'ProductType' => $data['product_type'] ?? 'ONX',
                            'PaymentType' => $data['payment_type'] ?? 'P',
                            'PaymentOptions' => $data['payment_options'] ?? null,
                            'Services' => $data['services'] ?? null,
                            'NumberOfPieces' => $data['number_of_pieces'] ?? 1,
                            'DescriptionOfGoods' => $data['product_name'] ?? '',
                            'GoodsOriginCountry' => $data['goods_origin_country'] ?? $account->country_code,
                            'Items' => $this->buildItems($data['items'] ?? []),
                        ],
                        'LabelInfo' => [
                            'ReportID' => $data['label_report_id'] ?? 9201,
                            'ReportType' => $data['label_report_type'] ?? 'URL',
                        ]
                    ]
                ],
                'LabelInfo' => [
                    'ReportID' => $data['label_report_id'] ?? 9201,
                    'ReportType' => $data['label_report_type'] ?? 'URL',
                ],
                'Transaction' => [
                    'Reference1' => $data['transaction_reference'] ?? Str::uuid(),
                    'Reference2' => $data['transaction_reference2'] ?? null,
                    'Reference3' => $data['transaction_reference3'] ?? null,
                    'Reference4' => $data['transaction_reference4'] ?? null,
                    'Reference5' => $data['transaction_reference5'] ?? null,
                ]
            ]
        );

        // Handle COD
        if (isset($data['cod_amount']) && $data['cod_amount'] > 0) {
            $payload['Shipments'][0]['Details']['PaymentType'] = 'C';
            $payload['Shipments'][0]['Details']['CashOnDeliveryAmount'] = [
                'Value' => $data['cod_amount'],
                'CurrencyCode' => $data['cod_currency'] ?? 'SAR',
            ];
        }

        try {
            Log::info('Aramex CreateShipment Request', [
                'merchant_id' => $merchantId,
                'url' => $url,
                'payload' => $payload,
            ]);

            $response = $this->client->post($url, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info('Aramex CreateShipment Response', [
                'merchant_id' => $merchantId,
                'response' => $responseData,
            ]);

            if (isset($responseData['HasErrors']) && $responseData['HasErrors']) {
                $errorMessage = $this->extractErrorMessage($responseData);
                
                $shipment = AramexShipment::create([
                    'merchant_id' => $merchantId,
                    'merchant_aramex_account_id' => $account->id,
                    'reference' => $data['reference'] ?? Str::uuid(),
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'shipment_data' => $data,
                    'aramex_response' => $responseData,
                ]);

                throw new \Exception($errorMessage, 400);
            }

            $shipmentResponse = $responseData['Shipments'][0] ?? null;
            
            if ($shipmentResponse && isset($shipmentResponse['ID'])) {
                $shipment = AramexShipment::create([
                    'merchant_id' => $merchantId,
                    'merchant_aramex_account_id' => $account->id,
                    'aramex_shipment_id' => $shipmentResponse['ID'],
                    'aramex_shipment_number' => $shipmentResponse['ID'],
                    'tracking_number' => $shipmentResponse['ID'],
                    'reference' => $data['reference'] ?? Str::uuid(),
                    'label_url' => $shipmentResponse['LabelURL'] ?? null,
                    'status' => 'created',
                    'shipment_data' => $data,
                    'aramex_response' => $responseData,
                    'receiver_info' => $data['receiver'] ?? [],
                    'shipper_info' => $data['shipper_contact'] ?? [],
                    'weight' => $data['weight'] ?? null,
                    'cod_amount' => $data['cod_amount'] ?? null,
                    'product_name' => $data['product_name'] ?? null,
                ]);

                // Save label if base64 provided
                if (isset($shipmentResponse['Label'])) {
                    $this->saveLabel($shipment, $shipmentResponse['Label']);
                }

                return [
                    'success' => true,
                    'shipment' => $shipment,
                    'shipment_number' => $shipmentResponse['ID'],
                    'tracking_url' => $this->buildTrackingUrl($shipmentResponse['ID']),
                    'label_url' => $shipmentResponse['LabelURL'] ?? null,
                ];
            }

            throw new \Exception('Invalid response from Aramex API', 500);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Aramex API Error', [
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);

            throw new \Exception('Aramex API request failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Track shipment.
     */
    public function trackShipment($merchantId, $trackingNumber)
    {
        $account = $this->validateMerchantAccount($merchantId);
        $url = $this->getBaseUrl($account->environment) . '/TrackShipments';

        $payload = array_merge(
            $this->buildAuthPayload($account),
            [
                'Shipments' => [$trackingNumber],
                'GetLastTrackingUpdateOnly' => false,
            ]
        );

        try {
            Log::info('Aramex TrackShipment Request', [
                'merchant_id' => $merchantId,
                'tracking_number' => $trackingNumber,
            ]);

            $response = $this->client->post($url, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info('Aramex TrackShipment Response', [
                'merchant_id' => $merchantId,
                'response' => $responseData,
            ]);

            if (isset($responseData['HasErrors']) && $responseData['HasErrors']) {
                throw new \Exception($this->extractErrorMessage($responseData), 400);
            }

            $trackingData = $responseData['TrackingResults'][0] ?? null;

            if (!$trackingData) {
                throw new \Exception('Tracking information not found', 404);
            }

            // Update shipment if exists
            $shipment = AramexShipment::where('tracking_number', $trackingNumber)
                ->where('merchant_id', $merchantId)
                ->first();

            if ($shipment) {
                $shipment->update([
                    'status' => $this->mapTrackingStatus($trackingData['UpdateCode'] ?? 'Unknown'),
                    'tracking_data' => $trackingData,
                    'last_tracking_update' => now(),
                ]);
            }

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'current_status' => $this->mapTrackingStatus($trackingData['UpdateCode'] ?? 'Unknown'),
                'status_code' => $trackingData['UpdateCode'] ?? null,
                'status_description' => $trackingData['UpdateDescription'] ?? null,
                'history' => $this->formatTrackingHistory($trackingData['TrackingEvents'] ?? []),
                'estimated_delivery_date' => $trackingData['EstimatedDeliveryDate'] ?? null,
            ];

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Aramex Tracking Error', [
                'merchant_id' => $merchantId,
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Aramex tracking request failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculate rate.
     */
    public function calculateRate($merchantId, array $data)
    {
        $account = $this->validateMerchantAccount($merchantId);
        $url = $this->getBaseUrl($account->environment) . '/CalculateRate';

        $payload = array_merge(
            $this->buildAuthPayload($account),
            [
                'OriginAddress' => [
                    'Line1' => $data['origin']['line1'] ?? '',
                    'Line2' => $data['origin']['line2'] ?? null,
                    'City' => $data['origin']['city'] ?? $account->city,
                    'StateOrProvinceCode' => $data['origin']['state'] ?? null,
                    'PostCode' => $data['origin']['postal_code'] ?? null,
                    'CountryCode' => $data['origin']['country_code'] ?? $account->country_code,
                ],
                'DestinationAddress' => [
                    'Line1' => $data['destination']['line1'] ?? '',
                    'Line2' => $data['destination']['line2'] ?? null,
                    'City' => $data['destination']['city'] ?? '',
                    'StateOrProvinceCode' => $data['destination']['state'] ?? null,
                    'PostCode' => $data['destination']['postal_code'] ?? '',
                    'CountryCode' => $data['destination']['country_code'] ?? '',
                ],
                'ShipmentDetails' => [
                    'Dimensions' => [
                        'Length' => $data['dimensions']['length'] ?? null,
                        'Width' => $data['dimensions']['width'] ?? null,
                        'Height' => $data['dimensions']['height'] ?? null,
                        'Unit' => $data['dimensions']['unit'] ?? 'CM',
                    ],
                    'ActualWeight' => [
                        'Value' => $data['weight'] ?? 1,
                        'Unit' => $data['weight_unit'] ?? 'KG',
                    ],
                    'ProductGroup' => $data['product_group'] ?? 'DOM',
                    'ProductType' => $data['product_type'] ?? 'ONX',
                    'NumberOfPieces' => $data['number_of_pieces'] ?? 1,
                ],
            ]
        );

        try {
            Log::info('Aramex CalculateRate Request', [
                'merchant_id' => $merchantId,
                'payload' => $payload,
            ]);

            $response = $this->client->post($url, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info('Aramex CalculateRate Response', [
                'merchant_id' => $merchantId,
                'response' => $responseData,
            ]);

            if (isset($responseData['HasErrors']) && $responseData['HasErrors']) {
                throw new \Exception($this->extractErrorMessage($responseData), 400);
            }

            return [
                'success' => true,
                'total_amount' => $responseData['TotalAmount']['Value'] ?? null,
                'currency' => $responseData['TotalAmount']['CurrencyCode'] ?? null,
                'response' => $responseData,
            ];

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Aramex CalculateRate Error', [
                'merchant_id' => $merchantId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Aramex rate calculation failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Build items array.
     */
    protected function buildItems($items)
    {
        if (empty($items)) {
            return [];
        }

        $aramexItems = [];
        foreach ($items as $item) {
            $aramexItems[] = [
                'PackageType' => $item['package_type'] ?? 'Box',
                'Quantity' => $item['quantity'] ?? 1,
                'Weight' => [
                    'Value' => $item['weight'] ?? 1,
                    'Unit' => $item['weight_unit'] ?? 'KG',
                ],
                'Comments' => $item['description'] ?? $item['name'] ?? '',
                'Reference' => $item['reference'] ?? null,
            ];
        }

        return $aramexItems;
    }

    /**
     * Extract error message.
     */
    protected function extractErrorMessage($responseData)
    {
        if (isset($responseData['Notifications']) && is_array($responseData['Notifications'])) {
            $messages = [];
            foreach ($responseData['Notifications'] as $notification) {
                if (isset($notification['Message'])) {
                    $messages[] = $notification['Message'];
                }
            }
            if (!empty($messages)) {
                return implode(', ', $messages);
            }
        }

        return $responseData['Message'] ?? 'Unknown error occurred';
    }

    /**
     * Map tracking status.
     */
    protected function mapTrackingStatus($aramexStatus)
    {
        $statusMap = [
            'SH001' => 'created',
            'SH002' => 'picked_up',
            'SH003' => 'in_transit',
            'SH004' => 'out_for_delivery',
            'SH005' => 'delivered',
            'SH006' => 'exception',
            'SH007' => 'returned',
            'SH008' => 'cancelled',
        ];

        return $statusMap[$aramexStatus] ?? strtolower($aramexStatus);
    }

    /**
     * Format tracking history.
     */
    protected function formatTrackingHistory($events)
    {
        $history = [];
        foreach ($events as $event) {
            $history[] = [
                'date' => $event['UpdateDateTime'] ?? null,
                'code' => $event['UpdateCode'] ?? null,
                'description' => $event['UpdateDescription'] ?? null,
                'location' => $event['UpdateLocation'] ?? null,
                'comments' => $event['Comments'] ?? null,
            ];
        }
        return $history;
    }

    /**
     * Build tracking URL.
     */
    protected function buildTrackingUrl($trackingNumber)
    {
        return "https://www.aramex.com/track/" . $trackingNumber;
    }

    /**
     * Save label PDF.
     */
    protected function saveLabel($shipment, $base64Label)
    {
        try {
            $labelPath = config('aramex.label.save_path');
            if (!file_exists($labelPath)) {
                mkdir($labelPath, 0755, true);
            }

            $filename = $shipment->tracking_number . '_' . time() . '.pdf';
            $filepath = $labelPath . '/' . $filename;

            file_put_contents($filepath, base64_decode($base64Label));

            $shipment->update([
                'label_path' => $filepath,
            ]);

            return $filepath;
        } catch (\Exception $e) {
            Log::error('Failed to save label', [
                'shipment_id' => $shipment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

