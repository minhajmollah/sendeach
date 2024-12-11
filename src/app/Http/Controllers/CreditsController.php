<?php

namespace App\Http\Controllers;

use App\Http\Utility\PaymentInsert;
use App\Models\CreditLog;
use App\Models\PaymentLog;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditsController extends Controller
{
    public function create()
    {
        $title = 'Buy Credits';

        $paymentMethods = PaymentMethod::where('status' , 1)->get();
        $dollarPerCredit = CreditLog::getDollarPerCredit();
        $creditsPerDollar = CreditLog::getCreditsPerDollar();

        return view('user.credits.create' , compact('paymentMethods' , 'title' , 'dollarPerCredit', 'creditsPerDollar'));
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'price' => 'required|numeric|min:10' ,
            'payment_gateway' => 'required|exists:payment_methods,id' ,
        ]);

        $user = Auth::user();
        PaymentLog::where('user_id' , $user->id)->where('status' , 0)->delete();


        session()->put('price' , $data['price']);
        session()->forget('subscription_id');

        $paymentMethod = PaymentMethod::where('id' , $data['payment_gateway'])->where('status' , 1)->first();
        PaymentInsert::creditsPaymentCreate($paymentMethod->unique_code);

        return redirect()->route('user.payment.preview');
    }
}
