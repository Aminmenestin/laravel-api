<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItems;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public static function create($request, $amounts, $token)
    {
        DB::beginTransaction();
        $order = Order::create([
            'user_id' => $request->user_id,
            'total_amount' => $amounts['totalAmount'],
            'delivery_amount' => $amounts['totalDeliveryAmount'],
            'paying_amount' => $amounts['payingAmount'],
        ]);

        foreach ($request->order_items as $orderItem) {
            $product = Product::find($orderItem['product_id']);
            OrderItems::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => $orderItem['quantity'],
                'subtotal' => ($product->price * $orderItem['quantity']),
            ]);
        }

        Transaction::create([
            'user_id' => $request->user_id,
            'order_id' => $order->id,
            'amount' => $amounts['payingAmount'],
            'token' => $token,
            'request_from' => $request->request_from
        ]);

        DB::commit();
    }


    public static function update($refNumber, $token)
    {



        DB::beginTransaction();

        $transaction = Transaction::where('token', $token)->firstOrFail();


        $transaction->update([
            'status' => 1,
            'trans_id' => $refNumber,
        ]);

        $order = Order::findOrFail($transaction->order_id);

        $order->update([
            'status' => 1,
            'payment_status' => 1
        ]);


        foreach (OrderItems::where('order_id', $order->id)->get() as $item) {

            $product = Product::find($item->product_id);

            $product->update([
                'quantity' => ($product->quantity - $item->quantity),
            ]);
        }

        DB::commit();
    }
}
