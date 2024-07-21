<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPayment;
use App\Models\Order;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    //
    public function orderPayment(Request $request, Order $order){
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'phone_number' => 'required'
        ]);

       
        $amount = $validated['amount'];
        $phoneNumber = $validated['phone_number'];
        ProcessPayment::dispatchAfterResponse($amount, $phoneNumber, $order);
        return response()->json(['success' => true, 'message' => 'Payment processing will start shortly.'], 200);
    }
}
