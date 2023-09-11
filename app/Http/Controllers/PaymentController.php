<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\OrderController;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class PaymentController extends ApiController
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required | integer',
            'order_items' => 'required',
            'order_items.*.product_id' => 'required | integer',
            'order_items.*.quantity' => 'required | integer',
            'request_from' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 400);
        }

        $totalAmount = 0;
        $totalDeliveryAmount = 0;
        foreach ($request->order_items as $orderItem) {
            $product = Product::findOrFail($orderItem['product_id']);
            if ($orderItem['quantity'] > $product->quantity) {
                return $this->errorResponse('تعداد محصول وارد شده نا درست است', 400);
            }
            $totalAmount += $product->price * $orderItem['quantity'];
            $totalDeliveryAmount += $product->delivery_amount;
        }

        $payingAmount = $totalAmount + $totalDeliveryAmount;

        $amounts = [
            'totalAmount' => $totalAmount,
            'totalDeliveryAmount' => $totalDeliveryAmount,
            'payingAmount' => $payingAmount,
        ];


        $merchant = env('ZIBAL_IR_API_KEY');
        $amount = $payingAmount . '0';
        $mobile = "شماره موبایل";
        $description = "توضیحات";
        $callbackUrl = env('ZIBAL_IR_CALLBACK_URL');
        $result = $this->sendRequest($merchant, $amount, $callbackUrl, $mobile, $description);


        $result = json_decode($result);

        if ($result->result == 100) {
            OrderController::create($request, $amounts, $result->trackId);
            $go = "https://gateway.zibal.ir/start/$result->trackId";
            return $this->successResponse([
                'url' => $go
            ], 200);
        } else {
            return $this->errorResponse('تراکنش با خطا مواجه شد', 422);
        }
    }

    public function verify(Request $request)
    {
        $merchant = env('ZIBAL_IR_API_KEY');
        $trackId = $request->trackId;

        $result = json_decode($this->verifyRequest($merchant, $trackId));

        if ($result->result == 100) {

            if ($result->result == 201) {
                return $this->errorResponse("قبلا این تراکنش تایید شده است", 200);
            }

            OrderController::update($request->refNumber, $trackId);

           return $this->successResponse('با موفقیت تایید شد.', 200);
        } else {
            if ($result->result == 201) {
                return $this->errorResponse("قبلا این تراکنش تایید شده است", 200);
            }
            if ($result->result == 202) {
                return $this->errorResponse("سفارش پرداخت نشده یا ناموفق بوده است", 422);
            }
            else{
                return $this->errorResponse("سفارش پرداخت نشده یا ناموفق بوده است", 422);
            }
        }

        // switch ($result->result) {
        //     case 100:
        //       return $this->successResponse('با موفقیت تایید شد.' , 100);
        //         break;
        //     case 201:
        //         return  $this->successResponse('قبلا تایید شده.' , 201);
        //         break;
        //     case -1:
        //         return  $this->successResponse('در انتظار پردخت.' , -1);
        //         break;
        //     case 1:
        //         return  $this->successResponse('پرداخت شده - تاییدشده' , 1);
        //         break;
        //     case 2:
        //         return  $this->successResponse('پرداخت شده - تاییدنشده' , 2);
        //         break;
        //     case 3:
        //         return  $this->errorResponse('لغوشده توسط کاربر' , 3);
        //         break;
        //     case 4:
        //         return  $this->errorResponse('شماره کارت نامعتبر می‌باشد.' , 4);
        //         break;
        //     case 5:
        //         return  $this->errorResponse('موجودی حساب کافی نمی‌باشد.' , 5);
        //         break;
        //     case 6:
        //         return  $this->errorResponse('رمز واردشده اشتباه می‌باشد.' , 6);
        //         break;
        //     case 7:
        //         return  $this->errorResponse('تعداد درخواست‌ها بیش از حد مجاز می‌باشد.' , 7);
        //         break;
        //     default:
        //         # code...
        //         break;
        // }
    }

    public function verifyRequest($merchant, $trackId)
    {
        return $this->curl_post('https://gateway.zibal.ir/v1/verify', [
            'merchant'     => $merchant,
            'trackId'       => $trackId,
        ]);
    }

    public function sendRequest($merchant, $amount, $callbackUrl, $mobile = null, $description = null)
    {
        return $this->curl_post('https://gateway.zibal.ir/v1/request', [
            'merchant'     => $merchant,
            'amount'       => $amount,
            'callbackUrl'  => $callbackUrl,
            'mobile'       => $mobile,
            'description'  => $description,
        ]);
    }

    public function curl_post($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}
