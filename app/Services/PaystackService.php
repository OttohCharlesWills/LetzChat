<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaystackService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    /**
     * Start a payment. Returns the checkout URL to redirect the user to.
     */
    public function initialize(User $user, float $amount): array
    {
        $reference = 'wallet_' . Str::uuid();

        $response = Http::withToken($this->secretKey)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email'        => $user->email,
                'amount'       => (int) round($amount * 100), // Paystack expects kobo
                'reference'    => $reference,
                'callback_url' => route('wallet.callback'),
                'metadata'     => [
                    'user_id' => $user->id,
                    'purpose' => 'wallet_topup',
                ],
            ]);

        if (! $response->successful() || ! $response->json('status')) {
            throw new \RuntimeException('Could not start payment: ' . $response->json('message', 'Unknown error'));
        }

        return [
            'authorization_url' => $response->json('data.authorization_url'),
            'reference'         => $reference,
        ];
    }

    /**
     * Confirm a transaction actually succeeded (never trust the redirect
     * alone — always re-verify with Paystack server-side).
     */
    public function verify(string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (! $response->successful()) {
            throw new \RuntimeException('Could not verify payment.');
        }

        return $response->json('data');
    }

    /**
     * Confirm a webhook request genuinely came from Paystack, not someone
     * spoofing a "payment succeeded" call to your server.
     */
    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        $expected = hash_hmac('sha512', $payload, $this->secretKey);

        return hash_equals($expected, $signature);
    }
}