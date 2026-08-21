<?php

namespace App\Services\Payments;

use App\Exceptions\PayMongoException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoClient
{
    public function isConfigured(): bool
    {
        return filled($this->secretKey());
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function createCheckoutSession(array $attributes): array
    {
        $response = $this->request('post', '/v1/checkout_sessions', [
            'data' => ['attributes' => $attributes],
        ]);

        return $this->resource($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveCheckoutSession(string $sessionId): array
    {
        $response = $this->request('get', '/v1/checkout_sessions/'.$sessionId);

        return $this->resource($response);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function request(string $method, string $path, array $payload = []): Response
    {
        if (! $this->isConfigured()) {
            throw new PayMongoException('PayMongo secret key is not configured.');
        }

        $pending = Http::withBasicAuth($this->secretKey(), '')
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->baseUrl(rtrim((string) config('services.paymongo.base_url'), '/'));

        $response = $method === 'get'
            ? $pending->get($path)
            : $pending->post($path, $payload);

        if ($response->failed()) {
            $detail = $this->errorDetail($response);

            Log::warning('PayMongo API request failed.', [
                'path' => $path,
                'status' => $response->status(),
                'detail' => $detail,
            ]);

            throw new PayMongoException($detail);
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    protected function resource(Response $response): array
    {
        $data = $response->json('data');

        if (! is_array($data)) {
            throw new PayMongoException('PayMongo returned an unexpected response.');
        }

        return $data;
    }

    protected function errorDetail(Response $response): string
    {
        $errors = $response->json('errors');

        if (is_array($errors) && isset($errors[0]['detail']) && is_string($errors[0]['detail'])) {
            return $errors[0]['detail'];
        }

        return 'PayMongo could not process this payment. Please try again.';
    }

    protected function secretKey(): string
    {
        return trim((string) config('services.paymongo.secret_key'));
    }
}
