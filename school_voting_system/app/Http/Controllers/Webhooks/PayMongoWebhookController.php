<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\DonationCheckoutService;
use App\Services\Payments\PayMongoWebhookVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PayMongoWebhookVerifier $verifier,
        DonationCheckoutService $checkout,
    ): Response {
        $raw = $request->getContent();

        if (! $verifier->verify($raw, $request->header('Paymongo-Signature'))) {
            Log::warning('Rejected PayMongo webhook with invalid signature.', [
                'ip' => $request->ip(),
            ]);

            return response('Invalid signature.', 401);
        }

        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return response('Invalid payload.', 400);
        }

        try {
            $checkout->handleWebhookEvent($payload);
        } catch (\Throwable $exception) {
            Log::error('PayMongo webhook processing failed.', [
                'message' => $exception->getMessage(),
            ]);
        }

        return response('OK', 200);
    }
}
