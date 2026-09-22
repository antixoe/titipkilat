<?php

namespace App\Http\Controllers;

use App\Models\EWallet;
use App\Models\FeeSetting;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebWalletController extends Controller
{
    public function index(Request $request)
    {
        $wallet = EWallet::firstOrCreate(['user_id' => $request->user()->id], ['balance' => 0]);
        $transactions = $wallet->transactions()->latest()->get();

        return view('wallet', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'totalTopUp' => $transactions->where('type', 'TOP_UP')->sum('credit'),
            'totalSpent' => $transactions->sum('debit'),
        ]);
    }

    public function topUp(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:1000',
            'payment_method' => 'required|string|in:Bank Transfer,Virtual Account,E-Wallet,QRIS',
        ]);
        $fee = FeeSetting::where('key', 'top_up_fee')->value('amount') ?? 1000;

        DB::transaction(function () use ($request, $data, $fee) {
            $wallet = EWallet::where('user_id', $request->user()->id)->lockForUpdate()->firstOrCreate(
                ['user_id' => $request->user()->id],
                ['balance' => 0]
            );
            $wallet->increment('balance', $data['amount']);
            WalletTransaction::create([
                'e_wallet_id' => $wallet->id,
                'type' => 'TOP_UP',
                'debit' => 0,
                'credit' => $data['amount'],
                'amount' => $data['amount'],
                'fee' => $fee,
                'description' => 'Top up saldo melalui '.$data['payment_method'],
                'reference' => 'TOPUP-'.str()->upper(str()->random(10)),
            ]);
        });

        return redirect()->route('wallet')->with('success', 'Top up berhasil. Saldo bertambah Rp '.number_format($data['amount'], 0, ',', '.').'.');
    }
}
