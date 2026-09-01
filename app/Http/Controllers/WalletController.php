<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $wallet = $this->walletService->walletFor($request->user());
        $transactions = $wallet->transactions()->orderByDesc('created_at')->paginate(20);

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    /**
     * Placeholder top-up — credits the wallet directly. Swap this for a
     * real Paystack/Flutterwave charge-and-verify flow when payment
     * processing is wired in; the WalletService::topUp() call underneath
     * stays the same either way.
     */
    public function topUp(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100', 'max:1000000'],
        ]);

        $this->walletService->topUp($request->user(), $validated['amount']);

        return back()->with('status', __('Wallet topped up.'));
    }
}