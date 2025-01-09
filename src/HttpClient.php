<?php

namespace App;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class HttpClient
{
    private $client;
    private $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->client = new Client([
            'base_uri' => rtrim($baseUrl, '/'),
            'verify' => false,
        ]);
        $this->apiKey = $apiKey;
    }

    public function request(string $method, string $endpoint, array $data = [])
    {
        $headers = [
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $options = ['headers' => $headers];

        if ($method === 'GET') {
            $endpoint .= '?' . http_build_query($data);
        } else {
            $options['json'] = $data;
        }

        try {
            $response = $this->client->request($method, $endpoint, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            throw new Exception($response->getBody()->getContents(), $response->getStatusCode());
        }
    }
}