<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class WatsonService
{
    protected $url;
    protected $apiKey;
    protected $environmentId;
    protected $version;
    protected $authHeader;

    public function __construct()
    {
        $this->url = config('watson.url');
        $this->apiKey = config('watson.api_key');
        $this->environmentId = config('watson.environment_id');
        $this->version = config('watson.version');
        $this->authHeader = 'Basic ' . base64_encode("apikey:{$this->apiKey}");

        if (empty($this->url) || empty($this->apiKey) || empty($this->environmentId) || empty($this->version)) {
            throw new \Exception('Watson configuration is missing');
        }

        Log::info('Watson Service Initialized');
        Log::info('Watson URL: ' . $this->url);
        Log::info('Watson Environment ID: ' . $this->environmentId);
        Log::info('Watson Version: ' . $this->version);
        Log::info('Watson API Key: ' . $this->apiKey);
        Log::info('Watson Auth Header: ' . $this->authHeader);
    }

    public function createSession()
    {
        try {

            $client = new Client();
            $endpoint = "{$this->url}/v2/assistants/{$this->environmentId}/sessions?version={$this->version}";
            Log::info('Watson Session Creation Endpoint: ' . $endpoint);
            // $response = Http::withHeaders([
            //     'Authorization' => $this->authHeader,
            // ])->post($endpoint);

            $response = $client->request('POST', $endpoint, [
                'headers' => [
                    'Authorization' => $this->authHeader,
                    'Content-Type' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Watson Session Creation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendMessage(string $sessionId, string $message)
    {
        try {
            $endpoint = "{$this->url}/v2/assistants/{$this->environmentId}/sessions/{$sessionId}/message?version={$this->version}";

            // $response = Http::withHeaders([
            //     'Authorization' => $this->authHeader,
            //     'Content-Type' => 'application/json',
            // ])->post($endpoint, [
            //     'input' => [
            //         'text' => $message,
            //     ],
            // ]);

            $client = new Client();
            $response = $client->request('POST', $endpoint, [
                'headers' => [
                    'Authorization' => $this->authHeader,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'input' => [
                        'text' => $message,
                    ],
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Watson Message Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
