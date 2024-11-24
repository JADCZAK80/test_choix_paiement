<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class SumUpService
{
    private $clientId;
    private $clientSecret;
    private $httpClient;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->httpClient = HttpClient::create();
    }

    public function getToken(): ?string
    {
        $response = $this->httpClient->request('POST', 'https://api.sumup.com/token', [
            'body' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $data = $response->toArray();
            return $data['access_token'] ?? null;
        }

        return null;
    }

    public function createCheckout(string $token, array $checkoutData): ?array
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.sumup.com/v0.1/checkouts', [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'Content-Type' => 'application/json', // Ajout explicite
                ],
                'json' => $checkoutData,
            ]);
    
            $statusCode = $response->getStatusCode();
            $responseContent = $response->getContent(false); // Empêche les exceptions en cas d'erreur
            $responseData = json_decode($responseContent, true);

            if ($statusCode === Response::HTTP_OK || $statusCode === Response::HTTP_CREATED) {
                return $responseData;
            }

            // Log des erreurs pour diagnostic
            dump('Erreur API SumUp:', $responseData);

            return null;
        } catch (\Exception $e) {
            // Gestion des exceptions et log pour debug
            dump('Exception lors de la création du checkout:', $e->getMessage());
            return null;
        }
    

    }
    

    public function completeCheckout(string $token, string $checkoutId, array $cardDetails): ?array
    {
        $response = $this->httpClient->request('PUT', "https://api.sumup.com/v0.1/checkouts/$checkoutId", [
            'headers' => [
                'Authorization' => "Bearer $token",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'payment_type' => 'card',
                'card' => $cardDetails,
            ],
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            return $response->toArray();
        }

        return null;
    }
}
