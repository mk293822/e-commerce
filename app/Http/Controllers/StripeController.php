<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\OrderViewResource;
use App\Mail\CheckoutCompleted;
use App\Mail\NewOrder;
use App\Models\CartItems;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function success(Request $request): Response
    {
        $user = auth()->user();
        $session_id = $request->get('session_id');

        $orders = Order::query()->where('stripe_session_id', $session_id)->get();

        if ($orders->count() === 0) {
            abort(403);
        }
        foreach ($orders as $order) {
            if ($order->user_id !== $user->id) {
                abort(403);
            }
        }

        return Inertia::render('Stripe/Success', [
            'orders' => OrderViewResource::collection($orders)->collection->toArray(),
        ]);
    }

    public function webhook(Request $request)
    {

        $stripe = new \Stripe\StripeClient(config('app.stripe_secret_key'));

        $endpoint_secret = config('app.webhook_end_point');

        $payload = $request->getContent();

        $sig_header = request()->header('Stripe-Signature');

        $event = null;

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

        } catch (\Exception $e){
            Log::error($e);
            return response("Invalid Payload", 400);
        }


        switch ($event->type){
            case 'charge.updated':
                $charge = $event->data->object;
                $transition_id = $charge['balance_transaction'];
                $payment_intent = $charge['payment_intent'];
                $balance_transaction = $stripe->balanceTransactions->retrieve($transition_id);

                $orders = Order::query()->where('payment_intent', $payment_intent)->get();

                $total_amount = $balance_transaction['amount'] * 100;

                $stripe_fee = 0;

                foreach ($balance_transaction['fee_details'] as $fee_detail) {
                    if($fee_detail->type === 'stripe_fee'){
                        $stripe_fee = $fee_detail->amount * 100;
                    }
                }
                $platform_fee_percentage = 1;

                foreach ($orders as $order) {
                    $vendor_share = $order->total_amount / $total_amount;

                    $order->online_payment_commission = $vendor_share * $stripe_fee;
                    $order->website_commission = ($order->total_amount - $order->online_payment_commission) / 100 * $platform_fee_percentage;
                    $order->vendor_sub_total = $order->total_amount - $order->online_payment_commission - $order->website_commission;
                    $order->save();

                    Mail::to($order->vendorUser)->send(new NewOrder($order));
                }
                Mail::to($orders[0]->user)->send(new CheckoutCompleted($orders));

            case 'checkout.session.completed':
                    $session = $event->data->object;
                    $pi = $session['payment_intent'];

                    $orders = Order::query()
                        ->with(['orderItems'])
                        ->where(['stripe_session_id'=>$session['id']])->get();

                    $productsToDeletedFromCart = [];
                    foreach ($orders as $order) {
                        $order->payment_intent = $pi;
                        $order->status = OrderStatusEnum::Paid->value;
                        $order->save();

                        $productsToDeletedFromCart = [
                            ...$productsToDeletedFromCart,
                            ...$order->orderItems
                                ->map(fn ($item) => $item->product_id)
                                ->toArray(),
                        ];

                        foreach ($order->orderItems as $orderItem) {
                            $options = $orderItem->variation_type_option_ids;
                            $product = $orderItem->product;
                            if($options){
                                sort($options);
                                $variation = $product->productVariation()->get();
                                foreach ($variation as $variationItem) {
                                    if(json_decode($variationItem->variation_type_option_ids, true) === $options) {
                                        if($variationItem && $variationItem->quantity !== null){
                                            $variationItem->quantity -= $orderItem->quantity;
                                            $variationItem->save();
                                        } else if($product->quantity !== null){
                                            $product->quantity -= $orderItem->quantity;
                                            $product->save();
                                        }
                                    }
                                }
                            }
                        }
                        CartItems::query()
                            ->where('user_id', $order->user_id)
                            ->whereIn('product_id', $productsToDeletedFromCart)
                            ->where('saved_for_later', false)
                            ->delete();
                    }

            default:
                echo 'Received unknown event ' . $event->type;
        }
        return response('', 200);
    }

    public function cancel(Request $request)
    {
        $user = auth()->user();
        $session_id = $request->get('session_id');

        $orders = Order::query()->where('stripe_session_id', $session_id)->get();

        if ($orders->count() === 0) {
            abort(403);
        }
        foreach ($orders as $order) {
            if ($order->user_id !== $user->id) {
                abort(403);
            }
        }

        return Inertia::render('Stripe/Fail', [
            'orders' => OrderViewResource::collection($orders)->collection->toArray(),
        ]);
    }
}
