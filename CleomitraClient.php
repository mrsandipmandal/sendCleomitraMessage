<?php
/**
 * @author Sandip
 * @version 1.0
 * Handles WhatsApp messaging via Cleomitra API
 */

class CleomitraClient
{
    private string $apiKey;
    private string $senderId;
    private string $defaultLanguage;
    private string $baseUrl = 'https://api.cleomitra.app';
    private array $endpointCache = [];

    /**
     * Constructor - can be called in multiple ways:
     * 
     * 1. With all defaults (uses hardcoded values below):
     *    $client = new CleomitraClient();
     * 
     * 2. With just API key (uses default sender/language):
     *    $client = new CleomitraClient('your-api-key');
     * 
     * 3. With API key and sender ID:
     *    $client = new CleomitraClient('your-api-key', 'sender-id');
     * 
     * 4. With API key, sender ID, and language:
     *    $client = new CleomitraClient('your-api-key', 'sender-id', 'en_US');
     * 
     * 5. With config array:
     *    $client = new CleomitraClient([
     *        'api_key' => '...',
     *        'sender_id' => '...',
     *        'default_language' => 'en_US'
     *    ]);
     * 
     * @param array|string|null $config Config array or API key string
     * @param string|null $senderId Sender endpoint UUID (optional)
     * @param string|null $defaultLanguage Language code (optional)
     */
    public function __construct(array|string|null $config = null, ?string $senderId = null, ?string $defaultLanguage = null)
    {

        if (is_array($config)) {
            $this->apiKey = $config['api_key'];
            $this->senderId = $config['sender_id'];
            $this->defaultLanguage = $config['default_language'];
        } elseif (is_string($config)) {
            $this->apiKey = $config;
            $this->senderId = $senderId;
            $this->defaultLanguage = $defaultLanguage;
        } else {
            throw new Exception('Invalid configuration provided. Please provide an API key or a config array.');
        }

        if (empty($this->apiKey)) {
            throw new Exception('API key is required');
        }
    }

    /**
     * Make HTTP request to Cleomitra API
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $headers = [
            "X-API-Key: {$this->apiKey}",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("API Error ({$httpCode}): " . ($result['message'] ?? $response));
        }

        return $result;
    }

    /**
     * Normalize phone number to +91XXXXXXXXXX format
     */
    private function normalizePhone(string $phone): string
    {
        $phone = ltrim($phone, '+');
        if (!str_starts_with($phone, '91')) {
            $phone = '91' . $phone;
        }
        return '+' . $phone;
    }

    /**
     * Get or create endpoint for a phone number
     */
    public function getOrCreateEndpoint(string $phone, string $displayName = 'Customer'): string
    {
        $normalized = $this->normalizePhone($phone);
        
        if (isset($this->endpointCache[$normalized])) {
            return $this->endpointCache[$normalized];
        }

        $result = $this->request('POST', '/endpoints', [
            'channel'     => 'whatsapp',
            'externalId'  => $normalized,
            'displayName' => $displayName
        ]);

        if (!isset($result['endpoint']['id'])) {
            throw new Exception("Failed to create/get endpoint for {$normalized}");
        }

        $endpointId = $result['endpoint']['id'];
        $this->endpointCache[$normalized] = $endpointId;
        return $endpointId;
    }

    /**
     * Send template message - uses config defaults
     */
    public function sendTemplate(
        string $toPhone,
        string $templateName,
        array $params,
        string $language = '',
        string $fromId = ''
    ): array {
        $fromId = $fromId ?: $this->senderId;
        $language = $language ?: $this->defaultLanguage;

        if (empty($fromId)) {
            throw new Exception('Sender ID not configured.');
        }

        $toId = $this->getOrCreateEndpoint($toPhone);

        $payload = [
            'fromId' => $fromId,
            'toId'   => $toId,
            'type'   => 'support',
            'channel' => 'whatsapp',
            'status' => 'pending',
            'content' => [
                'type' => 'template',
                'templateName' => $templateName,
                'templateLanguage' => $language,
                'templateBodyParams' => $params
            ]
        ];

        return $this->request('POST', '/v1/messages', $payload);
    }

    /**
     * Send text message - uses config defaults
     * 
     * @param string $toPhone  Recipient phone number
     * @param string $text     Message text
     * @param string $fromId   Sender endpoint (optional, uses default)
     * @return array
     */
    public function sendText(string $toPhone, string $text, string $fromId = ''): array
    {
        $fromId = $fromId ?: $this->senderId;

        if (empty($fromId)) {
            throw new Exception('Sender ID not configured.');
        }

        $toId = $this->getOrCreateEndpoint($toPhone);

        $payload = [
            'fromId' => $fromId,
            'toId'   => $toId,
            'type'   => 'support',
            'channel' => 'whatsapp',
            'status' => 'pending',
            'content' => [
                'type' => 'text',
                'text' => $text
            ]
        ];

        return $this->request('POST', '/v1/messages', $payload);
    }

    /**
     * Get message status by ID
     */
    public function getMessageStatus(string $messageId): array
    {
        return $this->request('GET', "/v1/messages/{$messageId}");
    }

    /**
     * Get recent messages
     */
    public function getRecentMessages(int $limit = 20): array
    {
        return $this->request('GET', "/v1/messages?pageSize={$limit}");
    }

    /**
     * Get config values
     */
    public function getSenderId(): string
    {
        return $this->senderId;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }
}