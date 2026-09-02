<?php

namespace App\Http\Controllers;

use App\Services\PaystackService;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected PaystackService $paystack
    ) {
        $this->middleware('auth')->except(['webhook']);
    }

    public function index(Request $request)
    {
        $wallet = $this->walletService->walletFor($request->user());
        $transactions = $wallet->transactions()->orderByDesc('created_at')->paginate(20);

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    /**
     * Start a real Paystack payment — redirects the user to Paystack's
     * hosted checkout page.
     */
    public function topUp(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100', 'max:1000000'],
        ]);

        try {
            $result = $this->paystack->initialize($request->user(), $validated['amount']);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->away($result['authorization_url']);
    }

    /**
     * Paystack redirects the user's browser back here after they pay (or
     * cancel). We re-verify server-side rather than trusting the redirect
     * itself — the webhook is the real source of truth, but we also try
     * to credit here so the user sees their balance update immediately
     * instead of waiting on the webhook to arrive.
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect()->route('wallet.index')->with('status', __('Payment reference missing.'));
        }

        try {
            $data = $this->paystack->verify($reference);
        } catch (\RuntimeException $e) {
            return redirect()->route('wallet.index')->with('status', __('Could not confirm payment. If you were charged, it will reflect shortly.'));
        }

        if (($data['status'] ?? null) !== 'success') {
            return redirect()->route('wallet.index')->with('status', __('Payment was not successful.'));
        }

        $amountInNaira = $data['amount'] / 100;
        $credited = $this->walletService->creditFromPaystackReference($request->user(), $reference, $amountInNaira);

        return redirect()->route('wallet.index')->with('status', $credited
            ? __('Payment successful! Your wallet has been credited.')
            : __('Payment already processed.'));
    }

    /**
     * Server-to-server confirmation from Paystack. This is the real source
     * of truth — no auth middleware (Paystack isn't logged in as anyone),
     * and CSRF is excluded for this route (see VerifyCsrfToken::$except).
     */
    public function webhook(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (! $this->paystack->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');

        if ($event === 'charge.success') {
            $data = $request->input('data');
            $reference = $data['reference'] ?? null;
            $userId = $data['metadata']['user_id'] ?? null;

            if ($reference && $userId) {
                $user = \App\Models\User::find($userId);

                if ($user) {
                    $amountInNaira = $data['amount'] / 100;
                    $this->walletService->creditFromPaystackReference($user, $reference, $amountInNaira);
                }
            }
        }

        return response()->json(['message' => 'ok']);
    }

        /**
     * Called by the inline popup's JS callback after payment completes.
     * Re-verifies with Paystack server-side (never trust the popup's
     * "success" callback alone) before crediting the wallet.
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'reference' => ['required', 'string'],
        ]);

        try {
            $data = $this->paystack->verify($validated['reference']);
        } catch (\RuntimeException $e) {
            return response()->json(['credited' => false, 'message' => __('Could not verify payment.')], 422);
        }

        if (($data['status'] ?? null) !== 'success') {
            return response()->json(['credited' => false, 'message' => __('Payment was not successful.')], 422);
        }

        $amountInNaira = $data['amount'] / 100;
        $credited = $this->walletService->creditFromPaystackReference(
            $request->user(),
            $validated['reference'],
            $amountInNaira
        );

        return response()->json([
            'credited' => $credited,
            'message'  => $credited
                ? __('Wallet credited successfully!')
                : __('This payment was already processed.'),
        ]);
    }
}